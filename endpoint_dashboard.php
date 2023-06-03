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


$mac_address = $_SESSION['mac_address'];

$q1 = "SELECT COUNT(*) AS row_count FROM endpoint_info, endpoint_events WHERE endpoint_events.mac_address = ? AND endpoint_events.event_status = '1';";
$q0 = "SELECT COUNT(*) AS row_count FROM endpoint_info, endpoint_events WHERE endpoint_events.mac_address = ? AND endpoint_events.event_status = '0';";

$stmt = mysqli_stmt_init($conn);
if (mysqli_stmt_prepare($stmt, $q1)) {
    mysqli_stmt_bind_param($stmt, "s", $mac_address);
    $q1_dbresult = mysqli_stmt_execute($stmt);
    $q1_result = mysqli_stmt_get_result($stmt);
    
    $q1_row = mysqli_fetch_assoc($q1_result);
    $q1_row_count = $q1_row['row_count'];
    // echo "Q Row Count: " . $q1_row_count . "<br>";
}

if (mysqli_stmt_prepare($stmt, $q0)) {
    mysqli_stmt_bind_param($stmt, "s", $mac_address);
    $q0_dbresult = mysqli_stmt_execute($stmt);
    $q0_result = mysqli_stmt_get_result($stmt);
    
    $q0_row = mysqli_fetch_assoc($q0_result);
    $q0_row_count = $q0_row['row_count'];
    // echo "q0 Row Count: " . $q0_row_count . "<br>";
}

$stats = array(
    "flagged" => $q1_row_count,
    "not_flagged" => $q0_row_count
);

echo json_encode($stats);
