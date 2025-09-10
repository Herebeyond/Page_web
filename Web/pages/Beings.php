<?php
require_once "./blueprints/page_init.php";
require_once "./blueprints/gl_ap_start.php";

// Check if this is an AJAX request for partial page refresh
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

// Search and filter parameters
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterSpecie = isset($_GET['specie']) ? trim($_GET['specie']) : '';

// Pagination
$perPage = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Build search conditions
$searchConditions = [];
$searchParams = [];
$paramIndex = 1;

if (!empty($searchTerm)) {
    $searchConditions[] = "(s.specie_name LIKE :param{$paramIndex} OR s.content_specie LIKE :param" . ($paramIndex + 1) . ")";
    $searchParams[":param{$paramIndex}"] = "%$searchTerm%";
    $searchParams[":param" . ($paramIndex + 1)] = "%$searchTerm%";
    $paramIndex += 2;
}

if (!empty($filterSpecie)) {
    $searchConditions[] = "s.specie_name = :param{$paramIndex}";
    $searchParams[":param{$paramIndex}"] = $filterSpecie;
    $paramIndex++;
}

$whereClause = !empty($searchConditions) ? 'WHERE ' . implode(' AND ', $searchConditions) : '';

// Get total count for pagination
$countQuery = "SELECT COUNT(*) FROM species s $whereClause";
$stmt = $pdo->prepare($countQuery);
foreach ($searchParams as $paramName => $paramValue) {
    $stmt->bindValue($paramName, $paramValue);
}
$stmt->execute();
$totalSpecies = $stmt->fetchColumn();
$totalPages = ceil($totalSpecies / $perPage);
$page = min($page, max(1, $totalPages));

$offset = ($page - 1) * $perPage;

// If this is an AJAX request, return only the beings grid content
if ($isAjax) {
    header('Content-Type: text/html; charset=UTF-8');
    ob_start();
}
?>

<div id="mainText"><?php if (!$isAjax): ?>
    <button id="Return" onclick="window.history.back()">Return</button>
    
    <!-- Beings Header and Controls (only for full page load) -->
    <div class="beings-header">
        <h1>Beings & Creatures</h1>
        <p>Explore the diverse species and races that inhabit the Forgotten Worlds</p>
        
        <!-- Admin Tools -->
        <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
        <div class="admin-tools">
            <button class="admin-btn" onclick="openAdminModal()">
                <span>⚙️</span> Manage Beings
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Search and Filter Section -->
    <div class="beings-controls">
        <div class="search-section"><?php endif; ?>
            <form method="GET" action="Beings.php" class="search-form">
                <div class="search-group">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" 
                           placeholder="Search species, races, or content..." class="search-input">
                    <select name="specie" class="filter-select">
                        <option value="">All Species</option>
                        <?php
                        $speciesQuery = $pdo->prepare("SELECT DISTINCT specie_name FROM species ORDER BY specie_name");
                        $speciesQuery->execute();
                        while ($spec = $speciesQuery->fetch(PDO::FETCH_ASSOC)) {
                            $selected = $filterSpecie === $spec['specie_name'] ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($spec['specie_name']) . "' $selected>" . 
                                 htmlspecialchars($spec['specie_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="search-actions">
                    <button type="submit" class="search-btn">Search</button>
                    <a href="Beings.php" class="clear-btn">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Summary -->
    <?php if (!empty($searchTerm) || !empty($filterSpecie)): ?>
    <div class="results-summary">
        <p>
            <?php if (!empty($searchTerm)): ?>
                Results for "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>"
            <?php endif; ?>
            <?php if (!empty($filterSpecie)): ?>
                <?php echo !empty($searchTerm) ? ' in ' : 'Showing '; ?>
                species: <strong><?php echo htmlspecialchars($filterSpecie); ?></strong>
            <?php endif; ?>
            - <?php echo $totalSpecies; ?> species found
        </p>
    </div>
    <?php endif; ?>

    <!-- Main Beings Grid -->
    <div class="beings-grid">
        <?php
        try {
            // Main query to get species
            $query = "SELECT s.* FROM species s $whereClause ORDER BY s.specie_name LIMIT :limit OFFSET :offset";
            
            $stmt = $pdo->prepare($query);
            
            // Bind search parameters with named parameters
            foreach ($searchParams as $paramName => $paramValue) {
                $stmt->bindValue($paramName, $paramValue);
            }
            
            // Bind pagination parameters
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            while ($species = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Get races for this species
                $racesQuery = $pdo->prepare("SELECT * FROM races WHERE correspondence = ? ORDER BY race_name");
                $racesQuery->execute([$species['id_specie']]);
                $races = $racesQuery->fetchAll(PDO::FETCH_ASSOC);

                // Species image handling
                $specieImg = $species['icon_specie'];
                if (empty($specieImg)) {
                    $specieImgPath = '../images/icon_default.png';
                } else {
                    $specieImgPath = '../images/species/' . $specieImg;
                    if (!isImageLinkValid($specieImgPath)) {
                        $specieImgPath = '../images/icon_invalide.png';
                    }
                }
                ?>
                
                <div class="species-card" data-species="<?php echo htmlspecialchars($species['specie_name']); ?>" data-species-id="<?php echo $species['id_specie']; ?>">
                    <!-- Species Header -->
                    <div class="species-header">
                        <div class="species-image">
                            <img src="<?php echo htmlspecialchars($specieImgPath); ?>" 
                                 alt="<?php echo htmlspecialchars($species['specie_name']); ?>"
                                 onerror="this.src='../images/icon_default.png'">
                        </div>
                        <div class="species-info" onclick="window.location.href='./Beings_display.php?specie_id=<?php echo $species['id_specie']; ?>'">
                            <h2 class="species-name"><?php echo htmlspecialchars($species['specie_name']); ?></h2>
                            <p class="species-race-count" data-count-type="race"><?php echo count($races); ?> race(s)</p>
                            <?php if (!empty($species['content_specie'])): ?>
                            <p class="species-description">
                                <?php echo htmlspecialchars(substr($species['content_specie'], 0, 150)); ?>
                                <?php if (strlen($species['content_specie']) > 150): ?>...<?php endif; ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="species-toggle" onclick="window.beingsManager ? window.beingsManager.toggleSpeciesRaces('<?php echo $species['id_specie']; ?>') : console.error('BeingsManager not found')">
                            <span class="toggle-icon">▼</span>
                        </div>
                    </div>

                    <!-- Races Section -->
                    <div class="races-section" id="races-<?php echo $species['id_specie']; ?>">
                        <?php if (empty($races)): ?>
                            <div class="no-races">
                                <p>No races defined for this species yet.</p>
                                <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
                                <button class="btn-add-race" onclick="addRaceToSpecies(<?php echo $species['id_specie']; ?>)">
                                    Add First Race
                                </button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="races-grid">
                                <?php foreach ($races as $race): 
                                    // Get characters for this race
                                    $charactersQuery = $pdo->prepare("SELECT * FROM characters WHERE correspondence = ? ORDER BY character_name");
                                    $charactersQuery->execute([$race['id_race']]);
                                    $characters = $charactersQuery->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    // Race image handling
                                    $raceImg = $race['icon_race'];
                                    if (empty($raceImg)) {
                                        $raceImgPath = '../images/icon_default.png';
                                    } else {
                                        $raceImgPath = '../images/races/' . $raceImg;
                                        if (!isImageLinkValid($raceImgPath)) {
                                            $raceImgPath = '../images/icon_invalide.png';
                                        }
                                    }
                                ?>
                                <div class="race-card" data-race-id="<?php echo $race['id_race']; ?>">
                                    <!-- Race Header -->
                                    <div class="race-header">
                                        <div class="race-content" onclick="viewRaceDetails(<?php echo $species['id_specie']; ?>, <?php echo $race['id_race']; ?>)">
                                            <div class="race-image">
                                                <img src="<?php echo htmlspecialchars($raceImgPath); ?>" 
                                                     alt="<?php echo htmlspecialchars($race['race_name']); ?>"
                                                     onerror="this.src='../images/icon_default.png'">
                                            </div>
                                            <div class="race-info">
                                                <h3 class="race-name"><?php echo htmlspecialchars($race['race_name']); ?></h3>
                                                <p class="race-character-count" data-count-type="character"><?php echo count($characters); ?> character(s)</p>
                                                <div class="race-stats">
                                                    <?php if (!empty($race['lifespan'])): ?>
                                                    <span class="race-stat">
                                                        <strong>Lifespan:</strong> <?php echo htmlspecialchars($race['lifespan']); ?>
                                                    </span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($race['homeworld'])): ?>
                                                    <span class="race-stat">
                                                        <strong>Homeworld:</strong> <?php echo htmlspecialchars($race['homeworld']); ?>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($race['content_race'])): ?>
                                                <p class="race-description">
                                                    <?php echo htmlspecialchars(substr($race['content_race'], 0, 100)); ?>
                                                    <?php if (strlen($race['content_race']) > 100): ?>...<?php endif; ?>
                                                </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if (!empty($characters)): ?>
                                        <div class="race-toggle" onclick="window.beingsManager ? window.beingsManager.toggleRaceCharacters('<?php echo $race['id_race']; ?>') : console.error('BeingsManager not found')">
                                            <span class="toggle-icon">▼</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
                                        <div class="race-admin-actions">
                                            <button class="btn-delete-race" onclick="event.stopPropagation(); confirmDeleteRace(<?php echo $race['id_race']; ?>, '<?php echo htmlspecialchars($race['race_name']); ?>')">
                                                ×
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Characters Section -->
                                    <?php if (!empty($characters)): ?>
                                    <div class="characters-section" id="characters-<?php echo $race['id_race']; ?>">
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
                                            ?>
                                            <div class="character-card" data-character-id="<?php echo $character['id_character']; ?>">
                                                <div class="character-content" onclick="viewSpeciesCharacters(<?php echo $species['id_specie']; ?>)">>
                                                    <div class="character-image">
                                                        <img src="<?php echo htmlspecialchars($characterImgPath); ?>" 
                                                             alt="<?php echo htmlspecialchars($character['character_name']); ?>"
                                                             onerror="this.src='../images/icon_default.png'">
                                                    </div>
                                                    <div class="character-info">
                                                        <h4 class="character-name"><?php echo htmlspecialchars($character['character_name']); ?></h4>
                                                        <?php if (!empty($character['age'])): ?>
                                                        <p class="character-age">Age: <?php echo htmlspecialchars($character['age']); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($character['description'])): ?>
                                                        <p class="character-description">
                                                            <?php echo htmlspecialchars(substr($character['description'], 0, 80)); ?>
                                                            <?php if (strlen($character['description']) > 80): ?>...<?php endif; ?>
                                                        </p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Admin Actions for Species -->
                        <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
                        <div class="species-admin-actions">
                            <button class="btn-edit" onclick="editSpecies(<?php echo $species['id_specie']; ?>)">
                                Edit Species
                            </button>
                            <button class="btn-danger" onclick="confirmDeleteSpecies(<?php echo $species['id_specie']; ?>, '<?php echo htmlspecialchars($species['specie_name']); ?>')">
                                Delete Species
                            </button>
                            <?php if (!empty($races)): ?>
                            <button class="btn-add" onclick="addRaceToSpecies(<?php echo $species['id_specie']; ?>)">
                                Add Race
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
            }
        } catch (PDOException $e) {
            echo '<div class="error-message">Error loading beings: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php
        $currentUrl = 'Beings.php';
        $params = [];
        if (!empty($searchTerm)) $params['search'] = $searchTerm;
        if (!empty($filterSpecie)) $params['specie'] = $filterSpecie;
        
        if ($page > 1): ?>
            <a href="<?php echo $currentUrl . '?' . http_build_query(array_merge($params, ['page' => 1])); ?>" class="page-link">&laquo; First</a>
            <a href="<?php echo $currentUrl . '?' . http_build_query(array_merge($params, ['page' => $page - 1])); ?>" class="page-link">&lsaquo; Previous</a>
        <?php endif; ?>
        
        <?php
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);
        
        for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="<?php echo $currentUrl . '?' . http_build_query(array_merge($params, ['page' => $i])); ?>" 
               class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="<?php echo $currentUrl . '?' . http_build_query(array_merge($params, ['page' => $page + 1])); ?>" class="page-link">Next &rsaquo;</a>
            <a href="<?php echo $currentUrl . '?' . http_build_query(array_merge($params, ['page' => $totalPages])); ?>" class="page-link">Last &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Admin Modal (will be loaded via AJAX if admin) -->
<div id="adminModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; z-index: 9999;">
    <div class="modal-content">
        <span class="close" onclick="closeAdminModal()">&times;</span>
        <div id="adminModalContent">
            <!-- Admin interface will be loaded here -->
        </div>
    </div>
</div>

<script>
// JavaScript functions are now in separate files
</script>

<?php
// Use the new clean JavaScript inclusion approach
if (isset($_SESSION['user']) && in_array('admin', $user_roles)) {
    // For admin users - include all functionality with clean file includes
    echo includeBeingsPageAssets('scriptes/Beings_admin_interface.php', true);
} else {
    // For non-admin users, only include basic functionality
    echo includeJavaScriptAssets(['beings'], [
        'namespace' => 'BeingsConfig',
        'data' => [
            'apiEndpoint' => 'scriptes/Beings_admin_interface.php',
            'isAdmin' => false
        ]
    ]);
}
?>

<?php 
// Handle AJAX requests - return only the content
if ($isAjax) {
    $content = ob_get_clean();
    echo $content;
    exit;
} 
?>

<?php require_once "./blueprints/gl_ap_end.php"; ?>