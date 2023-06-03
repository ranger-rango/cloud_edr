<?php
session_start();
?>
<?php

require 'vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;
error_reporting(E_ALL);
ini_set('display_errors', 1);


function add_custom_rules($event_id, $code, $tactic, $technique, $string_insert1, $string_insert2, $severity_scale)
{
    // $unique_id = "0b29fd65e4ba54264818cb8885c423f5";

    $default_rules_file = "security_rules/default_security_rules.yaml";
    $custom_rules_file = "security_rules/user_security_rules/" . $_SESSION['unique_id'] . "_security_rules.yaml";
    // $custom_rules_file = "security_rules/user_security_rules/" . $unique_id . "_security_rules.yaml";

    if(file_exists($custom_rules_file))
    {
        echo $custom_rules_file;
        $custom_rules = Yaml::parseFile($custom_rules_file);
        $new_rule = [
            'event_id' => $event_id,
            'code' => $code,
            'attack_tactic' => $tactic,
            'attack_technique' => $technique,
            'string_inserts' => [
                'insert1' => $string_insert1,
                'insert2' => $string_insert2
            ],
            'scale' => $severity_scale
        ];

        $custom_rules[] = $new_rule;
        // var_dump($custom_rules);

        $updated_custom_rules = Yaml::dump($custom_rules);

        file_puts_content($custom_rules_file, $updated_custom_rules);

    }
    else
    {
        $default_rules = Yaml::parseFile($default_rules_file);
        $new_rule = [
            'event_id' => $event_id,
            'code' => $code,
            'attack_tactic' => $tactic,
            'attack_technique' => $technique,
            'string_inserts' => [
                'insert1' => $string_insert1,
                'insert2' => $string_insert2
            ],
            'scale' => $severity_scale
        ];

        $default_rules[] = $new_rule;
        // var_dump($default_rules);

        $custom_rules = Yaml::dump($default_rules);

        $file_handler = fopen($custom_rules_file, "w");
        if($file_handler !== false)
        {
            fwrite($file_handler, $custom_rules);
        }

        // echo "file not found !!!!";
    }

    header("location: endpoint.php");

}

if(isset($_POST['add_rule_button']))
{
    if(isset($_POST['event_id']) && isset($_POST['code']) && isset($_POST['tactic']) && isset($_POST['technique']) && isset($_POST['scale']))
    {
        $string_insert1 = null;
        $string_insert2 = null;
        if(isset($_POST['insert1']))
        {
            $string_insert1 = $_POST['insert1'];
        }

        if(isset($_POST['insert2']))
        {
            $string_insert2 = $_POST['insert2'];
        }

        $event_id = (int)$_POST['event_id'];
        $code = $_POST['code'];
        $tactic = $_POST['tactic'];
        $technique = $_POST['technique'];
        $severity_scale = (int)$_POST['scale'];


        add_custom_rules($event_id, $code, $tactic, $technique, $string_insert1, $string_insert2, $severity_scale);
    }
}
