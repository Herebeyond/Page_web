<?php
session_start();
require 'db.php'; // Database connection

// Initialize variables for form fields
$username = '';
$password = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['Identification']); // Sanitize user input
    $password = trim($_POST['psw']);

    // Check if the user already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        array_push($errors, "This username is already taken.");
    } else { // If the user is not already taken, check other conditions


        // Check the length of the username
        if (strlen($username) < 3 or strlen($username) > 15) { // Check if the username is between 3 and 15 characters
            array_push($errors, "The username must contain between 3 and 15 characters.");
        }

        // Check the length of the password
        if (strlen($password) < 8 or strlen($password) > 50) { // Check if the password is between 8 and 50 characters
            array_push($errors, "The password must contain between 8 and 50 characters.");
        }

        // Check the validity of the username
        if (!preg_match('/^[a-zA-Z0-9_@]+$/u', $username)) { // Check if the username contains only letters, numbers, underscores, and @
            array_push($errors, "The username can only contain letters, numbers, underscores, and @.");
        }

        // Check the validity of the password
        if (!preg_match('/^[a-zA-Z0-9_@]+$/u', $password)) { // Check if the password contains only letters, numbers, underscores, and @
            array_push($errors, 'The password can only contain letters, numbers, underscores, and @.');
        }
    }

    // If no errors, hash the password and save the user
    if (empty($errors)) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Save the user and their password in the database
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);

        // Redirect to the login page
        header('Location: login.php');
        exit;
    } else {
        for ($i = 0; $i < count($errors); $i++) {
            echo "<span class=error>" . htmlspecialchars($errors[$i]) . "</span><br>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../style/PageStyle.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body id="bodyLogin">
    <div id="globalLogin">
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color:red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        
        <form method="POST" action="register.php" id="formLogin">
            <h2> Register </h2>
            <label for="Identification">Identification</label>
            <input type="text" id="Identification" name="Identification" value="<?php echo htmlspecialchars($username); ?>" required><br>
            <label for="psw">Password</label>
            <input type="password" id="psw" name="psw" value="<?php echo htmlspecialchars($password); ?>" required><br>
            <button type="submit">Register</button><br>
            <span><a href="login.php">Already registered ? Sign In</a>  ||  <a href="../pages/Homepage.php">Homepage</a></span>
        </form><br>         
    </div>
</body>
</html>