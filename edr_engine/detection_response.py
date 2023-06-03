import asyncio
import websockets
import mysql.connector, yaml, json, os, smtplib 
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
import socket

sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
sock.connect(("8.8.8.8", 80))
server_ip = sock.getsockname()[0]
sock.close()

client_ip = None

async def sim_client():
    global client_ip
    print("threat simulation client searching for server ...")
    if client_ip != None:
        json_file = "../threat_simulator/atomic_sim.json"
        check_file = os.path.isfile(f"{json_file}")
        if check_file == True:
            with open(json_file, "r") as fo:
                atomic_executors = json.load(fo)    

            if ("commands" in atomic_executors and "cleanup" in atomic_executors) or ("commands" in atomic_executors or "cleanup" in atomic_executors):
                atomic_cleanup = None
                atomic_arguments = None

                atomic_commands = atomic_executors['commands']
                processed_atomic_commands = []
                processed_atomic_cleanup = []
                atomic_string = {}

                if "input_arguments" in atomic_executors:
                    atomic_arguments = atomic_executors['input_arguments']
                    for c in atomic_commands:
                        new_atomic_command = c
                        for k, v in atomic_arguments.items():
                            k_str = "{" + k + "}"
                            if k_str in new_atomic_command:
                                new_atomic_command = new_atomic_command.replace(k_str, str(v['default']))
                        new_atomic_command = new_atomic_command.replace("#", "")
                        processed_atomic_commands.append(new_atomic_command)
                    atomic_string['commands'] = processed_atomic_commands
                    if "cleanup" in atomic_executors:
                        atomic_cleanup = atomic_executors['cleanup']

                        for c in atomic_cleanup:
                            new_atomic_command = c
                            for k, v in atomic_arguments.items():
                                k_str = "{" + k + "}"
                                if k_str in new_atomic_command:
                                    new_atomic_command = new_atomic_command.replace(k_str, str(v['default']))
                            new_atomic_command = new_atomic_command.replace("#", "")
                            processed_atomic_cleanup.append(new_atomic_command)
                    
                        atomic_string['cleanup'] = processed_atomic_cleanup

                else:
                    processed_atomic_commands = atomic_commands
                    atomic_string['commands'] = processed_atomic_commands
                    if "cleanup" in atomic_executors:
                        atomic_cleanup = atomic_executors['cleanup']
                        processed_atomic_cleanup = atomic_cleanup
                        atomic_string['cleanup'] = processed_atomic_cleanup

                print(atomic_string)
                atomic_string = str(atomic_string)
                response = {"response" : "success"}
                with open(json_file, "w") as fo:
                    json.dump(response, fo)
                async with websockets.connect(f"ws://{client_ip}:8001/", timeout=10) as ws:
                    await ws.send(atomic_string)
                    # await ws.close()

            else:
                print("sim commands not found")
                return
        
        if check_file == False:
            print("sim commands not found")
            return
                    
    


async def db_handler(query, db_data):
    conn = mysql.connector.connect(
        host = 'localhost',
        port = '8889',
        user = 'root',
        password = 'root',
        database = 'edr'
    )

    cur = conn.cursor()
    cur.execute(query, db_data)
    conn.commit()
    cur.close()
    conn.close()
    return



async def detection_eng(data):
    conn = mysql.connector.connect(
        host = 'localhost',
        port = 8889,
        user = 'root',
        password = 'root',
        database = 'edr'
    )

    cur = conn.cursor()
    query = f"""
    SELECT unique_id 
    FROM endpoint_info, endpoint_events
    WHERE endpoint_info.mac_address = %s
    """
    var = (data['MacAddress'],)
    cur.execute(query, var)
    f = None
    try:
        unique_id = cur.fetchall()[0][0]
        f = f"../security_rules/user_security_rules/{unique_id}_security_rules.yaml"
    except IndexError:
        pass
    finally:
        cur.close()
        conn.close()


    check_f = os.path.isfile(f"{f}")
    if check_f == True:
        file = f
    if check_f == False:
        file = "../security_rules/default_security_rules.yaml"
    
    with open(file, "r") as fo:
        rules = yaml.safe_load(fo)
    for rule in rules:
        if "extra_ids" in rule:
            if int(data['EventID']) in rule['extra_ids']:
                data['status'] = 1
                break

        if int(data['EventID']) == rule['event_id']:
            data['status'] = '1'

            if rule['scale'] >= 4:
                await response_eng(data['MacAddress'], data['EventID'], rule['attack_tactic'], rule['attack_technique'], data['EventDescription'], data['TimeWritten'], rule['scale'])
            
            if "string_inserts" in rule:
                for v in rule['string_inserts']:
                    if type(v) == list:
                        if int(data["ExtraEventInfo"][v[0]]) not in v[1:]:
                            data['status'] = '0'
            break
        
        if int(data['EventID']) != rule['event_id']:
            data['status'] = '0'            


    if data['ExtraEventInfo'] is not None:
        extra_event_info = str(data['ExtraEventInfo'])
        extra_event_info = extra_event_info.replace("(", "")
        extra_event_info = extra_event_info.replace(")", "")
        extra_event_info = extra_event_info.replace("'", "")
        extra_event_info = extra_event_info.replace('"', "")
        extra_event_info = extra_event_info.replace("-", "")
        extra_event_info = extra_event_info.replace("{", "")
        extra_event_info = extra_event_info.replace("}", "")
    else:
        extra_event_info = "None"

    query = f"""
    INSERT INTO endpoint_events (mac_address, event_id, record_number, source_name, event_description, time_written, extra_event_info, event_status)
    VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
    """
    db_data = (data['MacAddress'], data['EventID'], data['RecordNumber'], data['SourceName'], data['EventDescription'], data['TimeWritten'], extra_event_info, data['status'])
    print("data send to dbhandler")
    await db_handler(query, db_data)
    return


async def response_eng(mac_address, event_id, tactic, technique, event_description, time_written, magnitude):
    conn = mysql.connector.connect(
        host = "localhost",
        port = 8889,
        user = "root",
        password = "root",
        database = "edr"
    )
    cur = conn.cursor()

    query = f"""
    SELECT customer_info.email
    FROM customer_info
    JOIN endpoint_info ON endpoint_info.unique_id = customer_info.unique_id
    WHERE endpoint_info.mac_address = %s;
    """
    var = (mac_address,)
    cur.execute(query, var)

    email = cur.fetchall()[0][0]
    cur.close()
    conn.close()

    msg = MIMEMultipart("alternative")
    msg["From"] = "abelbenardm@gmail.com"
    msg["To"] = email
    msg["Subject"] = f"Magnitude {magnitude} Security Alert: Event Flagged {mac_address}"

    html_message = f"""
    <html>
        <body>
            <h3>Suspicious Event Detected on MacAddress: {mac_address}</h3>
            <p>EventID: {event_id} </p>
            <p>EventDescription: {event_description} </p>
            <p>EventTactic: {tactic} </p>
            <p>EventTechnique: {technique} </p>
            <p>TimeWritten: {time_written} </p>
            <p>Magnitude: {magnitude} </p>

            <p>Respond to this Event if it is out of your normal system behaviour or was not authorised by you </p>
            <br />
        </body>
    </html>
    """

    msg.attach(MIMEText(html_message, "html"))

    try:
        server = smtplib.SMTP("smtp.gmail.com", 587, timeout=20)
        server.starttls()
        server.login("abelbenardm@gmail.com", "cwkgskxevfwftiar")
        
        server.send_message(msg)

        server.quit()

        print("Email Sent Successfully")
    except Exception as e:
        print(f"Error Sending Email: {str(e)}")
    
    return



async def detection_response_server(websocket):
    global client_ip
    client_ip = websocket.remote_address[0]
    print("detection_response_server listening ...")
    async for message in websocket:
        data = eval(message)

        if data['TransmissionType'] == '0':
            query =  f"INSERT INTO endpoint_info (endpoint_name, mac_address, unique_id) VALUES (%s, %s, %s) "
            db_data = (data['ComputerName'], data['MacAddress'], data['UniqueID'])
            await db_handler(query, db_data)

        if data['TransmissionType'] == '1':
            await detection_eng(data)



async def run_detection_response_server():
    await websockets.serve(detection_response_server, f"{server_ip}", 8000)

async def run_sim_client():
    while True:
        await sim_client()
        await asyncio.sleep(5)

async def main():
    detection_response_server_task = asyncio.create_task(run_detection_response_server())
    sim_client_task = asyncio.create_task(run_sim_client())

    await asyncio.gather(detection_response_server_task, sim_client_task)

if __name__ == "__main__":
    asyncio.run(main())
