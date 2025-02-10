
<?php
session_start(); // Démarre la session pour récupérer les données (nom prénom) de Lettre.php
?>

<html>
    <head>
        <link rel="stylesheet" href="./styles/style.css?ver=<?php echo time(); ?>">
        <title>Félicitations</title>
    </head>
    <body>
        <div id="global">

            <div id="header">
                Joyeux Noël <br>
                Ton cadeau est commandé
            </div>

            <div id=englobeIntro>
                <div id="content">
                    <?php
                    // Récupération des données de la session
                    $surname = isset($_SESSION['surname']) ? htmlspecialchars($_SESSION['surname']) : "Inconnu";
                    $name = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : "Inconnu";
                    ?>

                    <h1>Félicitations <?php echo $surname . ' ' . $name; ?> !</h1>
                    <p>Ton cadeau a bien été commandé. Le Père Noël est très fier de toi et te souhaite un merveilleux Noël !</p>
                </div>

                <div id=link>
                    <a onclick='window.location.href="./Intro.php"'> Vers l'intro </a>
                </div>
            </div>
        </div>
    </body>
</html>
