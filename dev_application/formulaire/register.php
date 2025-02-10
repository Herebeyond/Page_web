<?php
session_start();
require 'db.php'; // Connexion à la base


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['Identification']); // Nettoyage des entrées utilisateur
    $password = trim($_POST['psw']);

    // Vérification que les champs ne sont pas vides
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
        header('Location: register.php');
        exit;
    }


    // Vérification si l'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Ce nom d'utilisateur est déjà pris.";
        header('Location: register.php');
        exit;
    }


    // Hacher le mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Sauvegarder l'utilisateur et son mot de passe dans la base de données
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hashedPassword]);


    header('Location: login.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
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
            <h2> Register </h2>
            <form method="POST" action="register.php">
                <label for="Identification">Identification</label>
                <input type="text" name="Identification" required><br>
                <label for="psw">password</label>
                <input type="password" name="psw" required><br>
                <button type="submit">Register</button>
            </form><br>
            <a href="login.php">Already registered ? Sign In</a> <span>  ||  </span> <a href="page.php">Home</a>
        </div>
    </div>
</body>
</html>