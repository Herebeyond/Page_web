<?php
session_start();
require_once __DIR__ . '/../database/db.php'; // Database connection

// Safety check for database connection
if (!$pdo) {
    $_SESSION['error'] = "Database connection error. Please try again later.";
    header('Location: register.php');
    exit;
}

// Initialize variables for form fields
$username = '';
$password = '';
$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['Identification']); // Sanitize user input
    $password = trim($_POST['psw']);
    $email = trim($_POST['email']);

    // Check if the user already exists
    try {
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

            // Validate the email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                array_push($errors, "Invalid email format.");
            }
        }
    } catch (PDOException $e) {
        array_push($errors, "Database error occurred. Please try again later.");
        error_log("Registration database error: " . $e->getMessage());
    }

    // If no errors, hash the password and save the user
    if (empty($errors)) {
        try {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Save the user, their password, and email in the database
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, user_roles) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$username, $hashedPassword, $email]);
            
            // Redirect to the login page
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            array_push($errors, "Registration failed. Please try again later.");
            error_log("Registration database error: " . $e->getMessage());
        } catch (Exception $e) {
            array_push($errors, "Registration failed: " . $e->getMessage());
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>

<html>
    <head>
        <link rel="stylesheet" href="../style/LoginStyle.css">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Register</title>
    </head>
    <body id="bodyLogin">
        <div id="globalLogin">
            <form method="POST" action="register.php" id="formLogin">
                <?php
                // Display database connection error if exists
                if (isset($_SESSION['error'])) {
                    echo '<p style="color:red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
                    unset($_SESSION['error']);
                }
                ?>
                <h2> Register </h2>
                <label for="Identification">Identification</label>
                <input type="text" id="Identification" name="Identification" value="<?php echo htmlspecialchars($username); ?>" required><br>
                <label for="psw">Password</label>
                <div class="password-container">
                    <input type="password" id="psw" name="psw" value="<?php echo htmlspecialchars($password); ?>" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Toggle password visibility">
                        <img class="eye-icon" src="../images/small_img/eye_show.png" alt="Show password">
                    </button>
                </div><br>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required><br>
                <button type="submit">Register</button><br>
                <?php
                for ($i = 0; $i < count($errors); $i++) {
                    echo "<span class=error>" . htmlspecialchars($errors[$i]) . "</span><br>";
                }
                ?>
                <span><a href="login.php">Already registered ? Sign In</a>  ||  <a href="../pages/Homepage.php">Homepage</a></span>
            </form><br>
        </div>
        
        <script>
            function togglePassword() {
                const passwordInput = document.getElementById('psw');
                const toggleButton = document.querySelector('.password-toggle');
                const eyeIcon = toggleButton.querySelector('.eye-icon');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    // Change to eye-hide icon (password is now visible)
                    eyeIcon.src = '../images/small_img/eye_hide.png';
                    eyeIcon.alt = 'Hide password';
                } else {
                    passwordInput.type = 'password';
                    // Change back to eye-show icon (password is now hidden)
                    eyeIcon.src = '../images/small_img/eye_show.png';
                    eyeIcon.alt = 'Show password';
                }
            }
        </script>
    </body>
</html>
