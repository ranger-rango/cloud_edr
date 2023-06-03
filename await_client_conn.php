<?php
session_start()
?>
<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
function getRowData() 
{
    $host_name = 'localhost';
    $user_name = 'root';
    $pwd = 'root';
    $db = 'edr';

    $conn = new mysqli($host_name, $user_name, $pwd, $db);

    $endpoint_name = null;
    $mac_address = null;

    
    // $unique_id = "0b29fd65e4ba54264818cb8885c423f5";
    $unique_id = $_SESSION['unique_id'];
    
    $fetch_query = "SELECT endpoint_name, mac_address FROM endpoint_info WHERE unique_id = ? ORDER BY id DESC LIMIT 1";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $fetch_query))
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
            if(mysqli_num_rows($result) > 0)
            {
                while($row = mysqli_fetch_assoc($result))
                {
                    $endpoint_name = $row['endpoint_name'];
                    $mac_address = $row['mac_address'];
    
                }
            }
            else
            {
                $endpoint_name = null;
                $mac_address = null;
            }

        }
        mysqli_stmt_close($stmt);

    }
    if($endpoint_name === null && $mac_address === null)
    {
        $status = "Continue";
    }
    else
    {
        $status = "Stop";
    }

    $rowData = array(
        array('column1' => $status, 'column2' => $endpoint_name, 'column3' => $mac_address),
    );

    return $rowData;


}


$rowData = getRowData();

echo json_encode($rowData);


?>
