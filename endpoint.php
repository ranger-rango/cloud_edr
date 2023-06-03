<?php
session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Endpoint Detection and Response</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/prism.css">
        <!-- <link rel="stylesheet" href="../../../../js/prism-master/themes/prism.css"> -->
        <!-- <link rel="stylesheet" href="css/prism-okaidia.min.css"> -->
        <!-- <link rel="stylesheet" href="css/prism-dark.css"> -->

        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="js/prism.js"></script>
        <script src="js/prism-json.min.js"></script>
        <script>
            function all_window()
            {
                document.querySelector('.endpoint-body-all').style.display='grid';
                document.querySelector('.endpoint-body-flagged').style.display='none';
                document.querySelector('.endpoint-dashboard').style.display='none';
                document.querySelector('.threat-simulator').style.display='none';
                document.querySelector('.sec-rules').style.display='none';
            }

            function flagged_window()
            {
                document.querySelector('.endpoint-body-all').style.display='none';
                document.querySelector('.endpoint-body-flagged').style.display='grid';
                document.querySelector('.endpoint-dashboard').style.display='none';
                document.querySelector('.threat-simulator').style.display='none';
                document.querySelector('.sec-rules').style.display='none';
            }

            function dashboard_window()
            {
                document.querySelector('.endpoint-body-all').style.display='none';
                document.querySelector('.endpoint-body-flagged').style.display='none';
                document.querySelector('.endpoint-dashboard').style.display='grid';
                document.querySelector('.threat-simulator').style.display='none';
                document.querySelector('.sec-rules').style.display='none';
            }

            function sim_window()
            {
                document.querySelector('.endpoint-body-all').style.display='none';
                document.querySelector('.endpoint-body-flagged').style.display='none';
                document.querySelector('.endpoint-dashboard').style.display='none';
                document.querySelector('.threat-simulator').style.display='grid';
                document.querySelector('.sec-rules').style.display='none';
            }

            function rule_window()
            {
                document.querySelector('.endpoint-body-all').style.display='none';
                document.querySelector('.endpoint-body-flagged').style.display='none';
                document.querySelector('.endpoint-dashboard').style.display='none';
                document.querySelector('.threat-simulator').style.display='none';
                document.querySelector('.sec-rules').style.display='grid';
            }

            function all_event_description_js(id)
            {
                $.ajax(
                    {
                        url: "load_event_description.php",
                        method: "POST",
                        dataType: "json",
                        data: {
                            evt_id: id
                        },
                        success: function(response)
                        {

                            var evt_details = JSON.stringify(response, null, 2);
                            var evt_details_window = document.querySelector('.all-events-display');

                            evt_details_window.innerHTML = '<pre class="language-json" style="background-color: rgb(24,24,24); margin-top: 2.5vh; border-color: rgb(61,61,61); overflow: auto; width:32.5vw; height: 75vh;" ><code class="language-json" style=" overflow: auto; width: 32vw; height: 74vh;">' + Prism.highlight(evt_details, Prism.languages.json, 'json') + '</code></pre>';

                        }
                    }
                );
            }

            function flagged_event_description_js(id)
            {
                $.ajax(
                    {
                        url: "load_flagged_events_description.php",
                        method: "POST",
                        dataType: "json",
                        data: {
                            evt_id: id
                        },
                        success: function(response)
                        {
                            var evt_details = JSON.stringify(response, null, 2);
                            var evt_details_window = document.querySelector('.flagged-event-display');

                            evt_details_window.innerHTML = '<pre class="language-json" style="background-color: rgb(24,24,24); margin-top: 2.5vh; border-color: rgb(61,61,61); overflow: auto; width:32.5vw; height: 75vh;" ><code class="language-json" style=" overflow: auto; width: 32vw; height: 74vh;">' + Prism.highlight(evt_details, Prism.languages.json, 'json') + '</code></pre>';

                        }
                    }
                );
            }

            $(document).ready(function() 
            {
                function loadContent() 
                {
                    $.ajax(
                        {
                            url: window.location.href,
                            type: 'GET',
                            success: function(response) 
                            {
                                var all_events_content = $(response).find('#all-events-div').html();
                                var flagged_events_content = $(response).find('#flagged-events-div').html();
                                $('#all-events-div').html(all_events_content);
                                $('#flagged-events-div').html(flagged_events_content);

                            }
                        });
                }

            loadContent();

            setInterval(function() 
            {
                loadContent();
            }, 5000);
            });

            window.onload = function()
            {
                $.ajax(
                    {
                        url: "endpoint_dashboard.php",
                        dataType: "json",
                        success: function(response) 
                        {
                            if(response)
                            {
                                console.log("success");
                            }
                            else
                            {
                                console.log("failed");
                            }
                            var pieData = {
                            labels: ["Flagged Events", "Cleared Events"],
                            datasets: [{
                                data: [parseInt(response['flagged']), parseInt(response['not_flagged'])],
                                backgroundColor: ["#FF6384", "#36A2EB"]
                            }]
                            };

                            var pieOptions = {
                            responsive: true
                            };

                            var pieChart = new Chart(document.getElementById("dashboard-piechart"), {
                            type: 'pie',
                            data: pieData,
                            options: pieOptions
                            });
                        }
                    });

                document.querySelector('.sim-info').addEventListener('click',
                function()
                {
                    var test_code = document.getElementById('test-code-input').value;

                    var sim_details_window = document.querySelector('.sim-details-window');
                    $.ajax(
                        {
                            url: "get_sim_details.php",
                            method: "POST",
                            dataType: "json",
                            data: {
                                atomic_test_code: test_code
                            },
                            success: function(response)
                            {
                                var sim_details = document.getElementById('sim-details');
                                var sim_commands = document.getElementById('sim-commands-info');
                                var sim_cleanup = document.getElementById('cleanup-commands-info');

                                sim_details.innerHTML = '';
                                sim_commands.innerHTML = '';
                                sim_cleanup.innerHTML = '';

                                sim_details_window.style.display = "grid";
                                sim_details.innerHTML = "<pre style='color: red; background-color: rgb(61, 61, 610;'>Attack Technique:   </pre><pre style='color: limegreen; background-color: rgb(61, 61, 610;'>" + response['attack_technique'] + "</pre> <pre style='color: red; background-color: rgb(61, 61, 610;'>Atomic Name:        </pre><pre style='color: limegreen; background-color: rgb(61, 61, 610;'>" + response['atomic_name'] + "</pre> <pre style='color: red; background-color: rgb(61, 61, 610;'>Atomic Description: </pre><pre style='color: limegreen; background-color: rgb(61, 61, 610;'>" + response['atomic_description'] + "</pre>";

                                for(var j = 0; j < response['commands'].length; j++)
                                {
                                    sim_commands.innerHTML += response['commands'][j] + "<br />";
                                }
                                if("cleanup" in response)
                                {
                                    for(var j = 0; j < response['cleanup'].length; j++)
                                    {
                                        sim_cleanup.innerHTML += response['cleanup'][j] + "<br />";
                                    }
                                }
                                else 
                                {
                                    sim_cleanup.innerHTML = "";
                                }

                            }
                        }
                    );
                });

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
                <li><button onclick="all_window()">all events</button></li>
                <li><button onclick="flagged_window()">flagged events</button></li>
                <li><button onclick="dashboard_window()">dashboard</button></li>
                <li><button onclick="sim_window()">threat simulator</button></li>
                <li><button onclick="rule_window()">add rule</button></li>
                <li><a href="logout.php"><img src="img/arrow-right-from-bracket-solid.svg" alt="log out" style="height: 1em; width: 1em; background-color: rgb(61,61,61);"></a></li>
            </ul>
        </div>

        <div class="body">
            <div class="endpoint-body-all">
                <div class="endpoint-events-all">

                    <table>
                        <thead>
                            <tr>
                                <th>All Events</th>
                            </tr>
    
                        </thead>

                        <tbody id="all-events-div" >
                            
                            <?php
                            $host_name = 'localhost';
                            $user_name = 'root';
                            $pwd = 'root';
                            $db = 'edr';

                            $conn = new mysqli($host_name, $user_name, $pwd, $db);
                            if($conn->connect_error)
                            {
                                die('fatal error: '.$conn->connect_error);
                            }

                            $id = $_GET['id'];
                            $query = "SELECT endpoint_events.id, event_description, event_status FROM endpoint_info, endpoint_events WHERE endpoint_info.id = ? AND endpoint_info.mac_address = endpoint_events.mac_address ORDER BY record_number DESC " ;

                            $stmt = mysqli_stmt_init($conn);
                            if(!mysqli_stmt_prepare($stmt, $query))
                            {
                                echo "fetch failed";
                            }
                            else
                            {
                                mysqli_stmt_bind_param($stmt, "s", $id);
                                $dbresult = mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                if(!$dbresult)
                                {
                                    echo "fetch failed";
                                }
                                else
                                {
                                    $count = 0;

                                    while($row = mysqli_fetch_assoc($result))
                                    {
                                        $evt_id = $row['id'];
                                        $event_status = $row['event_status'];
                                        $event_description = $row['event_description'];

                                        if($count % 2 > 0)
                                        {
                                            if($event_status == "0")
                                            {
                                                echo '
                                                    <tr class="odd">
                                                        <td style="background-color: rgb(21, 21, 21); color: white; "><a href="" class="button" onclick="event.preventDefault(); all_event_description_js('.$evt_id.')" style="background-color: rgb(21, 21, 21); color: white; font-size: 0.8em; ">'.$event_description.'</a></td>
                                                    </tr>
                                                    ';
                                            }

                                            if($event_status == "1")
                                            {
                                                echo '
                                                    <tr class="odd">
                                                        <td style="background-color: rgb(21, 21, 21); color: red;"><a href="" class="button" onclick="event.preventDefault(); all_event_description_js('.$evt_id.')" style="background-color: rgb(21, 21, 21); color: red; font-size: 0.8em; ">'.$event_description.'</a></td>
                                                    </tr>
                                                    ';
                                            }

                                        }
                                        else
                                        {
                                            if($event_status == "0")
                                            {
                                                echo '
                                                    <tr class="even">
                                                        <td style="background-color: rgb(61, 61, 61); color: white;"><a href="" class="button" onclick="event.preventDefault(); all_event_description_js('.$evt_id.')" style="background-color: rgb(61, 61, 61); color: white; font-size: 0.8em; ">'.$event_description.'</a></td>
                                                    </tr>
                                                    ';
                                            }

                                            if($event_status == "1")
                                            {
                                                echo '
                                                    <tr class="even">
                                                        <td style="background-color: rgb(61, 61, 61); color: red;"><a href="" class="button" onclick="event.preventDefault(); all_event_description_js('.$evt_id.')" style="background-color: rgb(61, 61, 61); color: red; font-size: 0.8em; ">'.$event_description.'</a></td>
                                                    </tr>
                                                    ';
                                            }

                                        }
                                        // echo '
                                        //     <tr>
                                        //         <td style="background-color: rgb(24, 24, 24);"><a href="" class="button" onclick="event.preventDefault(); all_event_description_js('.$evt_id.')">'.$event_description.'</a></td>
                                        //     </tr>
                                        //     ';

                                        $count++;
                                    }
                                }
                            }

                            mysqli_stmt_close($stmt);
                            $conn->close();
                            ?>

                            <!-- <tr>
                                <td style="background-color: rgb(61, 61, 61);">firewall shutdown</td>
                            </tr>
                            <tr>
                                <td style="background-color: rgb(24, 24, 24);">new user created</td>
                            </tr>
                            <tr>
                                <td style="background-color: rgb(61, 61, 61);">firewall shutdown</td>
                            </tr>
                            <tr>
                                <td style="background-color: rgb(24, 24, 24);">new user created</td>
                            </tr>
                            <tr>
                                <td style="background-color: rgb(61, 61, 61);">firewall shutdown</td>
                            </tr>
                            <tr>
                                <td style="background-color: rgb(24, 24, 24);">new user created</td>
                            </tr>                                                 -->
                        </tbody>
                    </table>

                    
                </div>
    
                <div class="all-event-info">
                    <table>
                        <thead>
                            <th>Event Description</th>
                        </thead>

                        <tbody>
                            <tr>
                                <td class="all-events-display"></td>
                            </tr>

                        </tbody>
                    </table>
                </div>
    
            </div> 


            <div class="endpoint-body-flagged">
                <div class="endpoint-events-flagged">
                    <table>
                        <thead>
                            <tr>
                                <th>Flagged Events</th>
                            </tr>
    
                        </thead>
    
                        <tbody id="flagged-events-div">
                            
                            <?php
                            $host_name = 'localhost';
                            $user_name = 'root';
                            $pwd = 'root';
                            $db = 'edr';

                            $conn = new mysqli($host_name, $user_name, $pwd, $db);
                            if($conn->connect_error)
                            {
                                die('fatal error: '.$conn->connect_error);
                            }

                            $id = $_GET['id'];
                            $query = "SELECT endpoint_events.id, event_description FROM endpoint_info, endpoint_events WHERE endpoint_events.event_status = '1' AND endpoint_info.id = ? AND endpoint_info.mac_address = endpoint_events.mac_address ORDER BY record_number DESC " ;

                            $stmt = mysqli_stmt_init($conn);
                            if(!mysqli_stmt_prepare($stmt, $query))
                            {
                                echo "fetch failed " . mysqli_stmt_error($stmt);
                            }
                            else
                            {
                                mysqli_stmt_bind_param($stmt, "s", $id);
                                $dbresult = mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                if(!$dbresult)
                                {
                                    echo "fetch failed";
                                }
                                else
                                {
                                    $count = 0;

                                    while($row = mysqli_fetch_assoc($result))
                                    {
                                        $evt_id = $row['id'];
                                        // $event_status = $row['event_status'];
                                        $event_description = $row['event_description'];

                                        if($count % 2 > 0)
                                        {
                                            echo '
                                                <tr class="odd">
                                                    <td style="background-color: rgb(24, 24, 24); color: red;"><a href="" class="button" onclick="event.preventDefault(); flagged_event_description_js('.$evt_id.')" style="background-color: rgb(24, 24, 24); color: red; font-size: 0.8em;">'.$event_description.'</a></td>
                                                </tr>
                                                ';

                                        }
                                        else
                                        {
                                            echo '
                                                <tr class="even">
                                                    <td style="background-color: rgb(61, 61, 61); color: red;"><a href="" class="button" onclick="event.preventDefault(); flagged_event_description_js('.$evt_id.')" style="background-color: rgb(61, 61, 61); color: red; font-size: 0.8em;">'.$event_description.'</a></td>
                                                </tr>
                                                ';

                                        }
                                        // echo '
                                        //     <tr>
                                        //         <td style="background-color: rgb(24, 24, 24);"><a href="" class="button" onclick="event.preventDefault(); all_event_description_js('.$evt_id.')">'.$event_description.'</a></td>
                                        //     </tr>
                                        //     ';

                                        $count++;
                                    }
                                }
                            }

                            mysqli_stmt_close($stmt);
                            $conn->close();
                            ?>

                            <!-- <tr>
                                <td style="background-color: rgb(61, 61, 61);">firewall shutdown</td>
                            </tr>
                            <tr>
                                <td style="background-color: rgb(24, 24, 24);">new user created</td>
                            </tr>
                            <tr>
                                <td style="background-color: rgb(61, 61, 61);">firewall shutdown</td>
                            </tr>
                            <tr>
                                <td style="background-color: rgb(24, 24, 24);">new user created</td>
                            </tr>
                            <tr>
                                <td style="background-color: rgb(61, 61, 61);">firewall shutdown</td>
                            </tr>
                            <tr>
                                <td style="background-color: rgb(24, 24, 24);">new user created</td>
                            </tr>                                                 -->
                        </tbody>
                    </table>
                </div>
    
                <div class="flagged-event-info">
                    <table>
                        <thead>
                            <th>Event Description</th>
                        </thead>

                        <tbody>
                            <tr>
                                <td class="flagged-event-display"></td>
                            </tr>

                        </tbody>
                    </table>
                </div>
    
            </div> 


            <div class="endpoint-dashboard">
                <div class="dashboard">
                    <div class="dashboard-pie-chart">
                        <p>This Endpoints Analytics</p>
                        <canvas id="dashboard-piechart" width="400" height="400"></canvas>
                    </div>
                </div>
            </div>

            
            <div class="threat-simulator">
                <div class="simulator">
                    <div class="sim-container">
                        <form action="atomic_sim.php" method="POST">
                            <table>
                                <tbody>
                                    <tr>
                                        <td>Simulate Atomic Test </td>
                                    </tr>

                                    <tr>
                                        <td><input id="test-code-input" type="text" name="atomic_code" required="required" placeholder="Atomic Test Code" ></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <button class="sim-info" onclick="event.preventDefault()">
                                                Get Atomic Test Information
                                            </button>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>

                                            <button class="sim-btn" name="sim_button">
                                                Run Threat Simulation
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>

                    </div>

                    <div class="sim-details-window">
                        <table>
                            <tbody>
                                <tr>
                                    <td colspan="2" style="color: rgb(69, 167, 250); background-color: rgb(61, 61, 61); border-top:0.01em solid rgb(61, 61, 61); border-left:0.01em solid rgb(61, 61, 61); border-right:0.01em solid rgb(61, 61, 61); border-radius: 10px 10px 0px 0px; margin-left: 1em;">Details</td>
                                </tr>
                                <tr>
                                    <td colspan="2" id="sim-details" style="border-bottom:0.01em solid rgb(61, 61, 61); border-left:0.01em solid rgb(61, 61, 61); border-right:0.01em solid rgb(61, 61, 61); 10px 0px 0px 0px;"></td>
                                </tr>
                                <tr>
                                    <td style="width:34vw; color: rgb(69, 167, 250); border-top:0.01em solid rgb(61, 61, 61); border-left:0.01em solid rgb(61, 61, 61); border-right:0.01em solid rgb(61, 61, 61); borger-raduis: 10px 0px 0px 0px; margin-left: 1em;">Simulation Commands</td>
                                    <td style="width:34vw; color: rgb(69, 167, 250); border-top:0.01em solid rgb(61, 61, 61); border-left:0.01em solid rgb(61, 61, 61); border-right:0.01em solid rgb(61, 61, 61); borger-raduis: 0px 10px 0px 0px; margin-left: 1em;">Cleanup Commands</td>
                                </tr>
                                <tr>
                                    <td id="sim-commands-info" style="width:34vw; color: limegreen; background-color: rgb(61, 61, 61); border-radius: 0px 0px 0px 10px; margin-left: 1em;"></td>
                                    <td id="cleanup-commands-info" style="width:34vw; color: limegreen; background-color: rgb(61, 61, 61); border-radius: 0px 0px 10px 0px; margin-left: 1em;"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>


                </div>
            </div>


            <div class="sec-rules">
                <div class="add-sec-rule">
                    <div class="sec-rule-table-container">
                        <form action="add_security_rule.php" method="POST">
                            <table>
                                <tbody>
                                    <tr>
                                        <td>Add Yara Security Rule </td>
                                    </tr>

                                    <tr>
                                        <td><input type="text" name="event_id" require="required" placeholder="Event ID" ></td>
                                        <td><input type="text" name="code" required="required" placeholder="Atomic Test Code" ></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" name="tactic" require="required" placeholder="Attack Tactic" ></td>
                                        <td><input type="text" name="technique" require="required" placeholder="Attack Technique" ></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" name="insert1" placeholder="String Insert (Optional)" ></td>
                                        <td><input type="text" name="insert2" placeholder="String Insert (Optional)" ></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select name="severity_scale" id="scale" required="required">
                                                <option value="">Severity Scale</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <button class="add-rule-btn" name="add_rule_button">
                                                Add Rule
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>
                    </div>

                </div>
            </div>



        </div>

    </body>

    <!-- <footer></footer> -->
</html>
