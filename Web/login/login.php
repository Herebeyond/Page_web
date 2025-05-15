<?php
session_start();
require_once 'db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check if the form has been submitted
    $username = trim($_POST['Identification']); // Sanitize user input
    $password = trim($_POST['psw']); 

    // Check if fields are not empty
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields";
        header('Location: login.php');
        exit;
    }

    // Initialize session variables for failed attempts if not already set
    if (!isset($_SESSION['failed_attempts'])) {
        $_SESSION['failed_attempts'] = 0;
        $_SESSION['last_failed_attempt'] = null;
    }

    // Check if the user is temporarily blocked
    $failed_attempts = $_SESSION['failed_attempts'];
    $last_failed_attempt = $_SESSION['last_failed_attempt'];
    $current_time = new DateTime();
    $block_duration = new DateInterval('PT2M'); // 2 minutes block duration

    // Check if the user has reached the maximum number of failed attempts
    if ($failed_attempts >= 3 && $last_failed_attempt) {
        $last_failed_attempt_time = new DateTime($last_failed_attempt);
        $last_failed_attempt_time->add($block_duration);

        if ($current_time < $last_failed_attempt_time) {
            $remaining_time = $last_failed_attempt_time->getTimestamp() - $current_time->getTimestamp(); // Calculate remaining time in seconds
            $minutes = floor($remaining_time / 60); // Convert remaining time to minutes
            $seconds = $remaining_time % 60; // Get remaining seconds
            $_SESSION['error'] = "Too many failed login attempts. Please try again in $minutes minutes and $seconds seconds."; 
            header('Location: login.php');
            exit;
        }
    }

    // Retrieve the user from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verify if the password matches the hashed password in the database
    if ($user && password_verify($password, $user['password'])) {
        // Reset failed attempts on successful login
        $_SESSION['failed_attempts'] = 0;
        $_SESSION['last_failed_attempt'] = null;

        $_SESSION['user'] = $user['id']; // Log in the user

        // Direct to the last page visited or homepage
        header('Location: ' . ($_SESSION['last_page'] ?? '../pages/Homepage.php')); // Redirect to the last page visited or homepage
        exit;
    } else {
        // Increment failed attempts on unsuccessful login
        $_SESSION['failed_attempts']++;
        $_SESSION['last_failed_attempt'] = $current_time->format('Y-m-d H:i:s');

        $_SESSION['error'] = "Username or password incorrect";
    }

    header('Location: login.php');
    exit;
}
?>

<html>
    <head>
        <link rel="stylesheet" href="../style/LoginStyle.css">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
    </head>
    <body id="bodyLogin">
        <div id="globalLogin">
            <form method="POST" action="login.php" id="formLogin">
                <?php
                if (isset($_SESSION['error'])) { // display an error message if the user did not fill in the fields
                    echo '<p style="color:red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
                    unset($_SESSION['error']);
                }
                ?>
                <h2> Login </h2>
                <label for="Identification">Identification</label>
                <input type="text" name="Identification" required><br>
                <label for="psw">Password</label>
                <input type="password" name="psw" required><br>
                <button type="submit">Sign In</button><br>
                <span><a href="register.php">Not registered ? Do it here</a>  ||  <a href="../pages/Homepage.php">Homepage</a></span>
            </form><br>
        </div>
    </body>
</html>
