<?php
/**
 * Universe Ideas Management API
 * Handles CRUD operations for the universe ideas system
 */

require_once '../../login/db.php';

// Verify database connection was successful
if (!isset($pdo) || !$pdo) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_ideas':
            getIdeas();
            break;
        case 'get_idea':
            getIdea();
            break;
        case 'create_idea':
            createIdea();
            break;
        case 'update_idea':
            updateIdea();
            break;
        case 'delete_idea':
            deleteIdea();
            break;
        case 'get_parent_options':
            getParentOptions();
            break;
        case 'export_ideas':
            exportIdeas();
            break;
        case 'get_stats':
            getStats();
            break;
        case 'bulk_import':
            bulkImport();
            break;
        default:
            throw new Exception('Invalid action specified');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getIdeas() {
    global $pdo;
    
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause based on filters
    $where = ['ui.is_active = TRUE'];
    $params = [];
    
    if (!empty($_GET['search'])) {
        $search = '%' . $_GET['search'] . '%';
        $where[] = '(ui.title LIKE ? OR ui.content LIKE ? OR ui.tags LIKE ? OR ui.comments LIKE ?)';
        $params = array_merge($params, [$search, $search, $search, $search]);
    }
    
    if (!empty($_GET['category'])) {
        $where[] = 'ui.category = ?';
        $params[] = $_GET['category'];
    }
    
    if (!empty($_GET['certainty'])) {
        $where[] = 'ui.certainty_level = ?';
        $params[] = $_GET['certainty'];
    }
    
    if (!empty($_GET['priority'])) {
        $where[] = 'ui.priority = ?';
        $params[] = $_GET['priority'];
    }
    
    if (!empty($_GET['status'])) {
        $where[] = 'ui.status = ?';
        $params[] = $_GET['status'];
    }
    
    if (!empty($_GET['parent_id'])) {
        $where[] = 'ui.parent_idea_id = ?';
        $params[] = $_GET['parent_id'];
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Get total count
    $countQuery = "SELECT COUNT(*) FROM universe_ideas ui WHERE $whereClause";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetchColumn();
    
    // Get ideas with pagination
    $query = "
        SELECT ui.*, 
               COALESCE(parent.title, 'Root Idea') as parent_title,
               (SELECT COUNT(*) FROM universe_ideas WHERE parent_idea_id = ui.id_idea AND is_active = TRUE) as child_count
        FROM universe_ideas ui
        LEFT JOIN universe_ideas parent ON ui.parent_idea_id = parent.id_idea
        WHERE $whereClause
        ORDER BY ui.created_at DESC
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $ideas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    $stats = getStatistics();
    
    echo json_encode([
        'success' => true,
        'ideas' => $ideas,
        'total_count' => $totalCount,
        'total_pages' => ceil($totalCount / $limit),
        'current_page' => $page,
        'stats' => $stats
    ]);
}

function getIdea() {
    global $pdo;
    
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception('Invalid idea ID');
    }
    
    $query = "
        SELECT ui.*, 
               COALESCE(parent.title, 'Root Idea') as parent_title,
               (SELECT COUNT(*) FROM universe_ideas WHERE parent_idea_id = ui.id_idea AND is_active = TRUE) as child_count
        FROM universe_ideas ui
        LEFT JOIN universe_ideas parent ON ui.parent_idea_id = parent.id_idea
        WHERE ui.id_idea = ? AND ui.is_active = TRUE
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $idea = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$idea) {
        throw new Exception('Idea not found');
    }
    
    echo json_encode([
        'success' => true,
        'idea' => $idea
    ]);
}

function createIdea() {
    global $pdo;
    
    // Validate required fields
    $requiredFields = ['title', 'content', 'category', 'certainty_level'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Sanitize and validate data
    $data = [
        'title' => trim($_POST['title']),
        'content' => trim($_POST['content']),
        'category' => $_POST['category'],
        'certainty_level' => $_POST['certainty_level'],
        'priority' => $_POST['priority'] ?? 'Medium',
        'status' => $_POST['status'] ?? 'Draft',
        'language' => $_POST['language'] ?? 'French',
        'world_impact' => $_POST['world_impact'] ?? 'Local',
        'ease_of_modification' => $_POST['ease_of_modification'] ?? 'Medium',
        'tags' => !empty($_POST['tags']) ? $_POST['tags'] : null,
        'parent_idea_id' => !empty($_POST['parent_idea_id']) ? intval($_POST['parent_idea_id']) : null,
        'implementation_notes' => !empty($_POST['implementation_notes']) ? trim($_POST['implementation_notes']) : null,
        'comments' => !empty($_POST['comments']) ? trim($_POST['comments']) : null,
        'inspiration_source' => !empty($_POST['inspiration_source']) ? trim($_POST['inspiration_source']) : null
    ];
    
    // Validate enums
    $validCategories = ['Magic_Systems', 'Creatures', 'Gods_Demons', 'Dimensions_Realms', 'Physics_Reality', 'Races_Beings', 'Items_Artifacts', 'Lore_History', 'Geography', 'Politics', 'Technology', 'Culture', 'Other'];
    $validCertainties = ['Idea', 'Not_Sure', 'Developing', 'Established', 'Canon'];
    $validPriorities = ['Low', 'Medium', 'High', 'Critical'];
    $validStatuses = ['Draft', 'In_Progress', 'Review', 'Finalized', 'Archived'];
    $validLanguages = ['French', 'English', 'Mixed'];
    $validImpacts = ['Local', 'Regional', 'Global', 'Universal', 'Dimensional'];
    $validEases = ['Easy', 'Medium', 'Hard', 'Core_Element'];
    
    if (!in_array($data['category'], $validCategories)) {
        throw new Exception('Invalid category');
    }
    if (!in_array($data['certainty_level'], $validCertainties)) {
        throw new Exception('Invalid certainty level');
    }
    if (!in_array($data['priority'], $validPriorities)) {
        throw new Exception('Invalid priority');
    }
    if (!in_array($data['status'], $validStatuses)) {
        throw new Exception('Invalid status');
    }
    if (!in_array($data['language'], $validLanguages)) {
        throw new Exception('Invalid language');
    }
    if (!in_array($data['world_impact'], $validImpacts)) {
        throw new Exception('Invalid world impact');
    }
    if (!in_array($data['ease_of_modification'], $validEases)) {
        throw new Exception('Invalid ease of modification');
    }
    
    // Validate parent idea exists if provided
    if ($data['parent_idea_id']) {
        $stmt = $pdo->prepare("SELECT id_idea FROM universe_ideas WHERE id_idea = ? AND is_active = TRUE");
        $stmt->execute([$data['parent_idea_id']]);
        if (!$stmt->fetch()) {
            throw new Exception('Parent idea not found');
        }
    }
    
    // Validate tags JSON format if provided
    if ($data['tags']) {
        $tags = json_decode($data['tags'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid tags format');
        }
    }
    
    $query = "
        INSERT INTO universe_ideas (
            title, content, category, certainty_level, priority, status, language,
            world_impact, ease_of_modification, tags, parent_idea_id, 
            implementation_notes, comments, inspiration_source
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    
    $stmt = $pdo->prepare($query);
    $success = $stmt->execute([
        $data['title'], $data['content'], $data['category'], $data['certainty_level'],
        $data['priority'], $data['status'], $data['language'], $data['world_impact'],
        $data['ease_of_modification'], $data['tags'], $data['parent_idea_id'],
        $data['implementation_notes'], $data['comments'], $data['inspiration_source']
    ]);
    
    if ($success) {
        $ideaId = $pdo->lastInsertId();
        echo json_encode([
            'success' => true,
            'message' => 'Idea created successfully',
            'idea_id' => $ideaId
        ]);
    } else {
        throw new Exception('Failed to create idea');
    }
}

function updateIdea() {
    global $pdo;
    
    $id = intval($_POST['ideaId'] ?? 0);
    if ($id <= 0) {
        throw new Exception('Invalid idea ID');
    }
    
    // Check if idea exists
    $stmt = $pdo->prepare("SELECT id_idea FROM universe_ideas WHERE id_idea = ? AND is_active = TRUE");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception('Idea not found');
    }
    
    // Validate required fields
    $requiredFields = ['title', 'content', 'category', 'certainty_level'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Sanitize and validate data (same validation as create)
    $data = [
        'title' => trim($_POST['title']),
        'content' => trim($_POST['content']),
        'category' => $_POST['category'],
        'certainty_level' => $_POST['certainty_level'],
        'priority' => $_POST['priority'] ?? 'Medium',
        'status' => $_POST['status'] ?? 'Draft',
        'language' => $_POST['language'] ?? 'French',
        'world_impact' => $_POST['world_impact'] ?? 'Local',
        'ease_of_modification' => $_POST['ease_of_modification'] ?? 'Medium',
        'tags' => !empty($_POST['tags']) ? $_POST['tags'] : null,
        'parent_idea_id' => !empty($_POST['parent_idea_id']) ? intval($_POST['parent_idea_id']) : null,
        'implementation_notes' => !empty($_POST['implementation_notes']) ? trim($_POST['implementation_notes']) : null,
        'comments' => !empty($_POST['comments']) ? trim($_POST['comments']) : null,
        'inspiration_source' => !empty($_POST['inspiration_source']) ? trim($_POST['inspiration_source']) : null
    ];
    
    // Prevent self-parenting
    if ($data['parent_idea_id'] == $id) {
        throw new Exception('An idea cannot be its own parent');
    }
    
    // Prevent circular references
    if ($data['parent_idea_id']) {
        if (hasCircularReference($id, $data['parent_idea_id'])) {
            throw new Exception('Circular reference detected in parent-child relationship');
        }
    }
    
    $query = "
        UPDATE universe_ideas SET 
            title = ?, content = ?, category = ?, certainty_level = ?, priority = ?, 
            status = ?, language = ?, world_impact = ?, ease_of_modification = ?, 
            tags = ?, parent_idea_id = ?, implementation_notes = ?, comments = ?, 
            inspiration_source = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id_idea = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $success = $stmt->execute([
        $data['title'], $data['content'], $data['category'], $data['certainty_level'],
        $data['priority'], $data['status'], $data['language'], $data['world_impact'],
        $data['ease_of_modification'], $data['tags'], $data['parent_idea_id'],
        $data['implementation_notes'], $data['comments'], $data['inspiration_source'], $id
    ]);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Idea updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update idea');
    }
}

function deleteIdea() {
    global $pdo;
    
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception('Invalid idea ID');
    }
    
    // Check if idea exists
    $stmt = $pdo->prepare("SELECT id_idea FROM universe_ideas WHERE id_idea = ? AND is_active = TRUE");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception('Idea not found');
    }
    
    // Check if idea has children
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM universe_ideas WHERE parent_idea_id = ? AND is_active = TRUE");
    $stmt->execute([$id]);
    $childCount = $stmt->fetchColumn();
    
    if ($childCount > 0) {
        // Update children to become root ideas instead of hard delete
        $pdo->prepare("UPDATE universe_ideas SET parent_idea_id = NULL WHERE parent_idea_id = ?")->execute([$id]);
    }
    
    // Soft delete the idea
    $stmt = $pdo->prepare("UPDATE universe_ideas SET is_active = FALSE, updated_at = CURRENT_TIMESTAMP WHERE id_idea = ?");
    $success = $stmt->execute([$id]);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Idea deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete idea');
    }
}

function getParentOptions() {
    global $pdo;
    
    $query = "
        SELECT id_idea, title 
        FROM universe_ideas 
        WHERE is_active = TRUE 
        ORDER BY title ASC
    ";
    
    $stmt = $pdo->query($query);
    $ideas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'ideas' => $ideas
    ]);
}

function getStatistics() {
    global $pdo;
    
    $stats = [];
    
    // Total ideas
    $stmt = $pdo->query("SELECT COUNT(*) FROM universe_ideas WHERE is_active = TRUE");
    $stats['total'] = $stmt->fetchColumn();
    
    // Canon ideas
    $stmt = $pdo->query("SELECT COUNT(*) FROM universe_ideas WHERE is_active = TRUE AND certainty_level = 'Canon'");
    $stats['canon'] = $stmt->fetchColumn();
    
    // Developing ideas
    $stmt = $pdo->query("SELECT COUNT(*) FROM universe_ideas WHERE is_active = TRUE AND certainty_level = 'Developing'");
    $stats['developing'] = $stmt->fetchColumn();
    
    // Categories count
    $stmt = $pdo->query("SELECT COUNT(DISTINCT category) FROM universe_ideas WHERE is_active = TRUE");
    $stats['categories'] = $stmt->fetchColumn();
    
    return $stats;
}

function getStats() {
    echo json_encode([
        'success' => true,
        'stats' => getStatistics()
    ]);
}

function exportIdeas() {
    global $pdo;
    
    $query = "
        SELECT ui.*, 
               COALESCE(parent.title, 'Root Idea') as parent_title,
               (SELECT COUNT(*) FROM universe_ideas WHERE parent_idea_id = ui.id_idea AND is_active = TRUE) as child_count
        FROM universe_ideas ui
        LEFT JOIN universe_ideas parent ON ui.parent_idea_id = parent.id_idea
        WHERE ui.is_active = TRUE
        ORDER BY ui.category, ui.title
    ";
    
    $stmt = $pdo->query($query);
    $ideas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="universe_ideas_' . date('Y-m-d_H-i-s') . '.csv"');
    
    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fwrite($output, "\xEF\xBB\xBF");
    
    // Add CSV headers
    $headers = [
        'ID', 'Title', 'Category', 'Certainty Level', 'Priority', 'Status', 'Language',
        'World Impact', 'Ease of Modification', 'Parent Title', 'Child Count', 'Tags',
        'Content', 'Implementation Notes', 'Comments', 'Inspiration Source', 'Created At', 'Updated At'
    ];
    
    fputcsv($output, $headers);
    
    // Add data rows
    foreach ($ideas as $idea) {
        $tags = $idea['tags'] ? implode('; ', json_decode($idea['tags'], true)) : '';
        
        $row = [
            $idea['id_idea'],
            $idea['title'],
            str_replace('_', ' ', $idea['category']),
            str_replace('_', ' ', $idea['certainty_level']),
            $idea['priority'],
            str_replace('_', ' ', $idea['status']),
            $idea['language'],
            $idea['world_impact'],
            str_replace('_', ' ', $idea['ease_of_modification']),
            $idea['parent_title'],
            $idea['child_count'],
            $tags,
            $idea['content'],
            $idea['implementation_notes'],
            $idea['comments'],
            $idea['inspiration_source'],
            $idea['created_at'],
            $idea['updated_at']
        ];
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

function hasCircularReference($ideaId, $parentId) {
    global $pdo;
    
    $visited = [$ideaId];
    $currentParent = $parentId;
    
    while ($currentParent) {
        if (in_array($currentParent, $visited)) {
            return true; // Circular reference found
        }
        
        $visited[] = $currentParent;
        
        // Get the parent of current parent
        $stmt = $pdo->prepare("SELECT parent_idea_id FROM universe_ideas WHERE id_idea = ? AND is_active = TRUE");
        $stmt->execute([$currentParent]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $currentParent = $result ? $result['parent_idea_id'] : null;
    }
    
    return false;
}

function bulkImport() {
    global $pdo;
    
    $ideasText = $_POST['ideasText'] ?? '';
    $defaultCategory = $_POST['defaultCategory'] ?? 'Other';
    $defaultCertainty = $_POST['defaultCertainty'] ?? 'Idea';
    $defaultLanguage = $_POST['defaultLanguage'] ?? 'French';
    
    if (empty($ideasText)) {
        throw new Exception('No ideas text provided');
    }
    
    // Split ideas by separator
    $rawIdeas = explode('---', $ideasText);
    $importedCount = 0;
    $errors = [];
    $details = [];
    
    foreach ($rawIdeas as $index => $rawIdea) {
        $rawIdea = trim($rawIdea);
        if (empty($rawIdea)) continue;
        
        try {
            // Parse the idea text
            $idea = parseIdeaText($rawIdea, $defaultCategory, $defaultCertainty, $defaultLanguage);
            
            if (empty($idea['title']) || empty($idea['content'])) {
                $errors[] = "Idea " . ($index + 1) . ": Missing title or content";
                continue;
            }
            
            // Insert the idea
            $query = "
                INSERT INTO universe_ideas (
                    title, content, category, certainty_level, language, tags, 
                    priority, status, world_impact, ease_of_modification
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            
            $stmt = $pdo->prepare($query);
            $success = $stmt->execute([
                $idea['title'],
                $idea['content'],
                $idea['category'],
                $idea['certainty_level'],
                $idea['language'],
                $idea['tags'],
                $idea['priority'],
                $idea['status'],
                $idea['world_impact'],
                $idea['ease_of_modification']
            ]);
            
            if ($success) {
                $importedCount++;
                $details[] = "✅ " . $idea['title'];
            } else {
                $errors[] = "Failed to insert: " . $idea['title'];
            }
            
        } catch (Exception $e) {
            $errors[] = "Idea " . ($index + 1) . ": " . $e->getMessage();
        }
    }
    
    echo json_encode([
        'success' => true,
        'imported_count' => $importedCount,
        'errors' => $errors,
        'details' => implode('<br>', $details)
    ]);
}

function parseIdeaText($text, $defaultCategory, $defaultCertainty, $defaultLanguage) {
    $lines = explode("\n", $text);
    $idea = [
        'title' => '',
        'content' => '',
        'category' => $defaultCategory,
        'certainty_level' => $defaultCertainty,
        'language' => $defaultLanguage,
        'tags' => null,
        'priority' => 'Medium',
        'status' => 'Draft',
        'world_impact' => 'Local',
        'ease_of_modification' => 'Medium'
    ];
    
    $currentSection = '';
    $contentLines = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Check for field definitions
        if (preg_match('/^(Title|Content|Tags|Category|Certainty|Language|Priority|Status|Impact|Ease):\s*(.*)$/i', $line, $matches)) {
            // Save previous content if we were building content
            if ($currentSection === 'content' && !empty($contentLines)) {
                $idea['content'] = implode("\n", $contentLines);
                $contentLines = [];
            }
            
            $field = strtolower($matches[1]);
            $value = trim($matches[2]);
            
            switch ($field) {
                case 'title':
                    $idea['title'] = $value;
                    $currentSection = 'title';
                    break;
                case 'content':
                    $currentSection = 'content';
                    if (!empty($value)) {
                        $contentLines[] = $value;
                    }
                    break;
                case 'tags':
                    if (!empty($value)) {
                        $tags = array_map('trim', explode(',', $value));
                        $idea['tags'] = json_encode($tags);
                    }
                    $currentSection = 'tags';
                    break;
                case 'category':
                    $validCategories = ['Magic_Systems', 'Creatures', 'Gods_Demons', 'Dimensions_Realms', 'Physics_Reality', 'Races_Beings', 'Items_Artifacts', 'Lore_History', 'Geography', 'Politics', 'Technology', 'Culture', 'Other'];
                    $value = str_replace(' ', '_', $value);
                    if (in_array($value, $validCategories)) {
                        $idea['category'] = $value;
                    }
                    $currentSection = 'category';
                    break;
                case 'certainty':
                    $validCertainties = ['Idea', 'Not_Sure', 'Developing', 'Established', 'Canon'];
                    $value = str_replace(' ', '_', $value);
                    if (in_array($value, $validCertainties)) {
                        $idea['certainty_level'] = $value;
                    }
                    $currentSection = 'certainty';
                    break;
                case 'language':
                    $validLanguages = ['French', 'English', 'Mixed'];
                    if (in_array($value, $validLanguages)) {
                        $idea['language'] = $value;
                    }
                    $currentSection = 'language';
                    break;
                default:
                    $currentSection = '';
            }
        } else {
            // Continue building content
            if ($currentSection === 'content') {
                $contentLines[] = $line;
            }
        }
    }
    
    // Save final content if we were building it
    if ($currentSection === 'content' && !empty($contentLines)) {
        $idea['content'] = implode("\n", $contentLines);
    }
    
    return $idea;
}
?>
