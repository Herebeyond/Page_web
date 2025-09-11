-- Instructions for setting up the place maps feature:

1. Create the maps table in your database by running the SQL in create_maps_table.sql:
   ```sql
   CREATE TABLE IF NOT EXISTS maps (
       id_map INT AUTO_INCREMENT PRIMARY KEY,
       name_map VARCHAR(255) NOT NULL,
       image_map VARCHAR(255) NOT NULL DEFAULT 'default-map.jpg',
       place_id INT,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       INDEX idx_place_id (place_id)
   );
   ```

2. Create the following folder structure:
   - /images/maps/ (for default map images)
   - /images/places/{place-slug}/map/ (for place-specific maps)

3. Add a default map image:
   - Place a default map image at: /images/maps/default-map.jpg
   - This will be used when no specific map is available for a place

4. To add custom maps for specific places:
   - Create a "map" folder inside each place's folder: /images/places/{place-slug}/map/
   - Add map images with names like: main.jpg, overview.png, etc.
   - The system will first try to load from the place-specific folder, then fall back to the default

5. The system automatically:
   - Creates a map record for each place when first accessed
   - Links points to specific maps using the map_IP column
   - Allows admins to add/edit points on place-specific maps
   - Keeps points separate for each place's map

Features:
- Each place gets its own interactive map
- Points are linked to specific maps (not global)
- Admin users can add/edit/delete points on each map
- Points are color-coded by type (same as the main map)
- Tooltips show point information on hover
- Save/clear functionality for unsaved changes
