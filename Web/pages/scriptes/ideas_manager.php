<?php
/**
 * Universe Ideas Management API
 * Handles CRUD operations for the universe ideas system
 */

// Suppress any output that could interfere with JSON response
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require_once '../../database/db.php';
require_once 'entity_linking.php'; // Include entity linking functionality

// Clean any output that might have been generated
ob_clean();

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
        case 'process_all_entity_links':
            $results = processAllIdeasEntityLinks();
            echo json_encode([
                'success' => $results['success'],
                'message' => $results['message'],
                'details' => $results
            ]);
            break;
        case 'get_entities':
            $entities = getAllEntityNames();
            echo json_encode([
                'success' => true,
                'entities' => $entities,
                'count' => count($entities)
            ]);
            break;
        case 'get_stats':
            getStats();
            break;
        case 'get_all_tags':
            getAllTags();
            break;
        case 'bulk_import':
            bulkImport();
            break;
        case 'get_categories':
            getCategories();
            break;
        case 'get_category_options':
            getCategoryOptions();
            break;
        case 'add_category':
            addCategory();
            break;
        case 'update_category':
            updateCategory();
            break;
        case 'delete_category':
            deleteCategory();
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
    $limit = 10; // Items per page
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause based on filters
    $where = [];
    $params = [];
    
    // Handle search - when searching, show ONLY matching ideas (not hierarchies)
    if (!empty($_GET['search'])) {
        $searchTerms = preg_split('/\s+/', trim($_GET['search']));
        $searchTerms = array_filter($searchTerms); // Remove empty terms
        
        if (!empty($searchTerms)) {
            $searchConditions = [];
            
            foreach ($searchTerms as $term) {
                $searchTerm = '%' . $term . '%';
                $searchConditions[] = '(ui.title LIKE ? OR ui.content LIKE ? OR ui.tags LIKE ? OR ui.comments LIKE ?)';
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            // All search terms must be found (AND logic)
            $where[] = '(' . implode(' AND ', $searchConditions) . ')';
        }
    }
    
    // Add other filters
    if (!empty($_GET['category'])) {
        $where[] = 'ui.category = ?';
        $params[] = $_GET['category'];
    }
    
    if (!empty($_GET['certainty'])) {
        $where[] = 'ui.certainty_level = ?';
        $params[] = $_GET['certainty'];
    }
    
    if (!empty($_GET['status'])) {
        $where[] = 'ui.status = ?';
        $params[] = $_GET['status'];
    }
    
    if (!empty($_GET['parent_id'])) {
        $where[] = 'ui.parent_idea_id = ?';
        $params[] = $_GET['parent_id'];
    }
    
    // When searching, show ALL matching ideas (parents AND children)
    // When NOT searching, show only parent ideas with their hierarchies
    if (empty($_GET['search']) && empty($_GET['parent_id'])) {
        // No search - show only parent ideas (for hierarchy display)
        $where[] = 'ui.parent_idea_id IS NULL';
    }
    
    // Build final query
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Count total matching ideas
    $countQuery = "SELECT COUNT(*) FROM ideas ui $whereClause";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetchColumn();
    
    if ($totalCount == 0) {
        echo json_encode([
            'success' => true,
            'ideas' => [],
            'total_count' => 0,
            'total_pages' => 0,
            'current_page' => $page,
            'stats' => getStatistics()
        ]);
        return;
    }
    
    // When searching, return only the matching ideas
    if (!empty($_GET['search'])) {
        // Simple query - return only matching ideas with pagination
        $query = "
            SELECT ui.id_idea, ui.parent_idea_id, ui.title, ui.content, ui.category, 
                   ui.certainty_level, ui.status, ui.tags, ui.comments, ui.inspiration_source,
                   ui.created_at, ui.updated_at, ui.priority,
                   (SELECT COUNT(*) FROM ideas sub WHERE sub.parent_idea_id = ui.id_idea) as child_count,
                   CASE WHEN ui.parent_idea_id IS NULL THEN NULL ELSE 
                       (SELECT title FROM ideas p WHERE p.id_idea = ui.parent_idea_id) 
                   END as parent_title
            FROM ideas ui
            $whereClause
            ORDER BY ui.created_at DESC
            LIMIT $limit OFFSET $offset
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $ideas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } else {
        // No search - get parent ideas and their complete hierarchies
        $parentQuery = "
            SELECT ui.id_idea
            FROM ideas ui
            $whereClause
            ORDER BY ui.created_at DESC
            LIMIT $limit OFFSET $offset
        ";
        
        $parentStmt = $pdo->prepare($parentQuery);
        $parentStmt->execute($params);
        $parentIds = $parentStmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($parentIds)) {
            echo json_encode([
                'success' => true,
                'ideas' => [],
                'total_count' => 0,
                'total_pages' => 0,
                'current_page' => $page,
                'stats' => getStatistics()
            ]);
            return;
        }
        
        // Get complete hierarchies for these parent ideas
        $parentIdPlaceholders = str_repeat('?,', count($parentIds) - 1) . '?';
        $hierarchyQuery = "
            WITH RECURSIVE idea_hierarchy AS (
                -- Base case: Get the selected parent ideas
                SELECT ui.id_idea, ui.parent_idea_id, ui.title, ui.content, ui.category, 
                       ui.certainty_level, ui.status, ui.tags, ui.comments, ui.inspiration_source,
                       ui.created_at, ui.updated_at, ui.priority,
                       (SELECT COUNT(*) FROM ideas sub WHERE sub.parent_idea_id = ui.id_idea) as child_count,
                       CASE WHEN ui.parent_idea_id IS NULL THEN NULL ELSE 
                           (SELECT title FROM ideas p WHERE p.id_idea = ui.parent_idea_id) 
                       END as parent_title
                FROM ideas ui
                WHERE ui.id_idea IN ($parentIdPlaceholders)
                
                UNION ALL
                
                -- Recursive case: Get all children
                SELECT ui.id_idea, ui.parent_idea_id, ui.title, ui.content, ui.category, 
                       ui.certainty_level, ui.status, ui.tags, ui.comments, ui.inspiration_source,
                       ui.created_at, ui.updated_at, ui.priority,
                       (SELECT COUNT(*) FROM ideas sub WHERE sub.parent_idea_id = ui.id_idea) as child_count,
                       CASE WHEN ui.parent_idea_id IS NULL THEN NULL ELSE 
                           (SELECT title FROM ideas p WHERE p.id_idea = ui.parent_idea_id) 
                       END as parent_title
                FROM ideas ui
                INNER JOIN idea_hierarchy ih ON ui.parent_idea_id = ih.id_idea
            )
            SELECT * FROM idea_hierarchy
            ORDER BY COALESCE(parent_idea_id, id_idea), parent_idea_id IS NOT NULL, created_at
        ";
        
        $stmt = $pdo->prepare($hierarchyQuery);
        $stmt->execute($parentIds);
        $ideas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Process entity links for each idea's content before returning
    foreach ($ideas as &$idea) {
        if (!empty($idea['content'])) {
            $idea['content'] = processEntityLinks($idea['content'], true); // true = for display only
        }
    }
    unset($idea); // Break the reference
    
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
        SELECT ui.id_idea, ui.title, ui.content, ui.category, ui.certainty_level, 
               ui.status, ui.tags, ui.parent_idea_id, ui.comments, ui.inspiration_source,
               ui.created_at, ui.updated_at,
               COALESCE(parent.title, 'Root Idea') as parent_title,
               (SELECT COUNT(*) FROM ideas WHERE parent_idea_id = ui.id_idea) as child_count
        FROM ideas ui
        LEFT JOIN ideas parent ON ui.parent_idea_id = parent.id_idea
        WHERE ui.id_idea = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $idea = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$idea) {
        throw new Exception('Idea not found');
    }
    
    // Process entity links for the idea's content before returning
    if (!empty($idea['content'])) {
        $idea['content'] = processEntityLinks($idea['content'], true); // true = for display only
    }
    
    echo json_encode([
        'success' => true,
        'idea' => $idea
    ]);
}

/**
 * Get valid categories from database ENUM definition
 */
function getValidCategories() {
    global $pdo;
    
    $enumQuery = "SHOW COLUMNS FROM ideas LIKE 'category'";
    $enumStmt = $pdo->prepare($enumQuery);
    $enumStmt->execute();
    $enumResult = $enumStmt->fetch(PDO::FETCH_ASSOC);
    
    // Parse ENUM values from the Type column
    $enumString = $enumResult['Type'];
    preg_match_all("/'([^']+)'/", $enumString, $matches);
    return $matches[1];
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
        'status' => $_POST['status'] ?? 'Draft',
        'tags' => !empty($_POST['tags']) ? $_POST['tags'] : null,
        'parent_idea_id' => !empty($_POST['parent_idea_id']) ? intval($_POST['parent_idea_id']) : null,
        'comments' => !empty($_POST['comments']) ? trim($_POST['comments']) : null,
        'inspiration_source' => !empty($_POST['inspiration_source']) ? trim($_POST['inspiration_source']) : null
    ];
    
    // Validate enums
    $validCategories = getValidCategories();
    $validCertainties = ['Idea', 'Not_Sure', 'Developing', 'Established', 'Canon'];
    $validStatuses = ['Draft', 'In_Progress', 'Review', 'Finalized', 'Archived', 'Need_Correction'];
    
    if (!in_array($data['category'], $validCategories)) {
        throw new Exception('Invalid category');
    }
    if (!in_array($data['certainty_level'], $validCertainties)) {
        throw new Exception('Invalid certainty level');
    }
    if (!in_array($data['status'], $validStatuses)) {
        throw new Exception('Invalid status');
    }
    
    // Validate parent idea exists if provided
    if ($data['parent_idea_id']) {
        $stmt = $pdo->prepare("SELECT id_idea FROM ideas WHERE id_idea = ?");
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
        INSERT INTO ideas (
            title, content, category, certainty_level, status,
            tags, parent_idea_id, 
            comments, inspiration_source
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    
    $stmt = $pdo->prepare($query);
    $success = $stmt->execute([
        $data['title'], $data['content'], $data['category'], $data['certainty_level'],
        $data['status'], $data['tags'], $data['parent_idea_id'],
        $data['comments'], $data['inspiration_source']
    ]);
    
    if ($success) {
        $ideaId = $pdo->lastInsertId();
        
        // Process entity links in the content
        processIdeaEntityLinks($ideaId);
        
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
    $stmt = $pdo->prepare("SELECT id_idea FROM ideas WHERE id_idea = ?");
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
        'status' => $_POST['status'] ?? 'Draft',
        'tags' => !empty($_POST['tags']) ? $_POST['tags'] : null,
        'parent_idea_id' => !empty($_POST['parent_idea_id']) ? intval($_POST['parent_idea_id']) : null,
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
        UPDATE ideas SET 
            title = ?, content = ?, category = ?, certainty_level = ?, 
            status = ?, tags = ?, parent_idea_id = ?, 
            comments = ?, inspiration_source = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id_idea = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $success = $stmt->execute([
        $data['title'], $data['content'], $data['category'], $data['certainty_level'],
        $data['status'], $data['tags'], $data['parent_idea_id'],
        $data['comments'], $data['inspiration_source'], $id
    ]);
    
    if ($success) {
        // Process entity links in the updated content
        processIdeaEntityLinks($id);
        
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
    $stmt = $pdo->prepare("SELECT id_idea FROM ideas WHERE id_idea = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception('Idea not found');
    }
    
    // Check if idea has children
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ideas WHERE parent_idea_id = ?");
    $stmt->execute([$id]);
    $childCount = $stmt->fetchColumn();
    
    if ($childCount > 0) {
        // Update children to become root ideas instead of hard delete
        $pdo->prepare("UPDATE ideas SET parent_idea_id = NULL WHERE parent_idea_id = ?")->execute([$id]);
    }
    
    // Actually delete the idea from the database
    $stmt = $pdo->prepare("DELETE FROM ideas WHERE id_idea = ?");
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
        FROM ideas 
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
    $stmt = $pdo->query("SELECT COUNT(*) FROM ideas");
    $stats['total'] = $stmt->fetchColumn();
    
    // Canon ideas
    $stmt = $pdo->query("SELECT COUNT(*) FROM ideas WHERE certainty_level = 'Canon'");
    $stats['canon'] = $stmt->fetchColumn();
    
    // Developing ideas
    $stmt = $pdo->query("SELECT COUNT(*) FROM ideas WHERE certainty_level = 'Developing'");
    $stats['developing'] = $stmt->fetchColumn();
    
    // Categories count
    $stmt = $pdo->query("SELECT COUNT(DISTINCT category) FROM ideas");
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
    
    // Clear any previous output for CSV export
    if (ob_get_length()) ob_clean();
    
    $query = "
        SELECT ui.id_idea, ui.title, ui.content, ui.category, ui.certainty_level, 
               ui.status, ui.tags, ui.parent_idea_id, ui.comments, ui.inspiration_source,
               ui.created_at, ui.updated_at,
               COALESCE(parent.title, 'Root Idea') as parent_title,
               (SELECT COUNT(*) FROM ideas WHERE parent_idea_id = ui.id_idea) as child_count
        FROM ideas ui
        LEFT JOIN ideas parent ON ui.parent_idea_id = parent.id_idea
        ORDER BY ui.category, ui.title
    ";
    
    $stmt = $pdo->query($query);
    $ideas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="ideas_' . date('Y-m-d_H-i-s') . '.csv"');
    
    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fwrite($output, "\xEF\xBB\xBF");
    
    // Add CSV headers
    $headers = [
        'ID', 'Title', 'Category', 'Certainty Level', 'Status', 'Parent Title', 'Child Count', 'Tags',
        'Content', 'Comments', 'Inspiration Source', 'Created At', 'Updated At'
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
            str_replace('_', ' ', $idea['status']),
            $idea['parent_title'],
            $idea['child_count'],
            $tags,
            $idea['content'],
            $idea['comments'] ?? '',
            $idea['inspiration_source'] ?? '',
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
        $stmt = $pdo->prepare("SELECT parent_idea_id FROM ideas WHERE id_idea = ?");
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
            $idea = parseIdeaText($rawIdea, $defaultCategory, $defaultCertainty);
            
            if (empty($idea['title']) || empty($idea['content'])) {
                $errors[] = "Idea " . ($index + 1) . ": Missing title or content";
                continue;
            }
            
            // Insert the idea
            $query = "
                INSERT INTO ideas (
                    title, content, category, certainty_level, tags, 
                    status
                ) VALUES (?, ?, ?, ?, ?, ?)
            ";
            
            $stmt = $pdo->prepare($query);
            $success = $stmt->execute([
                $idea['title'],
                $idea['content'],
                $idea['category'],
                $idea['certainty_level'],
                $idea['tags'],
                $idea['status']
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

function parseIdeaText($text, $defaultCategory, $defaultCertainty) {
    $lines = explode("\n", $text);
    $idea = [
        'title' => '',
        'content' => '',
        'category' => $defaultCategory,
        'certainty_level' => $defaultCertainty,
        'tags' => null,
        'status' => 'Draft'
    ];
    
    $currentSection = '';
    $contentLines = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Check for field definitions
        if (preg_match('/^(Title|Content|Tags|Category|Certainty|Status):\s*(.*)$/i', $line, $matches)) {
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
                    $validCategories = getValidCategories();
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
                case 'status':
                    $validStatuses = ['Draft', 'In_Progress', 'Review', 'Finalized', 'Archived'];
                    if (in_array($value, $validStatuses)) {
                        $idea['status'] = $value;
                    }
                    $currentSection = 'status';
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

function getAllTags() {
    global $pdo;
    
    try {
        $query = "
            SELECT DISTINCT tags 
            FROM ideas 
            WHERE tags IS NOT NULL AND tags != ''
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $allTags = [];
        foreach ($results as $tagJson) {
            $tags = json_decode($tagJson, true);
            if (is_array($tags)) {
                $allTags = array_merge($allTags, $tags);
            }
        }
        
        // Remove duplicates and sort
        $allTags = array_unique($allTags);
        sort($allTags);
        
        echo json_encode([
            'success' => true,
            'tags' => array_values($allTags)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching tags: ' . $e->getMessage()
        ]);
    }
}

/**
 * Get category options for forms (simplified version)
 */
function getCategoryOptions() {
    global $pdo;
    
    try {
        $categories = getValidCategories();
        
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching category options: ' . $e->getMessage()
        ]);
    }
}

/**
 * Get all categories with their usage count
 */
function getCategories() {
    global $pdo;
    
    try {
        // Get all possible category values from the ENUM definition
        $enumQuery = "SHOW COLUMNS FROM ideas LIKE 'category'";
        $enumStmt = $pdo->prepare($enumQuery);
        $enumStmt->execute();
        $enumResult = $enumStmt->fetch(PDO::FETCH_ASSOC);
        
        // Parse ENUM values from the Type column
        $enumString = $enumResult['Type'];
        preg_match_all("/'([^']+)'/", $enumString, $matches);
        $allCategories = $matches[1];
        
        // Get usage count for each category
        $usageQuery = "
            SELECT category, COUNT(*) as count 
            FROM ideas 
            WHERE category IS NOT NULL 
            GROUP BY category
        ";
        $usageStmt = $pdo->prepare($usageQuery);
        $usageStmt->execute();
        $usageCounts = $usageStmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Build result array with all categories
        $categories = [];
        foreach ($allCategories as $category) {
            $categories[] = [
                'name' => $category,
                'count' => $usageCounts[$category] ?? 0
            ];
        }
        
        // Sort by name
        usort($categories, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching categories: ' . $e->getMessage()
        ]);
    }
}

/**
 * Add a new category
 */
function addCategory() {
    global $pdo;
    
    try {
        $categoryName = trim($_POST['category_name'] ?? '');
        
        if (empty($categoryName)) {
            throw new Exception('Category name is required');
        }
        
        // Validate category name format (alphanumeric, underscores only)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $categoryName)) {
            throw new Exception('Category name can only contain letters, numbers, and underscores');
        }
        
        // Get current ENUM values
        $enumQuery = "SHOW COLUMNS FROM ideas LIKE 'category'";
        $enumStmt = $pdo->prepare($enumQuery);
        $enumStmt->execute();
        $enumResult = $enumStmt->fetch(PDO::FETCH_ASSOC);
        
        // Parse current ENUM values
        $enumString = $enumResult['Type'];
        preg_match_all("/'([^']+)'/", $enumString, $matches);
        $currentCategories = $matches[1];
        
        // Check if category already exists
        if (in_array($categoryName, $currentCategories)) {
            throw new Exception('Category already exists');
        }
        
        // Add new category to ENUM
        $currentCategories[] = $categoryName;
        $newEnumValues = "'" . implode("','", $currentCategories) . "'";
        
        $alterQuery = "ALTER TABLE ideas MODIFY COLUMN category ENUM($newEnumValues) NOT NULL DEFAULT 'Other'";
        $pdo->exec($alterQuery);
        
        echo json_encode([
            'success' => true,
            'message' => "Category '$categoryName' added successfully"
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Update/rename a category
 */
function updateCategory() {
    global $pdo;
    
    $oldName = trim($_POST['old_name'] ?? '');
    $newName = trim($_POST['new_name'] ?? '');
    
    if (empty($oldName) || empty($newName)) {
        echo json_encode([
            'success' => false,
            'message' => 'Both old and new category names are required'
        ]);
        return;
    }
    
    if ($oldName === $newName) {
        echo json_encode([
            'success' => false,
            'message' => 'New name must be different from the old name'
        ]);
        return;
    }
    
    // Validate new category name format
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $newName)) {
        echo json_encode([
            'success' => false,
            'message' => 'Category name can only contain letters, numbers, and underscores'
        ]);
        return;
    }
    
    try {
        // Get current ENUM values
        $enumQuery = "SHOW COLUMNS FROM ideas LIKE 'category'";
        $enumStmt = $pdo->prepare($enumQuery);
        $enumStmt->execute();
        $enumResult = $enumStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$enumResult) {
            throw new Exception('Could not retrieve category column information');
        }
        
        // Parse current ENUM values
        $enumString = $enumResult['Type'];
        preg_match_all("/'([^']+)'/", $enumString, $matches);
        $currentCategories = $matches[1];
        
        // Check if old category exists
        if (!in_array($oldName, $currentCategories)) {
            echo json_encode([
                'success' => false,
                'message' => 'Original category not found'
            ]);
            return;
        }
        
        // Check if new category name already exists
        if (in_array($newName, $currentCategories)) {
            echo json_encode([
                'success' => false,
                'message' => 'A category with that name already exists'
            ]);
            return;
        }
        
        // Step 1: Add the new category to the ENUM
        $newCategories = $currentCategories;
        $newCategories[] = $newName;
        $newEnumValues = "'" . implode("','", $newCategories) . "'";
        
        $alterAddQuery = "ALTER TABLE ideas MODIFY COLUMN category ENUM($newEnumValues) NOT NULL DEFAULT 'Other'";
        $addResult = $pdo->exec($alterAddQuery);
        
        if ($addResult === false) {
            throw new Exception('Failed to add new category to database schema');
        }
        
        // Step 2: Update all ideas with the old category to use the new category
        $updateQuery = "UPDATE ideas SET category = ?, updated_at = NOW() WHERE category = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateResult = $updateStmt->execute([$newName, $oldName]);
        
        if (!$updateResult) {
            throw new Exception('Failed to update ideas with new category name');
        }
        
        $affectedRows = $updateStmt->rowCount();
        
        // Step 3: Remove the old category from the ENUM
        $finalCategories = array_filter($newCategories, function($cat) use ($oldName) {
            return $cat !== $oldName;
        });
        $finalEnumValues = "'" . implode("','", $finalCategories) . "'";
        
        $alterRemoveQuery = "ALTER TABLE ideas MODIFY COLUMN category ENUM($finalEnumValues) NOT NULL DEFAULT 'Other'";
        $removeResult = $pdo->exec($alterRemoveQuery);
        
        if ($removeResult === false) {
            throw new Exception('Failed to remove old category from database schema');
        }
        
        // Success response
        echo json_encode([
            'success' => true,
            'message' => "Category updated successfully. $affectedRows idea(s) affected."
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating category: ' . $e->getMessage()
        ]);
    }
}

/**
 * Delete a category (safely move ideas to "Other" and remove from ENUM)
 */
function deleteCategory() {
    global $pdo;
    
    $categoryName = trim($_POST['category_name'] ?? '');
    
    if (empty($categoryName)) {
        echo json_encode([
            'success' => false,
            'message' => 'Category name is required'
        ]);
        return;
    }
    
    if ($categoryName === 'Other') {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete the "Other" category as it serves as the default'
        ]);
        return;
    }
    
    try {
        // Get current ENUM values
        $enumQuery = "SHOW COLUMNS FROM ideas LIKE 'category'";
        $enumStmt = $pdo->prepare($enumQuery);
        $enumStmt->execute();
        $enumResult = $enumStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$enumResult) {
            throw new Exception('Could not retrieve category column information');
        }
        
        // Parse current ENUM values
        $enumString = $enumResult['Type'];
        preg_match_all("/'([^']+)'/", $enumString, $matches);
        $currentCategories = $matches[1];
        
        // Check if category exists
        if (!in_array($categoryName, $currentCategories)) {
            echo json_encode([
                'success' => false,
                'message' => 'Category not found'
            ]);
            return;
        }
        
        // Step 1: Count ideas in this category
        $countQuery = "SELECT COUNT(*) FROM ideas WHERE category = ?";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute([$categoryName]);
        $ideaCount = $countStmt->fetchColumn();
        
        // Step 2: Move all ideas in this category to "Other" (if any)
        if ($ideaCount > 0) {
            $updateQuery = "UPDATE ideas SET category = 'Other', updated_at = NOW() WHERE category = ?";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateResult = $updateStmt->execute([$categoryName]);
            
            if (!$updateResult) {
                throw new Exception('Failed to move ideas to "Other" category');
            }
        }
        
        // Step 3: Remove category from ENUM
        $newCategories = array_filter($currentCategories, function($cat) use ($categoryName) {
            return $cat !== $categoryName;
        });
        $newEnumValues = "'" . implode("','", $newCategories) . "'";
        
        $alterQuery = "ALTER TABLE ideas MODIFY COLUMN category ENUM($newEnumValues) NOT NULL DEFAULT 'Other'";
        $alterResult = $pdo->exec($alterQuery);
        
        if ($alterResult === false) {
            throw new Exception('Failed to remove category from database schema');
        }
        
        // Success response
        $message = "Category '$categoryName' deleted successfully.";
        if ($ideaCount > 0) {
            $message .= " $ideaCount idea(s) moved to 'Other'.";
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting category: ' . $e->getMessage()
        ]);
    }
}
?>
