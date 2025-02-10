
<?php
session_start();
require 'db.php'; // Connexion à la base
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>
<body>
    <p>test2</p>
    <div id="global">
        <div id="Identification">
            <h1>
                <?php
                echo "test3";
                if (isset($_SESSION['user'])) {
                    // Récupérer le nom d'utilisateur depuis la base de données
                    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user']]);
                    $user = $stmt->fetch();
                    echo "Bienvenue, " . htmlspecialchars($user['username']) . "!";
                } else {
                    echo '<a href="login.php">Sign In</a> | <a href="register.php">Register</a>';
                }
                echo "test4";
                ?>
            </h1>
        </div>
        <div id="englobe">
            <p>
                Ceci est une page, une page de test.<br>
                Elle n'est pas bien grande mais c'est déjà un début.<br>
                Alors voilà voilà, ma page ressemble à ça pour le moment.
            </p>
        </div>
        <a href="logout.php">Disconnect</a>
    </div>
</body>
</html>