# import yaml

# file = "threat_simulator/atomics/T1078.001/T1078.001.yaml"
# # file = "security_rules/default_security_rules.yaml"
# with open(file, "r") as fo:
#     rules = yaml.safe_load(fo)

# for rule in rules:
#     event_id = rule['event_id']
#     code = rule['code']
#     print(event_id)
#     print(code)
#     if "string_inserts" in rule:
#         # if type(rule['string_inserts']) == list:
#         for k, v in rule['string_inserts'].items():
#             print(k, v)
#     else:
#         pass
#     print("\n")



# import json

# file = "threat_simulator/atomic_sim.json"

# with open(file, "r") as fo:
#     atomic_executors = json.load(fo)

# atomic_cleanup = None
# atomic_arguments = None

# atomic_commands = atomic_executors['commands']
# processed_atomic_commands = []
# processed_atomic_cleanup = []
# atomic_string = {}

# if "input_arguments" in atomic_executors:
#     atomic_arguments = atomic_executors['input_arguments']
#     for c in atomic_commands:
#         new_atomic_command = c
#         for k, v in atomic_arguments.items():
#             k_str = "{" + k + "}"
#             if k_str in new_atomic_command:
#                 new_atomic_command = new_atomic_command.replace(k_str, str(v['default']))
#         new_atomic_command = new_atomic_command.replace("#", "")
#         processed_atomic_commands.append(new_atomic_command)
#     atomic_string['commands'] = processed_atomic_commands
#     if "cleanup" in atomic_executors:
#         atomic_cleanup = atomic_executors['cleanup']

#         for c in atomic_cleanup:
#             new_atomic_command = c
#             for k, v in atomic_arguments.items():
#                 k_str = "{" + k + "}"
#                 if k_str in new_atomic_command:
#                     new_atomic_command = new_atomic_command.replace(k_str, str(v['default']))
#             new_atomic_command = new_atomic_command.replace("#", "")
#             processed_atomic_cleanup.append(new_atomic_command)
    
#         atomic_string['cleanup'] = processed_atomic_cleanup

# else:
#     processed_atomic_commands = atomic_commands
#     atomic_string['commands'] = processed_atomic_commands
#     if "cleanup" in atomic_executors:
#         atomic_cleanup = atomic_executors['cleanup']
#         processed_atomic_cleanup = atomic_cleanup
#         atomic_string['cleanup'] = processed_atomic_cleanup


# for k, v in atomic_string.items():
#     print(f"{k} -> {v}")
# print(atomic_executors)
# print(atomic_commands)
# print("\n\n")
# print(atomic_cleanup)


# string = "('S-1-5-18', 'SYSTEM', 'NT AUTHORITY', '0x3e7', 'SeAssignPrimaryTokenPrivilege\r\n\t\t\tSeTcbPrivilege\r\n\t\t\tSeSecurityPrivilege\r\n\t\t\tSeTakeOwnershipPrivilege\r\n\t\t\tSeLoadDriverPrivilege\r\n\t\t\tSeBackupPrivilege\r\n\t\t\tSeRestorePrivilege\r\n\t\t\tSeDebugPrivilege\r\n\t\t\tSeAuditPrivilege\r\n\t\t\tSeSystemEnvironmentPrivilege\r\n\t\t\tSeImpersonatePrivilege\r\n\t\t\tSeDelegateSessionUserImpersonatePrivilege')"
# string = string.replace("\r", "")
# string = string.replace("\n", "")
# string = string.replace("\t", "")
# tup = eval(string)

# for t in tup:
#     print(t)

# import socket

# def get_ip_address():
#     # Create a socket object
#     sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)

#     try:
#         # Attempt to connect to a public address (Google DNS)
#         sock.connect(("8.8.8.8", 80))
#         # Get the IP address
#         ip_address = sock.getsockname()[0]
#     finally:
#         # Close the socket
#         sock.close()

#     return ip_address

# # Call the function to retrieve the IP address
# ip = get_ip_address()
# print("IP Address:", ip)

import mysql.connector

conn = mysql.connector.connect(
    host = "localhost",
    port = 8889,
    user = "root",
    password = "root",
    database = "edr"
)
cur = conn.cursor()

query = f"""
SELECT unique_id 
FROM endpoint_info, endpoint_events
WHERE endpoint_info.mac_address = %s
"""
var = ("08:00:27:2d:43:64",)
cur.execute(query, var)

# email = cur.fetchall()[0][0]

try:
    unique_id = cur.fetchall()[0][0]
    print(unique_id)
except IndexError:
    print("uid not found")
cur.close()
conn.close()

# import smtplib
# from email.mime.multipart import MIMEMultipart
# from email.mime.text import MIMEText

# msg = MIMEMultipart("alternative")
# msg["From"] = "abelbenardm@gmail.com"
# msg["To"] = "abelbenard@students.uonbi.ac.ke"
# msg["Subject"] = f"Magnitude  Security Alert: Event Flagged "

# html_message = f"""
# <html>
#     <body>
#         <h3>Suspicious Event Detected on MacAddress: </h3>
#         <p>EventID:  </p>
#         <p>EventDescription:  </p>
#         <p>EventTactic:  </p>
#         <p>EventTechnique: </p>
#         <p>TimeWritten:  </p>
#         <p>Magnitude:  </p>

#         <p>Respond to this Event if it is out of your normal system behaviour or was not authorised by you </p>
#         <br />
#     </body>
# </html>
# """

# msg.attach(MIMEText(html_message, "html"))

# try:
#     # server = smtplib.SMTP_SSL("mail.google.com", 465)
#     server = smtplib.SMTP("smtp.gmail.com", 587, timeout=10)
#     server.starttls()
#     server.login("abelbenardm@gmail.com", "cwkgskxevfwftiar")
    
#     server.send_message(msg)

#     server.quit()

#     print("Email Sent Successfully")
# except Exception as e:
#     print(f"Error Sending Email: {str(e)}")

