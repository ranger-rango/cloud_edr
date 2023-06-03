<?php
session_start();
?>
<?php

require 'vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

error_reporting(E_ALL);
ini_set('display_errors', 1);

function atomic_test_sim($test_code)
{
    $atomic_file = "threat_simulator/atomics/" . $test_code . "/" . $test_code . ".yaml";
    $atomic_test = Yaml::parseFile($atomic_file);

    $attack_technique = $atomic_test['attack_technique'];
    $atomic_name = $atomic_test['display_name'];

    $sim = $atomic_test['atomic_tests'][0];
    $sim_description = $sim['description'];

    $sim_executor = $sim['executor'];
    $atomic_commands = explode("\n", $sim_executor['command']);
    $atomic_sim_details = array();

    if(array_key_exists('cleanup_command', $sim_executor))
    {
        $atomic_cleanup_commands = explode("\n", $sim_executor['cleanup_command']);

        $atomic_sim_details = array(
            "attack_technique" => $attack_technique,
            "atomic_name" => $atomic_name,
            "atomic_description" => $sim_description,
            "commands" => $atomic_commands,
            "cleanup" => $atomic_cleanup_commands
        );
    }
    else
    {
        $atomic_sim_details = array(
            "attack_technique" => $attack_technique,
            "atomic_name" => $atomic_name,
            "atomic_description" => $sim_description,
            "commands" => $atomic_commands
        );
    }

    $atomic_sim_details = json_encode($atomic_sim_details);

//    echo $atomic_sim_details;
    return $atomic_sim_details;

}
// echo atomic_test_sim("T1110.001");

if(isset($_POST['atomic_test_code']))
{
    $test_code = $_POST['atomic_test_code'];

    $sim_details = atomic_test_sim($test_code);

    echo $sim_details;
}