<?php
$host_name = 'localhost';
$user_name = 'root';
$pass = 'root';
$db = 'edr';

$conn = new mysqli($host_name, $user_name, $pass, $db);

if($conn->connect_error)
{
    die("connection error: ".$conn->connect_error);
}


function login_user($conn, $email, $passkey)
{
    $query = "SELECT * FROM customer_info WHERE email = ?";

    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $query))
    {
        echo "login failed";
    }
    else
    {
        mysqli_stmt_bind_param($stmt, "s", $email);
        $dbresult = mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if(!$dbresult)
        {
            header("location: ../index.php");
            exit();
        }
        else
        {
            while($row = mysqli_fetch_assoc($result))
            {
                $dbpass = $row['pass'];
                $checkpwd = password_verify($passkey, $dbpass);

                if($checkpwd === false)
                {
                    header("location: ../index.php");
                    exit();
                }
                else if($checkpwd === true)
                {
                    $unique_id = $row['unique_id'];
                    $first_name = $row['frist_name'];
                    echo "successful login";
                    echo "welcome " . $row['first_name'];

                    session_start();
                    $_SESSION['unique_id'] = $unique_id;
                    $_SESSION['first_name'] = $first_name;
                    header("location: ../home.php");
                }
            }
        }
        mysqli_stmt_close($stmt);
    }
}

if(isset($_POST['login_button']))
{
    if(isset($_POST['email']) && isset($_POST['passkey']))
    {
        $email = strtolower($_POST['email']);
        $passkey = $_POST['passkey'];

    }

    login_user($conn, $email, $passkey);
}


$conn->close();