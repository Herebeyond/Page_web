-- Map Tables Creation Script
-- This script creates the correct table structure for the map system

-- First, let's create the IP_types table for point types
CREATE TABLE IF NOT EXISTS `IP_types` (
    `id_IPT` INT AUTO_INCREMENT PRIMARY KEY,
    `name_IPT` VARCHAR(100) NOT NULL UNIQUE,
    `color_IPT` VARCHAR(7) DEFAULT '#ff0000', -- Hex color code
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert some default point types
INSERT IGNORE INTO `IP_types` (`name_IPT`, `color_IPT`) VALUES
('City', '#ff0000'),
('Village', '#00ff00'),
('Location', '#800080'),
('Tower', '#0000ff'),
('Ruins', '#808080'),
('Forest', '#228b22'),
('Mountain', '#8b4513'),
('Cave', '#000000'),
('Temple', '#ffd700'),
('Fort', '#cd853f'),
('Other', '#ff69b4');

-- Check if the old interest_points table exists and has the wrong structure
-- If so, we need to rename it to preserve data and create the new structure

-- Create the new interest_points table with correct structure
CREATE TABLE IF NOT EXISTS `interest_points_new` (
    `id_IP` INT AUTO_INCREMENT PRIMARY KEY,
    `name_IP` VARCHAR(255) NOT NULL,
    `description_IP` TEXT,
    `map_IP` INT DEFAULT 1,
    `type_IP` INT,
    `coordinates_IP` JSON NOT NULL, -- Stores {x: value, y: value}
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key to IP_types
    FOREIGN KEY (`type_IP`) REFERENCES `IP_types`(`id_IPT`) ON DELETE SET NULL,
    
    -- Index for better performance
    INDEX `idx_map_ip` (`map_IP`),
    INDEX `idx_type_ip` (`type_IP`)
);

-- Migration note: Data from old interest_points table will need to be migrated manually
-- if it exists and contains important data
