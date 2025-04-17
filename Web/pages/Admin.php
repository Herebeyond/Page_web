<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file



require_once "./blueprints/gl_ap_start.php"; // includes the start of the general page file

if (isset($_SESSION['user'])) {
    // Retrieve the username from the database to check if the user is an admin
    $stmt = $pdo->prepare("SELECT admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']]);
    $user = $stmt->fetch();
} else {
    $user = null; // if the user is not logged in, set user to null
}
?>





<div id="mainText"> <!-- Right div -->
    <button id="Return" onclick="window.history.back()">Return</button><br>

    <h2>Admin page</h2>
    <form method="POST" action="Admin.php" onsubmit="return validateForm()">
        <label>Users</label>
        <select name="User"> <!-- Dropdown selection to choose a user -->
            <option value="">Select a user</option>
            <?php 
                
                $stmt = $pdo->prepare("SELECT * FROM users ORDER BY username;");
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $username = sanitize_output($row['username']);
                    $id = sanitize_output($row['id']);

                    echo '<option value="' . $id . '">' . $id . " - " . $username . '</option>';
                }

            ?>
        </select><br>
        <button type="submit">Submit</button>
        <button type="button" onclick="fetchUserInfo()">Fetch Info</button><br><br>
        <button type="button" onclick="confirmUserDelete()">Delete Race</button>
    </form><br>
    <div id="userInfo"></div>

















    <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color:red;">' . sanitize_output($_SESSION['error']) . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color:green;">' . sanitize_output($_SESSION['success']) . '</p>';
            unset($_SESSION['success']);
        }
    ?>
</div>








<?php
require_once "./blueprints/gl_ap_end.php"; // includes the end of the general page file
?>