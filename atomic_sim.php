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

    $atomic_execution_commands = array();

    if(array_key_exists('input_arguments', $sim))
    {
        $input_arguments = $sim['input_arguments'];
        $input_arguments = $input_arguments;

        if(array_key_exists('cleanup_command', $sim_executor))
        {
            $atomic_cleanup_commands = explode("\n", $sim_executor['cleanup_command']);
    
            $atomic_execution_commands = array(
                "commands" => $atomic_commands,
                "cleanup" => $atomic_cleanup_commands,
                "input_arguments" => $input_arguments
            );
        }
        else
        {
            $atomic_execution_commands = array(
                "commands" => $atomic_commands,
                "input_arguments" => $input_arguments
            );
        }
    }
    else
    {
        if(array_key_exists('cleanup_command', $sim_executor))
        {
            $atomic_cleanup_commands = explode("\n", $sim_executor['cleanup_command']);
    
            $atomic_execution_commands = array(
                "commands" => $atomic_commands,
                "cleanup" => $atomic_cleanup_commands
            );
        }
        else
        {
            $atomic_execution_commands = array(
                "commands" => $atomic_commands
            );
        }

    }

    // print_r($input_arguments);
    // echo "<br />";


    $atomic_execution_commands = json_encode($atomic_execution_commands);
    print_r($atomic_execution_commands);

    $json_file = "threat_simulator/atomic_sim.json";

    if(file_put_contents($json_file, $atomic_execution_commands))
    {
        echo "<br />atomic commands passed succefully";
    }
    else
    {
        echo "<br />atomic command not passed";
    }


    // sleep(3);

    // $atomic_exec_response = file_get_contents($file);
    // $response = json_decode($atomic_exec_response);

    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();



}
// atomic_test_sim("T1078.001");
if(isset($_POST['sim_button']))
{
    if(isset($_POST['atomic_code']))
    {
        $test_code = $_POST['atomic_code'];

        $sim_details = atomic_test_sim($test_code);

    }
}