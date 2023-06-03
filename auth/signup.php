<?php
// var_dump($_POST);
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
else
{
    echo "connection successful";
}


function validate_mail($email)
{
    $result;
    if(filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        $result = true;
        echo $result;
    }
    else
    {
        $result = false;
    }

    return $result; 
}


function validate_passwd($passkey, $conf_passkey)
{
    $result;
    if($passkey !== $conf_passkey)
    {
        $result = false;
    }
    else
    {
        $result = true;
        echo $result;
    }

    return $result;
}


function signup_user($conn, $first_name, $last_name, $email, $passkey, $uniqueId)
{
    $hashedpass = password_hash($passkey, PASSWORD_DEFAULT);

    $query =  "INSERT INTO customer_info (first_name, last_name, email, pass, unique_id) values (?, ?, ?, ?, ?)";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $query))
    {
        echo "sigup failed";
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "sssss", $first_name, $last_name, $email, $hashedpass, $uniqueId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $_SESSION['signup_status'] = "successful";
    }

    header("location: ../index.php");
}


if(isset($_POST['signup_button']))
{

    if(isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['passkey']) && isset($_POST['conf_passkey']))
    {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = strtolower($_POST['email']);
        $passkey = $_POST['passkey'];
        $conf_passkey = $_POST['conf_passkey'];

        echo " details posted ";
    }
    else
    {
       exit();
    }

    if(validate_mail($email) === false)
    {
        $_SESSION['signup_status'] = "email_err";
        header("location: ../index.php");
        exit();
    }
    if(validate_passwd($passkey, $conf_passkey) === false)
    {
        $_SESSION['signup_status'] = "pass_err";
        header("location: ../index.php");
        exit();
    }

    $uniqueId = uniqid('', true);
    $uniqueId = md5($uniqueId); 

    signup_user($conn, $first_name, $last_name, $email, $passkey, $uniqueId);
}
