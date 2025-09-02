<?php
require_once "./blueprints/page_init.php";
require_once "./blueprints/gl_ap_start.php";

$error_msg = "";

if (isset($_GET['specie_id'])) {
    $specie_id = (int)$_GET['specie_id'];
    
    try {
        // Get species information
        $stmt = $pdo->prepare("SELECT * FROM species WHERE id_specie = ?");
        $stmt->execute([$specie_id]);
        $speciesInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$speciesInfo) {
            $error_msg = "Species not found.";
        } else {
            // Get all races for this species with their characters
            $stmt = $pdo->prepare("
                SELECT r.*, 
                       COUNT(c.id_character) as character_count
                FROM races r 
                LEFT JOIN characters c ON r.id_race = c.correspondence 
                WHERE r.correspondence = ? 
                GROUP BY r.id_race 
                ORDER BY r.race_name
            ");
            $stmt->execute([$specie_id]);
            $races = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error_msg = "Database error: " . $e->getMessage();
    }
} else {
    $error_msg = "No species selected.";
}
?>
?>

<script>
    // Intersection Observer to fade in the elements when they are in the viewport
    document.addEventListener("DOMContentLoaded", function() {
        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("visible");
                    observer.unobserve(entry.target); // stop observing the element once it's visible
                }
            });
        });

        document.querySelectorAll(".fadeIn").forEach(element => {
            observer.observe(element);
        });
    });
</script>

<div id='mainText'>
    <button id="Return" onclick="window.history.back()">Return</button>
    
    <?php if ($error_msg): ?>
        <div class="error-message">
            <span class='title'><?php echo htmlspecialchars($error_msg); ?></span>
        </div>
    <?php 
        require_once "./blueprints/gl_ap_end.php"; 
        exit; 
    endif; ?>
    
    <!-- Header Section -->
    <div class="beings-header">
        <h1><?php echo htmlspecialchars($speciesInfo['specie_name']); ?> Characters</h1>
        <p>All characters belonging to the <?php echo htmlspecialchars($speciesInfo['specie_name']); ?> species</p>
        
        <!-- Admin Tools -->
        <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
        <div class="admin-tools">
            <button class="admin-btn" onclick="openCharacterAdminModal()">
                <span>⚙️</span> Manage Characters
            </button>
        </div>
        <?php endif; ?>
    </div>
    <!-- Characters organized by Race -->
    <div class="characters-by-race">
        <?php if (empty($races)): ?>
            <div class="no-races-message">
                <p>No races found for this species.</p>
                <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
                    <button class="btn-add" onclick="addRaceToSpecies(<?php echo $specie_id; ?>)">
                        Add First Race
                    </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($races as $race): 
                // Get characters for this race
                $charactersQuery = $pdo->prepare("SELECT * FROM characters WHERE correspondence = ? ORDER BY character_name");
                $charactersQuery->execute([$race['id_race']]);
                $characters = $charactersQuery->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <div class="race-section" data-race-id="<?php echo $race['id_race']; ?>">
                <!-- Race Header -->
                <div class="race-header-section">
                    <div class="race-title-info">
                        <?php 
                        $raceImg = $race['icon_race'];
                        if (empty($raceImg)) {
                            $raceImgPath = '../images/icon_default.png';
                        } else {
                            $raceImgPath = '../images/' . str_replace(' ', '_', $raceImg);
                            if (!isImageLinkValid($raceImgPath)) {
                                $raceImgPath = '../images/icon_invalide.png';
                            }
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($raceImgPath); ?>" 
                             alt="<?php echo htmlspecialchars($race['race_name']); ?>"
                             class="race-icon"
                             onerror="this.src='../images/icon_default.png'">
                        <h2 class="race-title"><?php echo htmlspecialchars($race['race_name']); ?></h2>
                        <span class="character-count">(<?php echo count($characters); ?> character<?php echo count($characters) !== 1 ? 's' : ''; ?>)</span>
                    </div>
                    
                    <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
                    <div class="race-admin-actions">
                        <button class="btn-add-character" onclick="addCharacterToRace(<?php echo $race['id_race']; ?>)">
                            Add Character
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Race Description -->
                <?php if (!empty($race['content_race'])): ?>
                <div class="race-description">
                    <p><?php echo nl2br(htmlspecialchars($race['content_race'])); ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Characters Grid -->
                <?php if (empty($characters)): ?>
                    <div class="no-characters-message">
                        <p>No characters defined for the <?php echo htmlspecialchars($race['race_name']); ?> race yet.</p>
                        <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
                            <button class="btn-add" onclick="addCharacterToRace(<?php echo $race['id_race']; ?>)">
                                Add First Character
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="characters-grid">
                        <?php foreach ($characters as $character): 
                            // Character image handling
                            $characterImg = $character['icon_character'];
                            if (empty($characterImg)) {
                                $characterImgPath = '../images/icon_default.png';
                            } else {
                                $characterImgPath = '../images/' . str_replace(' ', '_', $characterImg);
                                if (!isImageLinkValid($characterImgPath)) {
                                    $characterImgPath = '../images/icon_invalide.png';
                                }
                            }
                            
                            $age = $character['age'];
                            if ($age == null || $age == '') {
                                $age = NOT_SPECIFIED;
                            } else {
                                $age = $age . ' years';
                            }
                            
                            $habitat = $character['habitat'] ?? NOT_SPECIFIED;
                            $country = $character['country'] ?? NOT_SPECIFIED;
                        ?>
                        
                        <div class="character-card fadeIn" data-character-id="<?php echo $character['id_character']; ?>">
                            <div class="character-content">
                                <div class="character-header">
                                    <div class="character-image">
                                        <img src="<?php echo htmlspecialchars($characterImgPath); ?>" 
                                             alt="<?php echo htmlspecialchars($character['character_name']); ?>"
                                             onerror="this.src='../images/icon_default.png'">
                                    </div>
                                    <div class="character-info">
                                        <h3 class="character-name"><?php echo htmlspecialchars($character['character_name']); ?></h3>
                                        <div class="character-stats">
                                            <div class="stat-item">
                                                <span class="stat-label">Age:</span>
                                                <span class="stat-value"><?php echo htmlspecialchars($age); ?></span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-label">Origin:</span>
                                                <span class="stat-value"><?php echo htmlspecialchars($habitat); ?></span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-label">Country:</span>
                                                <span class="stat-value"><?php echo htmlspecialchars($country); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
                                    <div class="character-admin-actions">
                                        <button class="btn-edit-character" onclick="editCharacter(<?php echo $character['id_character']; ?>)" title="Edit Character">
                                            ✏️
                                        </button>
                                        <button class="btn-delete-character" onclick="confirmDeleteCharacter(<?php echo $character['id_character']; ?>, '<?php echo htmlspecialchars($character['character_name']); ?>')" title="Delete Character">
                                            🗑️
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Character Description -->
                                <?php if (!empty($character['content_character'])): ?>
                                <div class="character-description">
                                    <p><?php echo nl2br(htmlspecialchars($character['content_character'])); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Admin Modal for Character Management -->
<?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
<div id="characterAdminModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; z-index: 9999;">
    <div class="modal-content">
        <span class="close" onclick="closeCharacterAdminModal()">&times;</span>
        <div id="characterAdminModalContent">
            <!-- Character admin interface will be loaded here -->
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Intersection Observer for fade-in animations
document.addEventListener("DOMContentLoaded", function() {
    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("visible");
                observer.unobserve(entry.target);
            }
        });
    });

    document.querySelectorAll(".fadeIn").forEach(element => {
        observer.observe(element);
    });
});
</script>

<?php
// Output JavaScript functions for character management
if (isset($_SESSION['user']) && in_array('admin', $user_roles)) {
    echo "<script>\n";
    echo generateSharedJavaScriptUtilities() . "\n";
    echo generateCharacterPageFunctions('./scriptes/Character_admin_interface.php') . "\n";
    echo generateEntityDeleteFunctions('character', './scriptes/Character_admin_interface.php') . "\n";
    echo "</script>\n";
}
?>

<?php
require_once "./blueprints/gl_ap_end.php"; // includes the end of the general page file
?>

