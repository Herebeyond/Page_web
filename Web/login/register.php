<?php
session_start();
require 'db.php'; // Connexion à la base

// Initialiser les variables pour les champs de formulaire
$username = '';
$password = '';
$errors = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['Identification']); // Nettoyage des entrées utilisateur
    $password = trim($_POST['psw']);

    
    // Vérification si l'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        array_push($errors, "This username is already taken.");
    } else { // Si l'utilisateur n'est pas déjà prit, vérifier les autres conditions
        // Vérification que les champs ne sont pas vides
        if (empty($username) || empty($password)) {
            array_push($errors, "Please fill in all fields.");
        }

        // Vérification de la longueur du nom d'utilisateur
        if (strlen($username) < 3 or strlen($username) > 15) { // Vérifie si le nom d'utilisateur est entre 3 et 15 caractères
            array_push($errors, "The username must contain between 3 and 15 characters.");
        }

        // Vérification de la longueur du mot de passe
        if (strlen($password) < 8 or strlen($password) > 50) { // Vérifie si le mot de passe est entre 8 et 50 caractères
            array_push($errors, "The password must contain between 8 and 50 characters.");
        }

        // Vérification de la validité du nom d'utilisateur
        if (!preg_match('/^[a-zA-Z0-9_@]+$/u', $username)) { // Vérifie si le nom d'utilisateur contient uniquement des lettres, des chiffres, des underscores et des @
            array_push($errors, "The username can only contain letters, numbers, underscores and @.");
        }
    }

    // Si aucune erreur, hacher le mot de passe et sauvegarder l'utilisateur
    if (empty($errors)) {
        // Hacher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Sauvegarder l'utilisateur et son mot de passe dans la base de données
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);

        // Rediriger vers la page de connexion
        header('Location: login.php');
        exit;
    } else {
        for ($i = 0; $i < count($errors); $i++) {
            echo "<span class=error>" . $errors[$i] . "</span><br>";
        }
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
            <h2> Register </h2>
            <form method="POST" action="register.php">
                <label for="Identification">Identification</label>
                <input type="text" name="Identification" value="<?php echo htmlspecialchars($username); ?>" required><br>
                <label for="psw">password</label>
                <input type="password" name="psw" value="<?php echo htmlspecialchars($password); ?>" required><br>
                <button type="submit">Register</button>
            </form><br>
            <a href="login.php">Already registered ? Sign In</a> <span>  ||  </span> <a href="page.php">Home</a>
        </div>
    </div>
</body>
</html>