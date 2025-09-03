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
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id_user = ?");
        $stmt->execute([$user['id_user']]);
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
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND id_user != ?");
        $stmt->execute([$username, $user['id_user']]);
        if ($stmt->fetch()) {
            array_push($errors, "This username is already taken.");
        }
        // Check username length
        if (strlen($username) < 3 || strlen($username) > 15) { 
            array_push($errors, "The username must contain between 3 and 15 characters.");
        }
        // Check username format
        if (!preg_match('/^[a-zA-Z0-9_@]+$/u', $username)) { 
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
            // Check allowed image formats (MIME types)
            $allowedFormats = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($imageInfo['mime'], $allowedFormats)) {
                array_push($errors, "Image format not allowed. Allowed formats: jpg, jpeg, png, gif, webp.");
            } else {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
                // Check image dimensions
                if ($width > $maxDim || $height > $maxDim) {
                    array_push($errors, "Image dimensions must be at most " . $maxDim . "x" . $maxDim . " pixels. Please upload a smaller image.");
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
            $params[] = $user['id_user'];
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id_user = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Optionally update $user session data if needed
            if (!empty($username)) {
                $user['username'] = $username;
            }
            if (!empty($email)) {
                $user['email'] = $email;
            }
            if (!empty($iconFileName)) {
                $user['icon'] = $iconFileName;
            }

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




<div id="mainText" class="content-page"> 
    <button id="Return" onclick="window.history.back()">Return</button>

    <div class="user-profile-container">
        <h2 class="user-profile-title">Profile Information</h2>

        <!-- Profile Settings Section -->
        <div class="profile-section">
            <h3>Account Settings</h3>
            <form method="POST" action="User_profil.php" onsubmit="return validateForm()" enctype="multipart/form-data">
                
                <!-- Current Icon Display -->
                <div class="profile-form-group vertical">
                    <label>Current Profile Icon</label>
                    <div class="user-icon-display">
                        <?php
                        if (!empty($user['icon'])) {
                            echo '<img src="../images/small_icon/' . htmlspecialchars($user['icon']) . '" alt="User Icon" onclick="openIconModal(\'' . htmlspecialchars($user['icon']) . '\')" title="Click to view full size">';
                            echo '<div class="user-icon-info">';
                            echo '<strong>Current icon:</strong><br>' . htmlspecialchars($user['icon']);
                            echo '<br><small style="color: rgba(255, 255, 255, 0.6);">Click icon to view full size</small>';
                            echo '</div>';
                        } else {
                            echo '<img src="../images/small_icon/default_user_icon.png" alt="Default Icon" onclick="openIconModal(\'default_user_icon.png\')" title="Click to view full size">';
                            echo '<div class="user-icon-info">';
                            echo '<strong>No custom icon set</strong><br>Using default icon';
                            echo '<br><small style="color: rgba(255, 255, 255, 0.6);">Click icon to view full size</small>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <div class="size-limit-note">
                        Maximum size: <?php echo $maxDim . "x" . $maxDim ?> pixels
                    </div>
                </div>

                <!-- Upload New Icon -->
                <div class="profile-form-group">
                    <label for="icon">Upload New Icon:</label>
                    <input type="file" id="icon_parameters" name="icon" accept="image/*">
                </div>

                <!-- Username -->
                <div class="profile-form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" placeholder="<?php echo sanitize_output($user['username']); ?>">
                </div>

                <!-- Email -->
                <div class="profile-form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="<?php echo sanitize_output($user['email']); ?>">
                </div>

                <!-- Password -->
                <div class="profile-form-group">
                    <label for="password">New Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter new password">
                </div>

                <!-- Confirm Password -->
                <div class="profile-form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="profile-submit-btn">Update Profile</button>

                <!-- Messages -->
                <?php
                // Display errors
                for ($i = 0; $i < count($errors); $i++) {
                    echo '<div class="profile-message error">' . htmlspecialchars($errors[$i]) . '</div>';
                }
                // Display success message
                if (isset($_SESSION['success'])) {
                    echo '<div class="profile-message success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                    unset($_SESSION['success']);
                }
                ?>
            </form>
        </div>

        <!-- User Roles Section -->
        <div class="profile-section">
            <h3>Your Roles</h3>
            <p style="color: rgba(255, 255, 255, 0.8); margin-bottom: 15px;">Here are the roles assigned to your account:</p>
            <ul class="user-roles-list">
                <?php
                // Display user roles using shared parsing function
                $roles = parseUserRoles($user['user_roles'] ?? '');
                
                if (!empty($roles)) {
                    foreach ($roles as $role) {
                        echo "<li>" . htmlspecialchars(ucfirst($role)) . "</li>";
                    }
                } else {
                    echo '<li style="color: rgba(255, 255, 255, 0.6); font-style: italic;">No roles assigned</li>';
                }
                ?>
            </ul>
        </div>
    </div>
    
    <!-- Icon Modal -->
    <div id="iconModal" class="icon-modal">
        <div class="icon-modal-content">
            <button class="icon-modal-close" onclick="closeIconModal()">&times;</button>
            <div class="icon-modal-title">Profile Icon</div>
            <img id="modalIconImage" class="icon-modal-image" src="" alt="Full Size Icon">
        </div>
    </div>
</div>

<script>
// Icon Modal Functions
function openIconModal(iconFileName) {
    const modal = document.getElementById('iconModal');
    const modalImage = document.getElementById('modalIconImage');
    
    // Set the image source
    modalImage.src = '../images/small_icon/' + iconFileName;
    modalImage.alt = 'Full Size: ' + iconFileName;
    
    // Reset modal title
    document.querySelector('.icon-modal-title').textContent = 'Profile Icon';
    
    // Show modal
    modal.classList.add('show');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeIconModal() {
    const modal = document.getElementById('iconModal');
    modal.classList.remove('show');
    document.body.style.overflow = 'auto'; // Restore scrolling
}

// Function to open modal with data URL (for preview images)
function openIconModalFromDataURL(dataURL) {
    const modal = document.getElementById('iconModal');
    const modalImage = document.getElementById('modalIconImage');
    
    // Set the image source to the data URL
    modalImage.src = dataURL;
    modalImage.alt = 'Preview: New Icon';
    
    // Update modal title for preview
    document.querySelector('.icon-modal-title').textContent = 'Preview: New Icon';
    
    // Show modal
    modal.classList.add('show');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

// Close modal when clicking outside the content
document.getElementById('iconModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeIconModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeIconModal();
    }
});

// Form validation and enhancement
function validateForm() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const fileInput = document.getElementById('icon_parameters');
    
    // Password validation
    if (password && password.length < 8) {
        alert('Password must be at least 8 characters long.');
        return false;
    }
    
    // Password confirmation check
    if (password !== confirmPassword) {
        alert('Password and confirm password do not match.');
        return false;
    }
    
    // File size validation (client-side check)
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (file.size > maxSize) {
            alert('File size must be less than 5MB.');
            return false;
        }
        
        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (JPEG, PNG, GIF, or WebP).');
            return false;
        }
    }
    
    return true;
}

// Add visual feedback for form fields
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.profile-form-group input');
    
    inputs.forEach(input => {
        // Add focus effect
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
        
        // Add typing effect for password fields
        if (input.type === 'password') {
            input.addEventListener('input', function() {
                const strength = getPasswordStrength(this.value);
                showPasswordStrength(this, strength);
            });
        }
    });
    
    // File input preview
    const fileInput = document.getElementById('icon_parameters');
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const existingPreview = document.querySelector('.icon-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }
                
                const preview = document.createElement('div');
                preview.className = 'icon-preview';
                preview.innerHTML = `
                    <img src="${e.target.result}" 
                         style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid #d4af37; margin-top: 10px; cursor: pointer; transition: all 0.3s ease;" 
                         onclick="openIconModalFromDataURL('${e.target.result}')" 
                         title="Click to view full size"
                         onmouseover="this.style.transform='scale(1.05)'; this.style.borderColor='#f4cf47';"
                         onmouseout="this.style.transform='scale(1)'; this.style.borderColor='#d4af37';">
                    <p style="color: #f4cf47; font-size: 0.9em; margin-top: 5px;">Preview of new icon<br><small style="color: rgba(255, 255, 255, 0.6);">Click to view full size</small></p>
                `;
                fileInput.parentElement.appendChild(preview);
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
});

function getPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    return strength;
}

function showPasswordStrength(input, strength) {
    let existingIndicator = input.parentElement.querySelector('.password-strength');
    if (existingIndicator) {
        existingIndicator.remove();
    }
    
    if (input.value.length > 0) {
        const indicator = document.createElement('div');
        indicator.className = 'password-strength';
        indicator.style.cssText = `
            margin-top: 5px;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
            transition: all 0.3s ease;
        `;
        
        const strengthTexts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        const strengthColors = ['#ff4444', '#ff8844', '#ffaa44', '#88ff44', '#44ff44'];
        
        indicator.textContent = `Password Strength: ${strengthTexts[strength] || 'Very Weak'}`;
        indicator.style.color = strengthColors[strength] || '#ff4444';
        indicator.style.backgroundColor = 'rgba(0, 0, 0, 0.3)';
        indicator.style.border = `1px solid ${strengthColors[strength] || '#ff4444'}`;
        
        input.parentElement.appendChild(indicator);
    }
}
</script>

<?php
require_once "./blueprints/gl_ap_end.php";
?>

