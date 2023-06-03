<?php
session_start();

$host_name = 'localhost';
$user_name = 'root';
$pass = 'root';
$db = 'edr';

$conn = new mysqli($host_name, $user_name, $pass, $db);

if($conn->connect_error)
{
    die("connection error: ".$conn->connect_error);
}
// else
// {
//     echo "connection successful";
//     echo "<br />";
// }

$uid = $_SESSION['unique_id'];

$qa1 = "SELECT COUNT(*) AS row_count FROM endpoint_events WHERE event_status = '1';";
$qa0 = "SELECT COUNT(*) AS row_count FROM endpoint_events WHERE event_status = '0';";

$qu1 = "SELECT COUNT(*) AS row_count FROM endpoint_info, endpoint_events WHERE endpoint_events.event_status = '1' AND endpoint_info.unique_id = ?;";
$qu0 = "SELECT COUNT(*) AS row_count FROM endpoint_info, endpoint_events WHERE endpoint_events.event_status = '0' AND endpoint_info.unique_id = ?;";

$stmt = mysqli_stmt_init($conn);
if (mysqli_stmt_prepare($stmt, $qu1)) {
    mysqli_stmt_bind_param($stmt, "s", $uid);
    $all_dbresult = mysqli_stmt_execute($stmt);
    $all_result = mysqli_stmt_get_result($stmt);
    
    $all_row = mysqli_fetch_assoc($all_result);
    $all_row_count = $all_row['row_count'];
    // echo "All Row Count: " . $all_row_count . "<br>";
}

if (mysqli_stmt_prepare($stmt, $qu0)) {
    mysqli_stmt_bind_param($stmt, "s", $uid);
    $u_dbresult = mysqli_stmt_execute($stmt);
    $u_result = mysqli_stmt_get_result($stmt);
    
    $u_row = mysqli_fetch_assoc($u_result);
    $u_row_count = $u_row['row_count'];
    // echo "Unread Row Count: " . $u_row_count . "<br>";
}

$qa1result = mysqli_query($conn, $qa1);
$qa0result = mysqli_query($conn, $qa0);

$qa1_row = mysqli_fetch_assoc($qa1result);
$qa1_row_count = $qa1_row['row_count'];
// echo "QA1 Row Count: " . $qa1_row_count . "<br>";

$qa0_row = mysqli_fetch_assoc($qa0result);
$qa0_row_count = $qa0_row['row_count'];
// echo "QA0 Row Count: " . $qa0_row_count . "<br>";


$stats = array(
    "all_events_1" => $qa1_row_count, 
    "all_events_0" => $qa0_row_count,
    "endpoints_1" => $all_row_count,
    "endpoints_0" => $u_row_count
);

// var_dump($stats);

echo json_encode($stats);


