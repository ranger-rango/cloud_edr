<?php
session_start()
?>
<?php
require 'vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;
error_reporting(E_ALL);
ini_set('display_errors', 1);

function fetch_event_details()
{
    $host_name = 'localhost';
    $user_name = 'root';
    $pass = 'root';
    $db = 'edr';

    $conn = new mysqli($host_name, $user_name, $pass, $db);

    // if($conn->connect_error)
    // {
    //     echo "connection error: ".$conn->connect_error;

    // }
    // else
    // {
    //     echo "successful connection";
    // }

    // $id = 1;
    $id = $_POST['evt_id'];
    $event_status = "1";

    $query = "SELECT * FROM endpoint_events WHERE id = ? AND event_status = ?";

    $stmt = mysqli_stmt_init($conn);

    if(mysqli_stmt_prepare($stmt, $query))
    {
        // echo "  prep successful";

        mysqli_stmt_bind_param($stmt, "ss", $id, $event_status);
        mysqli_stmt_execute($stmt);
        $db_result = mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if($result)
        {
            if(mysqli_num_rows($result) > 0)
            {
                $raw_event_details = mysqli_fetch_assoc($result);
                
                $extra_event_details = $raw_event_details['extra_event_info'];
                $extra_event_details = explode(',', $extra_event_details);


                $raw_event_details['extra_event_info'] = $extra_event_details;

                // $unique_id = "0b29fd65e4ba54264818cb8885c423f5";

                $default_rules_file = "security_rules/default_security_rules.yaml";
                $custom_rules_file = "security_rules/user_security_rules/" . $_SESSION['unique_id'] . "_security_rules.yaml";
                // $custom_rules_file = "security_rules/user_security_rules/" . $unique_id . "_security_rules.yaml";


                if(file_exists($custom_rules_file))
                {
                    $sec_rules = Yaml::parseFile($custom_rules_file);
                }
                else
                {
                    $sec_rules = Yaml::parseFile($default_rules_file);
                }


                foreach($sec_rules as $rule)
                {

                    if($raw_event_details['event_id'] == $rule['event_id'])
                    {
                        $tactic_technique = array("attack_tactic" => $rule['attack_tactic'], "attack_technique" => $rule['attack_technique']);
                        $index = 3;
                        $raw_event_details = array_merge(
                            array_slice($raw_event_details, 0, $index),
                            $tactic_technique,
                            array_slice($raw_event_details, $index)
                        );

                    }

                    if(array_key_exists('extra_ids', $rule))
                    {
                        if(in_array($raw_event_details['event_id'], $rule['extra_ids']))
                        {
                            $tactic_technique = array("attack_tactic" => $rule['attack_tactic'], "attack_technique" => $rule['attack_technique']);
                            $index = 3;
                            $raw_event_details = array_merge(
                                array_slice($raw_event_details, 0, $index),
                                $tactic_technique,
                                array_slice($raw_event_details, $index)
                            );
                        }
                    }
                }

                $event_details = json_encode($raw_event_details);
            }
        }
    }
    else
    {
        echo "  preparation failed";
    }

    return $event_details;

}

$evt_details = fetch_event_details();

echo $evt_details;

?>