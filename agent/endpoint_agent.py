import win32evtlog, asyncio, websockets, os, subprocess, json
import socket
import uuid

sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
sock.connect(("8.8.8.8", 80))
server_ip = sock.getsockname()[0]
sock.close()

det_response_ip = socket.gethostbyname('abel.local')
print(f"Sim Server IP : {server_ip}")

edr_agent_dir = "C:/EDR_AGENT/"

async def setup_endpoint():

    file = f"{edr_agent_dir}Setup_EDR.txt"

    check_file = os.path.isfile(f"{file}")
    if check_file == True:
        return

    if check_file == False:
        while True:
            unique_id = input("Product Key: ")
            if unique_id:
                break

        computer_name = socket.gethostname()
        mac_address = ':'.join(format(x, '02x') for x in uuid.getnode().to_bytes(6, 'big'))

        print("Computer Name:", computer_name)
        print("MAC Address:", mac_address)
        
        device_info = {
            "TransmissionType" : "0",
            "UniqueID" : unique_id,
            "MacAddress" : mac_address,
            "ComputerName" : computer_name,
        }

        os.makedirs(edr_agent_dir, exist_ok=True)
        with open(file, "w") as fo:
            fo.write("EDR setup: 1")

    return device_info



server = 'localhost'

async def collect_logs(closingrecordnum, logtype):
    mac_address = ':'.join(format(x, '02x') for x in uuid.getnode().to_bytes(6, 'big'))


    h = win32evtlog.OpenEventLog(server, logtype)
    endpoint_logs = []

    file = closingrecordnum
    json_file = "windows_event_descriptions.json"

    with open(json_file, "r") as fo:
        windows_event_descriptions = json.load(fo)

    checkfile = os.path.isfile(f"{file}")
    if checkfile == True:
        with open(file, "r") as fo:
            ClosingRecordNumber = fo.read()
        LastRecordNumber = int(ClosingRecordNumber)

    if checkfile == False:
        LastRecordNumber = 0


    if LastRecordNumber != 0:
        counter = 0
        while True:
            events = win32evtlog.ReadEventLog(h, win32evtlog.EVENTLOG_BACKWARDS_READ|win32evtlog.EVENTLOG_SEQUENTIAL_READ, 0)
            if events:
                for event in events:
                    CurrentRecordNumber = event.RecordNumber

                    if counter == 0:
                        newLastRecordNumber = CurrentRecordNumber

                    if CurrentRecordNumber == LastRecordNumber:
                        LastRecordNumber = newLastRecordNumber

                        break
                    if CurrentRecordNumber > LastRecordNumber:


                        if event.StringInserts is not None:
                            string_inserts = event.StringInserts
                        else:
                            string_inserts = None

                        event_data = {
                            "TransmissionType" : "1",
                            "MacAddress" : mac_address,
                            "EventID" : str(event.EventID),
                            "RecordNumber" : str(event.RecordNumber),
                            "TimeWritten" : str(event.TimeWritten),
                            "SourceName" : str(event.SourceName),
                            "EventDescription" : windows_event_descriptions[str(event.EventID)],
                            "ExtraEventInfo" : string_inserts
                        }


                        endpoint_logs.append(event_data)

                    counter += 1

            else:
                break
    if LastRecordNumber == 0:
        while True:
            events = win32evtlog.ReadEventLog(h, win32evtlog.EVENTLOG_FORWARDS_READ|win32evtlog.EVENTLOG_SEQUENTIAL_READ, 0)
            if events:
                
                for event in events:
                    CurrentRecordNumber = event.RecordNumber

                    if CurrentRecordNumber > LastRecordNumber:
                        if event.StringInserts is not None:
                            string_inserts = event.StringInserts
                        else:
                            string_inserts = None

                        event_data = {
                            "TransmissionType" : "1",
                            "MacAddress" : mac_address,
                            "EventID" : str(event.EventID),
                            "RecordNumber" : str(event.RecordNumber),
                            "TimeWritten" : str(event.TimeWritten),
                            "SourceName" : str(event.SourceName),
                            "EventDescription" : windows_event_descriptions[str(event.EventID)],
                            "ExtraEventInfo" : string_inserts
                        }
                        

                        endpoint_logs.append(event_data)


                    LastRecordNumber = CurrentRecordNumber
            else:
                break

    ClosingRecordNumber = str(LastRecordNumber)

    with open(file, "w") as fo:
        fo.write(ClosingRecordNumber)
    return endpoint_logs



async def transmit_endpoint_data():
    global det_response_ip
    try:
        async with websockets.connect(f"ws://{det_response_ip}:8000/", timeout=30) as websocket:
            endpoint_info = await setup_endpoint()
            setup_data = None
            event_data = None

            if endpoint_info:
                setup_data = endpoint_info
            if endpoint_info == None:
                secdata = await collect_logs(f"{edr_agent_dir}Security_ClosingRecordNumber.txt", "Security")

                event_data = secdata


            if setup_data:
                message = setup_data
                message = str(message)
                await websocket.send(message)
                # await websocket.close()

            if event_data:
                for data in event_data:
                    message = data
                    message = str(message)
                    await websocket.send(message)        
                    # await websocket.close()


    except websockets.exceptions.ConnectionClosedOK:
        await asyncio.sleep(5)
    



async def sim_server(ws):
    async for message in ws:
        atomic_commands = eval(message)

        sim_atomic_commands = atomic_commands['commands']
        for command in sim_atomic_commands:
            try:
                subprocess.run(command, shell=True)
                
            except subprocess.CalledProcessError as e:
                subprocess.run(['powershell.exe', '-Command', command])


        sim_atomic_cleanup = None
        if "cleanup" in atomic_commands:
            sim_atomic_cleanup = atomic_commands['cleanup']
            for command in sim_atomic_cleanup:
                try:
                    subprocess.run(command, shell=True)
                    
                except subprocess.CalledProcessError as e:
                    subprocess.run(['powershell.exe', '-Command', command])




async def run_transmit_endpoint_data():
    while True:
        await transmit_endpoint_data()
        await asyncio.sleep(7)


async def run_sim_server():
    global server_ip
    await websockets.serve(sim_server, f'{server_ip}', 8001)



async def main():
    transmit_endpoint_data_task = asyncio.create_task(run_transmit_endpoint_data())
    sim_server_task = asyncio.create_task(run_sim_server())

    await asyncio.gather(transmit_endpoint_data_task, sim_server_task)

if __name__ == "__main__":
    asyncio.run(main())
