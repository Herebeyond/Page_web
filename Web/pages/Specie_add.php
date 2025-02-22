<?php
require "./blueprints/page_init.php"; // inclut le fichier d'initialisation de la page


if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Vérifie si le formulaire a été soumis
    $Specie_name = isset($_POST['Specie_name']) ? trim($_POST['Specie_name']) : ''; // Nettoyage des entrées utilisateur
    $Specie_Icon = isset($_POST['icon_Specie']) ? trim($_POST['icon_Specie']) : '';
    $Specie_content = isset($_POST['Specie_text']) ? trim($_POST['Specie_text']) : '';


    // Récupérer le nom de la Specie depuis la base de données
    $stmt = $pdo->prepare("SELECT * FROM species WHERE nom_Specie = ?"); 
    $stmt->execute([$Specie_name]);
    $Specie = $stmt->fetch();

    // Vérifier si la Specie existe déjà dans la base, si non alors intégrer la Specie à la base de données
    if ($Specie) {
        $_SESSION['error'] = "Specie already exists";
        header('Location: Specie_add.php');
        exit;
    } else {
        // Insérer la nouvelle Specie dans la base de données
        $stmt = $pdo->prepare("INSERT INTO species (nom_Specie, icon_Specie, content_Specie) VALUES (?, ?, ?)");
        $stmt->execute([$Specie_name, $Specie_Icon, $Specie_content]);
        $_SESSION['success'] = "Specie added successfully";
        header('Location: Specie_add.php');
        exit;
    }
}

require "./blueprints/gl_ap_start.php"; // inclut le fichier d'en-tête de la page
?>


<div id="textePrincipal"> <!-- Div de droite -->
    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
    <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color:red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color:green;">' . htmlspecialchars($_SESSION['success']) . '</p>';
            unset($_SESSION['success']);
        }
    ?>

    <h2> Add a Specie </h2><br>
    <form method="POST" action="Specie_add.php">
        <label for="Specie_name">Specie Name</label>
        <input type="text" name="Specie_name" required><br>
        <label for="Specie_icon">Specie Icon</label>
        <input type="text" name="icon_Specie"><br>
        <label for="Specie_text">Specie content :</label><br>
        <input type="text" name="Specie_text" id="content_input"><br><br>
        <button type="submit">Submit</button>
    </form><br>
</div>



<?php require "./blueprints/gl_ap_end.php"; // inclut le fichier de pied de page ?>