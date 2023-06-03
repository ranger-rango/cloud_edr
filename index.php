<?php
session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Endpoint Detection and Response</title>
        <link rel="stylesheet" href="css/style.css">

        <script>
            function loginviz()
            {
                document.querySelector('.login-wrapper').style.display='grid';

            }
            function signupviz()
            {
                document.querySelector('.signup-wrapper').style.display='grid';
            }

            window.onload = function()
            {
                document.getElementById('login-close').addEventListener('click',
                function()
                {
                    document.querySelector('.login-wrapper').style.display='none';
                });

                document.getElementById('signup-close').addEventListener('click',
                function()
                {
                    document.querySelector('.signup-wrapper').style.display='none';
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
                <li><button onclick="loginviz()">log in</button></li>
                <li><button onclick="signupviz()">sign up</button></li>
            </ul>
        </div>

        <div class="body">
            <div class="desc">

                <?php
                if(isset($_SESSION['signup_status']))
                {
                    if($_SESSION['signup_status'] === "successful")
                    {
                        echo "
                        <p style='font-size:2em; color: green; '> Account Created Successfullly </p>
                        ";
                    }
                    if($_SESSION['signup_status'] === "pass_err")
                    {
                        echo "
                        <p style='font-size:2em; color: red; '> Sign Up Failed: Passwords Dont Match !!! </p>
                        ";
                    }
                    if($_SESSION['signup_status'] === "email_err")
                    {
                        echo "
                        <p style='font-size:2em; color: red; '> Sign Up Failed: Input Valid Email !!! </p>
                        ";
                    }
                    unset($_SESSION['signup_status']);
                }

                ?>

                <div class="sys-description">
                    <h3 style="margin-top: 2.5vh; margin-left: 7vh; color: rgb(149, 149, 149);">CYBERSECURITY AS A SERVICE</h3>
                    <br />
                    <pre style="line-height: 1.7em; color: rgb(137, 139, 149)">
                        This is a Security as a Service Platform that provides enhanced control and visibility over your endpoints improving your security posture. 
                        It is easy to set up, offers:
                            Near real-time analysis of endpoint events
                            Security event monitoring
                            Offers proactive threat hunting 
                            It is lightweight and cheap

                        The platform is targeted to everyone from normal people to security experts. 
                        The system is suitable to : 
                        <> Novice Users. 
                            Simple setup procedures, and a dedicated security team make it an effective addition to the security tools
                            of security focused customers who are non-experts.
                        <> SOC Analysts. 
                            With near real-time endpoint visibility the EDR offers an effective tool for SOCs to monitor endpoints.
                        <> Threat Intelligence Analysts. 
                            The large amounts of endpoint data collected are useful in understanding the ever evolving threat landscape.
                        <> Forensic Analysts. 
                            The large amounts of data collected can be useful in investigating long standing intrusions, sequences of events leading,
                            during and after an attack.
                    </pre>
                </div>
            </div>
            
            <div class="login-wrapper">
                <div class="login-window">
                    <form action="auth/login.php" method="POST" id="signup-form">
                        <table>
                            <tbody>
                                <tr>
                                    <td>Log in</td>
                                    <td id="login-close">+</td>
                                </tr>                                

                                <tr>
                                    <td><input type="text" name="email" required="required" placeholder="email"></td>
                                </tr>
                                <tr>
                                    <td><input type="password" name="passkey" require="required" placeholder="password"></td>
                                </tr>
                                <tr>
                                    <td>
                                        <button name="login_button">
                                            log in
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>

            <div class="signup-wrapper">
                <div class="signup-window">
                    <form action="auth/signup.php" method="POST">
                    <!-- <form action="" method="POST"> -->
                        <table>
                            <tbody>
                                <tr>
                                    <td>Sign Up</td>
                                    <td id="signup-close">+</td>
                                </tr>                                
                                <tr>
                                    <td><input type="text" name="first_name" required="required" placeholder="first name"></td>
                                    <td><input type="text" name="last_name" required="required" placeholder="last name"></td>
                                </tr>
                                <tr>
                                    <td><input type="text" name="email" required="required" placeholder="email"></td>
                                </tr>
                                <tr>
                                    <td><input type="password" name="passkey" require="required" placeholder="password"></td>
                                    <td><input type="password" name="conf_passkey" require="required" placeholder="confirm password"></td>
                                </tr>
                                <!-- <tr> -->
                                <!-- </tr> -->
                                <tr>
                                    <td>
                                        <button name="signup_button" >
                                            Sign Up
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>                    
                </div>
            </div>
        </div>
    </body>

    <!-- <footer></footer> -->
</html>