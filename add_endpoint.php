<?php
session_start()
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Endpoint Detection and Response</title>
        <link rel="stylesheet" href="css/style.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            var intervalID;
            var productKey;


            function await_client_connection()
            {
                intervalID = setInterval(callPHPFunction, 5000);

                document.querySelector('.await-client').style.display='grid';
            }

            function stop_await_client()
            {
                clearInterval(intervalID);
            }

            function callPHPFunction()
            {
                $.ajax(
                    {
                        url: "await_client_conn.php",
                        dataType: "json",
                        cache: false,
                        success: function(response) 
                        {
                            var row_data = response[0];
                            var column1 = row_data.column1;
                            var column2 = row_data.column2;
                            var column3 = row_data.column3;

                            console.log(response[0]);
                            if(column1 === "Stop")
                            {
                                var response_table = document.querySelector('.await-response');
                                var response_table_html = "<p style='margin-left: 2.5vw;'> Endpoint Successfully Intergrated :</p>" + "<p style='margin-left: 2.5vw;'> Endpoint Name:  " + column2 + "</p>" + "<p style='margin-left: 2.5vw;'> Mac Address:  " + column3 + "</p>" ;

                                response_table.innerHTML = response_table_html;
                                stop_await_client();
                            }
                        },
                        error: function(xhr, status, error) 
                        {
                            console.error(xhr, status, error);
                        }
                    }
                );
            }

            function copyToClipboard() 
            {
                var copyText = document.getElementById("copyText");
                copyText.select();
                copyText.setSelectionRange(0, 99999);

                document.execCommand("copy");
                
                var copyButton = document.getElementById("copyButton");
                copyButton.textContent = "Copied!";
            }
        </script>
    </head>

    <body>
        <div class="top-nav">
            <ul class="sys-name">
                <li>Endpoint Detection and Response</li>
            </ul>
            <ul class="top-nav-items">
                <li><a href="home.php">home</a></li>
                <li><a href="logout.php"><img src="img/arrow-right-from-bracket-solid.svg" alt="log out" style="height: 1em; width: 1em; background-color: rgb(61,61,61);"></a></li>
            </ul>
        </div>

        <div class="body">
            <div class="add-endpoint-wrapper">
                <div class="add-endpoint-body">
                    <p>
                        Agents' Downlaod Links
                    </p>
                    <div class="agent-download-buttons">
                        <a href="agent/endpoint_agent.py" class="agent-download-button" onclick="await_client_connection()" download="EdrAgent.py">
                            <img src="img/windows.svg" alt="windows.exe file" id="windows-icon"> <br />
                            <img src="img/download-solid.svg" alt="windows download agent" class="download-icon">
                        </a>
                        <a href="" class="agent-download-button" onclick="" download="">
                            <img src="img/apple.svg" alt="windows.exe file" id="mac-icon"> <br />
                            <img src="img/download-solid.svg" alt="mac download agent" class="download-icon">
                        </a>
                        <a href="" class="agent-download-button" onclick=""download="">
                            <img src="img/linux.svg" alt="windows.exe file" id="linux-icon"> <br />
                            <img src="img/download-solid.svg" alt="linux download agent" class="download-icon">
                        </a>
                    </div>
                    <ol>
                        <li>Run the agent in the WINDOWS POWERSHELL</li>
                        <li>COPY the UniqueID into the PROMPT</li>
                        <li>Wait for the client to establish a connection to the server</li>
                    </ol>
                    <div class="await-client">
                        <div class="inform-user">
                            <p>Waiting for the client to connect to the server : </p>
                            
                            <table style="margin-left: 2.5vw; ">
                                <tbody>
                                    <?php
                                    echo '
                                    <tr>
                                        <td><textarea id="copyText" rows="1" cols="50" disabled>'.$_SESSION['unique_id'].'</textarea></td>
                                        <td><button id="copyButton" onclick="copyToClipboard()">Copy</button></td>
                                    </tr>';
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="await-response"></div>
                    </div>
                </div>
            </div>
        </div>
    </body>

</html>