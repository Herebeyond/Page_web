<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file



// Initialize variables for form fields
$username = '';
$password = '';
$email = '';
$errors = [];


$maxDim = 1000; // Maximum dimension for the icon image

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate the input
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';


    // VERIFICATION PART
    // Password and confirm_password needs to be either both empty or filled
    if ((empty($password) && !empty($confirm_password)) || (!empty($password) && empty($confirm_password))) {
        array_push($errors, "You need to fill both password and confirm password.");
    }

    // Check if the password and confirm_password are the same
    if (!empty($password) && !empty($confirm_password) && $password !== $confirm_password) {
        array_push($errors, "The password and confirm password do not match.");
    }

    // Check if the password is the same as the old password
    if (!empty($password)) {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $old_password = $stmt->fetchColumn();

        if (password_verify($password, $old_password)) {
            array_push($errors, "The new password cannot be the same as the old password.");
        }
    }


    // VALIDATION PART
    // If password isn't empty, check if the password is to the right format
    if (!empty($password)) {
        if (strlen($password) < 8 || strlen($password) > 50) { // Check if the password is between 8 and 50 characters
            array_push($errors, "The password must contain between 8 and 50 characters.");
        }
        if (!preg_match('/^[a-zA-Z0-9_@]+$/u', $password)) { // Check if the password contains only letters, numbers, underscores, and @
            array_push($errors, 'The password can only contain letters, numbers, underscores, and @.');
        }
    }

    // If username isn't empty, check if the username is to the right format
    if (!empty($username)) {
        // Check if the username already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $user['id']]);
        if ($stmt->fetch()) {
            array_push($errors, "This username is already taken.");
        }
        if (strlen($username) < 3 || strlen($username) > 15) { // Check if the username is between 3 and 15 characters
            array_push($errors, "The username must contain between 3 and 15 characters.");
        }
        if (!preg_match('/^[a-zA-Z0-9_@]+$/u', $username)) { // Check if the username contains only letters, numbers, underscores, and @
            array_push($errors, "The username can only contain letters, numbers, underscores, and @.");
        }
    }
    
    // Validate the email format
    if (!empty($email) && (!filter_var($email, FILTER_VALIDATE_EMAIL))) {
        array_push($errors, "Invalid email format.");
    }


    // ICON UPLOAD PART
    $iconFileName = '';
    if (isset($_FILES['icon']) && $_FILES['icon']['error'] !== UPLOAD_ERR_NO_FILE) {
        $iconTmpPath = $_FILES['icon']['tmp_name'];
        $iconOriginalName = $_FILES['icon']['name'];
        $iconSize = $_FILES['icon']['size'];

        // Check if file is an image
        $imageInfo = getimagesize($iconTmpPath);
        if ($imageInfo === false) {
            array_push($errors, "Uploaded file is not a valid image.");
        } else {
            // Check allowed image formats
            $allowedFormats = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($imageInfo['mime'], $allowedFormats)) {
                array_push($errors, "Image format not allowed. Allowed formats: jpg, jpeg, png, gif, webp.");
            } else {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
                if ($width > $maxDim || $height > $maxDim) {
                    array_push($errors, "Image dimensions must be at most 300x300 pixels. Please upload a smaller image.");
                } else {
                    // Save with unique name (keep original extension)
                    $iconExt = strtolower(pathinfo($iconOriginalName, PATHINFO_EXTENSION));
                    $uniqueId = uniqid('_', true);
                    $iconFileName = pathinfo($iconOriginalName, PATHINFO_FILENAME) . $uniqueId . '.' . $iconExt;
                    $destPath = "../images/small_icon/" . $iconFileName;
                    if (!move_uploaded_file($iconTmpPath, $destPath)) {
                        array_push($errors, "Failed to save the icon image.");
                    }
                }
            }
        }
    }

    // If no errors, update only the fields that have been filled in
    if (empty($errors)) {
        $fields = [];
        $params = [];

        if (!empty($username)) {
            $fields[] = 'username = ?';
            $params[] = $username;
        }
        if (!empty($email)) {
            $fields[] = 'email = ?';
            $params[] = $email;
        }
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $fields[] = 'password = ?';
            $params[] = $hashedPassword;
        }
        if (!empty($iconFileName)) {
            $fields[] = 'icon = ?';
            $params[] = $iconFileName;
        }

        if (!empty($fields)) {
            $params[] = $user['id'];
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Optionally update $user session data if needed
            if (!empty($username)) $user['username'] = $username;
            if (!empty($email)) $user['email'] = $email;
            if (!empty($iconFileName)) $user['icon'] = $iconFileName;

            $_SESSION['success'] = "Profile updated successfully.";
            header('Location: User_profil.php');
            exit;
        } else {
            array_push($errors, "No fields to update.");
        }
    }

    
}






require_once "./blueprints/gl_ap_start.php";


?>




<div id="mainText" style="opacity: 100%;"> 
    <button id="Return" onclick="window.history.back()">Return</button><br>

    <h2>Profil informations</h2>
    <br><br>


    <!-- Personnal user profil -->
    <!-- The user informations have already been fetched in header.php in the admin verification part -->
    <form method="POST" action="User_profil.php" onsubmit="return validateForm()" enctype="multipart/form-data">
        <label for="icon">Icon (size limite <?php echo $maxDim . " x " . $maxDim ?> px): </label>
        <br><br>
        <?php
        // Display the current icon if it exists
        if (!empty($user['icon'])) {
            echo '<img src="../images/small_icon/' . htmlspecialchars($user['icon']) . '" alt="User Icon" style="width: 100px; height: 100px;"><br>';
            echo $user['icon'];
        } else {
            echo 'No icon selected';
        }
        ?>
        <br><br>
        <input type="file" id="icon_parameters" name="icon" accept="image/*">
        <br><br>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="<?php echo sanitize_output($user['username']); ?>">
        <br><br>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="<?php echo sanitize_output($user['email']); ?>">
        <br><br>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter new password">
        <br><br>
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
        <br><br>
        <button type="submit">Update</button>
        <br><br>
        <?php
        // Display errors
        for ($i = 0; $i < count($errors); $i++) {
            echo "<span class=error style='color:red;'>" . htmlspecialchars($errors[$i]) . "</span><br>";
        }
        // Display success message
        if (isset($_SESSION['success'])) {
            echo "<span class=success style='color:green;'>" . htmlspecialchars($_SESSION['success']) . "</span><br>";
            unset($_SESSION['success']);
        }
        ?>
        
    </form>



</div>





















<?php
require_once "./blueprints/gl_ap_end.php";
?>