-- Ideas Table Creation Script
-- This table stores all the creative ideas, lore, and concepts for the fantasy universe

CREATE TABLE IF NOT EXISTS `ideas` (
    `id_idea` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `category` ENUM('Cosmology_Creation', 'Demons_Abyssal', 'Magic_Systems', 'Divine_Religious', 'Races_Species', 'Death_Undeath', 'Dungeons_Artifacts', 'Combat_Martial', 'Geography_Environments', 'Physics_Reality', 'Culture_Society', 'Biology_Anatomy', 'Other') DEFAULT 'Other',
    `certainty_level` ENUM('Idea', 'Not_Sure', 'Developing', 'Established', 'Canon') DEFAULT 'Idea',
    `status` ENUM('Draft', 'Need_Correction', 'In_Progress', 'Review', 'Finalized', 'Archived') DEFAULT 'Draft',
    `tags` TEXT DEFAULT NULL,
    `comments` TEXT DEFAULT NULL,
    `inspiration_source` VARCHAR(500) DEFAULT NULL,
    `parent_idea_id` INT DEFAULT NULL,
    `priority` TINYINT DEFAULT 3,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraint for hierarchical structure
    FOREIGN KEY (`parent_idea_id`) REFERENCES `ideas`(`id_idea`) ON DELETE SET NULL,
    
    -- Indexes for better performance
    INDEX `idx_category` (`category`),
    INDEX `idx_certainty` (`certainty_level`),
    INDEX `idx_status` (`status`),
    INDEX `idx_parent` (`parent_idea_id`),
    INDEX `idx_created` (`created_at`),
    INDEX `idx_title` (`title`),
    
    -- Full-text search indexes for content searching
    FULLTEXT INDEX `ft_search` (`title`, `content`, `tags`, `comments`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some initial categories (optional)
-- These can be managed through the web interface
