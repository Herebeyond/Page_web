<?php
session_start();
require 'db.php'; // Connexion à la base


if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $username = trim($_POST['Identification']); // Nettoyage des entrées utilisateur
    $password = trim($_POST['psw']); 

    // Vérification que les champs ne sont pas vides
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "please fill in all fields";
        header('Location: login.php');
        exit;
    }


    // Récupérer l'utilisateur depuis la base de données
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Vérifier si le mot de passe correspond au mot de passe haché dans la base
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['id']; // Connecte l'utilisateur
        header('Location: page.php');
        exit;
    } else {
        $_SESSION['error'] = "username or password incorrect";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="../css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
</head>
<body>
    <div id="global">
        <div id="englobe">
            <?php
            if (isset($_SESSION['error'])) {
                echo '<p style="color:red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
                unset($_SESSION['error']);
            }
            ?>
            <h2> Login </h2>
            <form method="POST" action="login.php">
                <label for="Identification">Identification</label>
                <input type="text" name="Identification" required><br>
                <label for="psw">password</label>
                <input type="password" name="psw" required><br>
                <button type="submit">Sign In</button>
            </form><br>
            <a href="register.php">Not registered ? Do it here</a> <span>  ||  </span> <a href="page.php">Home</a>
        </div>
    </div>
</body>
</html>