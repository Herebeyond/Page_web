<?php
require 'blueprints/page_init.php'; // inclut le fichier page_init.php


if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Vérifie si le formulaire a été soumis
    $Specie_name = isset($_POST['Specie_name']) ? trim($_POST['Specie_name']) : ''; // Nettoyage des entrées utilisateur
    $Specie_Icon = isset($_POST['icon_Specie']) ? trim($_POST['icon_Specie']) : '';
    $Specie_content = isset($_POST['Specie_text']) ? trim($_POST['Specie_text']) : '';

    // Préparer la requête SQL dynamique
    $fields = [];
    $params = [];

    if ($Specie_Icon !== '' && $Specie_Icon != null) {
        $fields[] = 'icon_Specie = ?';
        $params[] = $Specie_Icon;
    }
    if ($Specie_content !== '' && $Specie_content != null) {
        $fields[] = 'content_Specie = ?';
        $params[] = $Specie_content;
    }
    $params[] = $Specie_name;

    if (!empty($fields)) {
        $sql = "UPDATE species SET " . implode(', ', $fields) . " WHERE nom_Specie = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Set success message
        $_SESSION['success'] = "Specie updated successfully";
    } else {
        $_SESSION['error'] = "No fields to update";
    }

    header('Location: Specie_edit.php');
    exit;
}
require 'blueprints/gl_ap_start.php'; // inclut le fichier gl_ap_start.php
?>


<div id="textePrincipal"> <!-- Div de droite -->
    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
    <?php // Afficher les messages d'erreur ou de succès après la soumission du formulaire
        if (isset($_SESSION['error'])) {
            echo '<p style="color:red;">' . sanitize_output($_SESSION['error']) . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color:green;">' . sanitize_output($_SESSION['success']) . '</p>';
            unset($_SESSION['success']);
        }
    ?>
    <h2> Modifie a Specie </h2><br>
    <form method="POST" action="Specie_edit.php" onsubmit="return confirmSubmit()">
        <label for="Specie_name">Specie Name</label>
        <select name="Specie_name" required>
            <option value="">Select a specie</option>
            <?php // Récupérer les noms des Specie depuis la base de données et les afficher dans une liste déroulante
                $stmt = $pdo->query("SELECT * FROM species ORDER BY nom_Specie;");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    //$selected = ($row['nom_specie'] == $Specie_name) ? 'selected' : ''; // servait lorsque j'essaiyais de récupérer les données de la Specie sélectionnée automatiquement je crois
                    echo '<option value="' . sanitize_output($row['nom_specie']) . '">' . sanitize_output($row['nom_specie']) . '</option>';
                }
            ?>
        </select><br>
        <label for="Specie_icon">Specie Icon</label>
        <input type="text" name="icon_Specie"><br>
        <label for="Specie_text">Specie content :</label><br>
        <textarea type="text" name="Specie_text"></textarea><br><br>
        <button type="submit">Submit</button> 
        <button type="button" onclick="fetchSpecieInfo()">Fetch Info</button><br><br>
        <button type="button" onclick="confirmSpecieDelete()">Delete Specie</button>
    </form><br>
    <!-- Afficher le text et l'icon de la Specie sélectionnée -->
    <div id="specieInfo"></div>
</div>


<?php require 'blueprints/gl_ap_end.php'; // inclut le fichier gl_ap_end.php ?>