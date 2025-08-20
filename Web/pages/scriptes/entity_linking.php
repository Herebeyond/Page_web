<?php
/**
 * Entity Linking System
 * Automatically converts entity names in content to clickable links
 */

// Prevent direct access - only include when needed
if (basename($_SERVER['PHP_SELF']) == 'entity_linking.php') {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Get all entity names from various tables
 * @return array Array of entities with name, type, and page info
 */
function getAllEntityNames() {
    global $pdo;
    
    if (!isset($pdo) || !$pdo) {
        return [];
    }
    
    $entities = [];
    
    try {
        // Get characters with their IDs and race names for proper linking
        $stmt = $pdo->prepare("
            SELECT c.character_name as name, 'character' as type, 
                   CONCAT('Character_display.php?character_id=', c.id_character, '&race=', COALESCE(r.race_name, '')) as page
            FROM characters c
            LEFT JOIN races r ON c.correspondence = r.id_race
            WHERE c.character_name IS NOT NULL AND c.character_name != '' AND LENGTH(TRIM(c.character_name)) >= 3
        ");
        $stmt->execute();
        $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $entities = array_merge($entities, $characters);
        
        // Get species with proper linking to race display page
        $stmt = $pdo->prepare("
            SELECT specie_name as name, 'species' as type, 
                   CONCAT('Races_display.php?specie=', REPLACE(specie_name, ' ', '_')) as page
            FROM species 
            WHERE specie_name IS NOT NULL AND specie_name != '' AND LENGTH(TRIM(specie_name)) >= 3
        ");
        $stmt->execute();
        $species = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $entities = array_merge($entities, $species);
        
        // Get races with proper linking to race display page with specie context
        $stmt = $pdo->prepare("
            SELECT r.race_name as name, 'race' as type, 
                   CONCAT('Races_display.php?specie=', REPLACE(s.specie_name, ' ', '_'), '&race=', REPLACE(r.race_name, ' ', '_')) as page
            FROM races r
            LEFT JOIN species s ON r.correspondence = s.id_specie
            WHERE r.race_name IS NOT NULL AND r.race_name != '' AND LENGTH(TRIM(r.race_name)) >= 3
        ");
        $stmt->execute();
        $races = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $entities = array_merge($entities, $races);
        
        // Get gods with proper linking (assuming similar pattern to characters)
        $stmt = $pdo->prepare("
            SELECT gods_name as name, 'god' as type, 
                   CONCAT('God_display.php?god_id=', gods_id) as page
            FROM gods 
            WHERE gods_name IS NOT NULL AND gods_name != '' AND LENGTH(TRIM(gods_name)) >= 3
        ");
        $stmt->execute();
        $gods = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $entities = array_merge($entities, $gods);
        
        // Get places/interest points with proper linking to map view
        $stmt = $pdo->prepare("
            SELECT name_IP as name, 'place' as type, 
                   CONCAT('Map_view.php?highlight=', id_IP) as page
            FROM interest_points 
            WHERE name_IP IS NOT NULL AND name_IP != '' AND LENGTH(TRIM(name_IP)) >= 3
        ");
        $stmt->execute();
        $places = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $entities = array_merge($entities, $places);
        
        // Clean and filter entities
        $cleanedEntities = [];
        foreach ($entities as $entity) {
            $cleanName = trim($entity['name']);
            if (!empty($cleanName) && strlen($cleanName) >= 3) {
                $entity['name'] = $cleanName;
                $cleanedEntities[] = $entity;
            }
        }
        
        // Remove duplicates based on name (case-insensitive)
        $uniqueEntities = [];
        $seenNames = [];
        foreach ($cleanedEntities as $entity) {
            $lowerName = strtolower($entity['name']);
            if (!in_array($lowerName, $seenNames)) {
                $seenNames[] = $lowerName;
                $uniqueEntities[] = $entity;
            }
        }
        
        return $uniqueEntities;
        
    } catch (Exception $e) {
        error_log("Error fetching entities: " . $e->getMessage());
        return [];
    }
}

/**
 * Convert a word between singular and plural forms
 * @param string $word The word to convert
 * @return array Array containing both singular and plural variations
 */
function getWordVariations($word) {
    $variations = [strtolower($word)];
    $lowerWord = strtolower($word);
    
    // Basic English pluralization rules
    if (substr($lowerWord, -1) === 's') {
        // Potentially plural, try to make singular
        if (substr($lowerWord, -3) === 'ies') {
            $variations[] = substr($lowerWord, 0, -3) . 'y'; // stories -> story
        } elseif (substr($lowerWord, -3) === 'ses') {
            $variations[] = substr($lowerWord, 0, -2); // glasses -> glass
        } elseif (substr($lowerWord, -4) === 'shes' || substr($lowerWord, -4) === 'ches') {
            $variations[] = substr($lowerWord, 0, -2); // wishes -> wish, watches -> watch
        } else {
            $variations[] = substr($lowerWord, 0, -1); // gods -> god
        }
    } else {
        // Potentially singular, try to make plural
        if (substr($lowerWord, -1) === 'y' && !in_array(substr($lowerWord, -2, 1), ['a', 'e', 'i', 'o', 'u'])) {
            $variations[] = substr($lowerWord, 0, -1) . 'ies'; // story -> stories
        } elseif (in_array(substr($lowerWord, -1), ['s', 'x', 'z']) || 
                  in_array(substr($lowerWord, -2), ['sh', 'ch'])) {
            $variations[] = $lowerWord . 'es'; // glass -> glasses, wish -> wishes
        } else {
            $variations[] = $lowerWord . 's'; // god -> gods
        }
    }
    
    return array_unique($variations);
}

/**
 * Extract n-grams (word combinations) from text
 * @param string $text The text to extract n-grams from
 * @param int $n The number of words in each n-gram
 * @return array Array of n-grams with their positions
 */
function extractNGrams($text, $n) {
    // Remove existing HTML tags for clean word extraction
    $cleanText = strip_tags($text);
    
    // Split into words, preserving positions
    preg_match_all('/\b\w+\b/u', $cleanText, $matches, PREG_OFFSET_CAPTURE);
    $words = $matches[0];
    
    $ngrams = [];
    for ($i = 0; $i <= count($words) - $n; $i++) {
        $ngramWords = [];
        $startPos = $words[$i][1];
        $endPos = $words[$i + $n - 1][1] + strlen($words[$i + $n - 1][0]);
        
        for ($j = 0; $j < $n; $j++) {
            $ngramWords[] = $words[$i + $j][0];
        }
        
        $ngrams[] = [
            'text' => implode(' ', $ngramWords),
            'start' => $startPos,
            'end' => $endPos,
            'length' => $endPos - $startPos
        ];
    }
    
    return $ngrams;
}

/**
 * Generate cartesian product of arrays (all combinations)
 * @param array $arrays Array of arrays to combine
 * @return array Array of all combinations
 */
function cartesianProduct($arrays) {
    $result = [[]];
    foreach ($arrays as $array) {
        $temp = [];
        foreach ($result as $resultItem) {
            foreach ($array as $arrayItem) {
                $temp[] = array_merge($resultItem, [$arrayItem]);
            }
        }
        $result = $temp;
    }
    return $result;
}

/**
 * Process text content to add entity links with multi-word and plural/singular support
 * @param string $content The content to process
 * @param bool $forDisplay Whether this is for display (real-time) or permanent storage
 * @return string Content with entity links added
 */
function processEntityLinks($content, $forDisplay = true) {
    if (empty($content)) {
        return $content;
    }
    
    try {
        $entities = getAllEntityNames();
        if (empty($entities)) {
            return $content;
        }
        
        // First, protect existing links and HTML tags by replacing them with placeholders
        $protectedContent = $content;
        $protectedElements = [];
        $counter = 0;
        
        // Protect existing links
        $protectedContent = preg_replace_callback('/<a\s[^>]*>.*?<\/a>/is', function($matches) use (&$protectedElements, &$counter) {
            $placeholder = "___PROTECTED_LINK_" . $counter . "___";
            $protectedElements[$placeholder] = $matches[0];
            $counter++;
            return $placeholder;
        }, $protectedContent);
        
        // Protect other HTML tags
        $protectedContent = preg_replace_callback('/<[^>]+>/i', function($matches) use (&$protectedElements, &$counter) {
            $placeholder = "___PROTECTED_TAG_" . $counter . "___";
            $protectedElements[$placeholder] = $matches[0];
            $counter++;
            return $placeholder;
        }, $protectedContent);
        
        // Create entity lookup with variations
        $entityLookup = [];
        foreach ($entities as $entity) {
            $entityName = trim($entity['name']);
            if (strlen($entityName) < 3) continue;
            
            // Split entity name into words and get variations
            $entityWords = explode(' ', $entityName);
            $entityVariations = [];
            
            // Generate all combinations of word variations
            $wordVariationSets = [];
            foreach ($entityWords as $word) {
                $wordVariationSets[] = getWordVariations($word);
            }
            
            // Generate cartesian product of all word variations
            $variationCombinations = cartesianProduct($wordVariationSets);
            foreach ($variationCombinations as $combination) {
                $variationText = implode(' ', $combination);
                if (!isset($entityLookup[$variationText]) || 
                    strlen($entityName) > strlen($entityLookup[$variationText]['original_name'])) {
                    $entityLookup[$variationText] = [
                        'entity' => $entity,
                        'original_name' => $entityName,
                        'word_count' => count($entityWords)
                    ];
                }
            }
        }
        
        // Find all potential matches for 1-4 word combinations
        $allMatches = [];
        
        for ($wordCount = 4; $wordCount >= 1; $wordCount--) { // Process longer matches first
            $ngrams = extractNGrams($protectedContent, $wordCount);
            
            foreach ($ngrams as $ngram) {
                $ngramLower = strtolower($ngram['text']);
                
                if (isset($entityLookup[$ngramLower])) {
                    $entityData = $entityLookup[$ngramLower];
                    
                    // Check if this position is already covered by a longer match
                    $covered = false;
                    foreach ($allMatches as $existingMatch) {
                        if ($ngram['start'] >= $existingMatch['start'] && 
                            $ngram['end'] <= $existingMatch['end']) {
                            $covered = true;
                            break;
                        }
                    }
                    
                    if (!$covered) {
                        $allMatches[] = [
                            'start' => $ngram['start'],
                            'end' => $ngram['end'],
                            'length' => $ngram['length'],
                            'text' => $ngram['text'],
                            'entity' => $entityData['entity'],
                            'original_name' => $entityData['original_name']
                        ];
                    }
                }
            }
        }
        
        // Sort matches by position (descending) to replace from end to beginning
        usort($allMatches, function($a, $b) {
            return $b['start'] - $a['start'];
        });
        
        // Apply replacements
        foreach ($allMatches as $match) {
            $entity = $match['entity'];
            $linkClass = $forDisplay ? 'entity-link' : 'entity-link-stored';
            
            // Create simple link without overlapping complexity
            $replacement = '<a href="' . htmlspecialchars($entity['page']) . '" ' .
                          'class="' . $linkClass . ' entity-' . htmlspecialchars($entity['type']) . '" ' .
                          'data-entity-type="' . htmlspecialchars($entity['type']) . '" ' .
                          'title="' . htmlspecialchars($match['original_name']) . ' (' . $entity['type'] . ')">' . 
                          htmlspecialchars($match['text']) . '</a>';
            
            // Find the exact position in the protected content
            $beforeText = substr($protectedContent, 0, $match['start']);
            $afterText = substr($protectedContent, $match['end']);
            
            $protectedContent = $beforeText . $replacement . $afterText;
        }
        
        // Restore protected elements
        foreach ($protectedElements as $placeholder => $originalContent) {
            $protectedContent = str_replace($placeholder, $originalContent, $protectedContent);
        }
        
        return $protectedContent;
        
    } catch (Exception $e) {
        error_log("Error in processEntityLinks: " . $e->getMessage());
        return $content; // Return original content if there's an error
    }
}

/**
 * Process entity links for a specific idea
 * @param int $ideaId The ID of the idea to process
 * @return bool Success status
 */
function processIdeaEntityLinks($ideaId) {
    global $pdo;
    
    if (!isset($pdo) || !$pdo) {
        return false;
    }
    
    try {
        // Get the current content
        $stmt = $pdo->prepare("SELECT content FROM universe_ideas WHERE id_idea = ?");
        $stmt->execute([$ideaId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return false;
        }
        
        $originalContent = $result['content'];
        $processedContent = processEntityLinks($originalContent, false); // false = for permanent storage
        
        // Only update if content actually changed
        if ($processedContent !== $originalContent) {
            $updateStmt = $pdo->prepare("UPDATE universe_ideas SET content = ? WHERE id_idea = ?");
            return $updateStmt->execute([$processedContent, $ideaId]);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error processing entity links for idea $ideaId: " . $e->getMessage());
        return false;
    }
}

/**
 * Process entity links for all ideas
 * @return array Results summary
 */
function processAllIdeasEntityLinks() {
    global $pdo;
    
    if (!isset($pdo) || !$pdo) {
        return ['success' => false, 'message' => 'Database connection not available'];
    }
    
    try {
        // First check if we have any entities to link
        $entities = getAllEntityNames();
        if (empty($entities)) {
            return [
                'success' => true,
                'message' => 'No entities found to link',
                'processed' => 0,
                'updated' => 0,
                'errors' => 0
            ];
        }
        
        // Get all ideas
        $stmt = $pdo->prepare("SELECT id_idea, content FROM universe_ideas WHERE content IS NOT NULL AND content != ''");
        $stmt->execute();
        $ideas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($ideas)) {
            return [
                'success' => true,
                'message' => 'No ideas found to process',
                'processed' => 0,
                'updated' => 0,
                'errors' => 0
            ];
        }
        
        $processed = 0;
        $updated = 0;
        $errors = 0;
        
        foreach ($ideas as $idea) {
            $processed++;
            $originalContent = $idea['content'];
            
            // Strip any existing entity links for a complete hard reset
            $cleanContent = preg_replace('/<a[^>]*class="[^"]*entity-link[^"]*"[^>]*>(.*?)<\/a>/', '$1', $originalContent);
            
            // Process content for permanent storage
            $processedContent = processEntityLinks($cleanContent, false);
            
            // Always update for hard reset (don't check if content changed)
            try {
                $updateStmt = $pdo->prepare("UPDATE universe_ideas SET content = ? WHERE id_idea = ?");
                if ($updateStmt->execute([$processedContent, $idea['id_idea']])) {
                    $updated++;
                } else {
                    $errors++;
                }
            } catch (Exception $e) {
                $errors++;
                error_log("Error updating idea " . $idea['id_idea'] . ": " . $e->getMessage());
            }
        }
        
        $message = "Hard reset completed: Processed $processed ideas";
        if ($updated > 0) {
            $message .= ", updated $updated with entity links";
        }
        if ($errors > 0) {
            $message .= ", $errors errors occurred";
        }
        
        return [
            'success' => true,
            'message' => $message,
            'processed' => $processed,
            'updated' => $updated,
            'errors' => $errors,
            'entities_available' => count($entities)
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error processing ideas: ' . $e->getMessage()
        ];
    }
}
?>