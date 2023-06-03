<?php
session_start()
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Endpoint Detection and Response</title>
        <link rel="stylesheet" href="css/style.css">

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            function homeVisibility()
            {
                document.querySelector('.live-endpoints').style.display='grid';
                document.querySelector('.analytics').style.display='none';
            }

            function analyticsVisibility()
            {
                document.querySelector('.analytics').style.display='grid';
                document.querySelector('.live-endpoints').style.display='none';
            }

            window.onload = function()
            {
                $.ajax(
                    {
                        url: "analytics.php",
                        dataType: "json",
                        success: function(response) 
                        {
                            var all_pieData = {
                            labels: ["Flagged Events", "Cleared Events"],
                            datasets: [{
                                data: [parseInt(response['all_events_1']), parseInt(response['all_events_0'])],
                                backgroundColor: ["#FF6384", "#36A2EB"]
                            }]
                            };

                            var ur_pieData = {
                            labels: ["Flagged Events", "Cleared Events"],
                            datasets: [{
                                data: [parseInt(response['endpoints_1']), parseInt(response['endpoints_0'])],
                                backgroundColor: ["#FF6384", "#36A2EB"]
                            }]
                            };

                            var pieOptions = {
                            responsive: true
                            };

                            var all_pieChart = new Chart(document.getElementById("all-pie-chart"), {
                            type: 'pie',
                            data: all_pieData,
                            options: pieOptions
                            });

                            var ur_pieChart = new Chart(document.getElementById("ur-pie-chart"), {
                            type: 'pie',
                            data: ur_pieData,
                            options: pieOptions
                            });
                        }
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
                <li><button onclick="homeVisibility()" >home</button></li>
                <li><button onclick="analyticsVisibility()" >Analytics</button></li>
                <li><a href="logout.php"><img src="img/arrow-right-from-bracket-solid.svg" alt="log out" style="height: 1em; width: 1em; background-color: rgb(61,61,61);"></a></li>
            </ul>
        </div>

        <div class="body">
            <div class="live-endpoints">
                <h4 style="text-transform: capitalize;">your endpoint devices</h4>

                <div class="user-endpoints">
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

                    // $unique_id = "0b29fd65e4ba54264818cb8885c423f5";
                    $unique_id = $_SESSION['unique_id'];
                    $query = "SELECT * FROM endpoint_info WHERE unique_id = ?" ;

                    $stmt = mysqli_stmt_init($conn);
                    if(!mysqli_stmt_prepare($stmt, $query))
                    {
                        echo "fetch failed";
                    }
                    else
                    {
                        mysqli_stmt_bind_param($stmt, "s", $unique_id);
                        $dbresult = mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        if(!$dbresult)
                        {
                            echo "fetch failed";
                        }
                        else
                        {
                            while($row = mysqli_fetch_assoc($result))
                            {
                                $endpoint_name = $row['endpoint_name'];
                                echo '
                                    <a href="endpoint.php?id='.$row["id"].'alt=edit">
                                        <div class="endpoint-names">
                                            <p>'.$endpoint_name.'</p>
                                        </div>
                                    </a>
                                    ';
                                $_SESSION['mac_address'] = $row['mac_address'];
                            }
                        }
                    }

                    mysqli_stmt_close($stmt);
                    $conn->close();
                    ?>

                    <a href="">
                        <div class="endpoint-names">
                            <p>windows 10</p>
                        </div>
                    </a>
                    <a href="">
                        <div class="endpoint-names">
                            <p>windows 11</p>
                        </div>
                    </a> 
                    <a href="">
                        <div class="endpoint-names">
                            <p>windows 8</p>
                        </div>
                    </a>  
                    <a href="">
                        <div class="endpoint-names">
                            <p>windows 11</p>
                        </div>
                    </a>                              
                </div>  
                
                <div class="add-endpoint-button">
                    <a href="add_endpoint.php">
                        <button class="add-endpoint">
                            add endpoint
                        </button>
                    </a>
                </div>

            </div>

            <div class="analytics">
                <div class="piecharts">
                    <div class="all-evt-analytics">
                        <p>All Events Anyalytics</p>
                        <canvas id="all-pie-chart" width="400" height="400"></canvas>
                    </div>
                    <div class="your-endpoints-anlytics">
                        <p>Your Endpoints' Events Analytics</p>
                        <canvas id="ur-pie-chart" width="400" height="400"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </body>

    <!-- <footer></footer> -->
</html>