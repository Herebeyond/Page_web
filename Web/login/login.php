<?php
session_start();
require 'db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check if the form has been submitted
    $username = trim($_POST['Identification']); // Sanitize user input
    $password = trim($_POST['psw']); 

    // Check if fields are not empty
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields";
        header('Location: login.php');
        exit;
    }

    // Retrieve the user from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verify if the password matches the hashed password in the database
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['id']; // Log in the user
        header('Location: page.php');
        exit;
    } else {
        $_SESSION['error'] = "Username or password incorrect";
    }
}
?>

<html>
    <head>
        <link rel="stylesheet" href="../css/style.css">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
    </head>
    <body>
        <div id="global">
            <div id="englobe">
                <?php
                if (isset($_SESSION['error'])) { // display an error message if the user did not fill in the fields
                    echo '<p style="color:red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
                    unset($_SESSION['error']);
                }
                ?>
                <h2> Login </h2>
                <form method="POST" action="login.php">
                    <label for="Identification">Identification</label>
                    <input type="text" name="Identification" value="<?php echo isset($username) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : ''; ?>" required><br>
                    <label for="psw">Password</label>
                    <input type="password" name="psw" required><br>
                    <button type="submit">Sign In</button>
                </form><br>
                <a href="register.php">Not registered ? Do it here</a> <span>  ||  </span> <a href="page.php">Home</a>
            </div>
        </div>
    </body>
</html>