# Multi-Map System Implementation

## Overview
The multi-map system allows the application to support multiple map layers (Surface, Aerial, Underground) where interest points can be assigned to specific maps. Users can switch between different map views to explore different layers of the world.

## Database Structure

### Maps Table
```sql
CREATE TABLE maps (
    id_map INT AUTO_INCREMENT PRIMARY KEY,
    name_map VARCHAR(100) NOT NULL,
    slug_map VARCHAR(100) NOT NULL UNIQUE,
    description_map TEXT,
    image_path_map VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
```

### Default Maps
1. **Surface World** (ID: 1) - Main surface world map
2. **Aerial View** (ID: 2) - Skyborne locations and aerial perspective  
3. **Underground** (ID: 3) - Subterranean caves, tunnels, and cities

### Interest Points Integration
- Added `map_IP` column to `interest_points` table
- Foreign key relationship: `interest_points.map_IP` → `maps.id_map`
- Default assignment: Surface World (ID: 1) for existing points

## Implementation Details

### 1. Backend API (`maps_manager.php`)
New API endpoint for map management:
- `get_all_maps` - Retrieve all active maps
- `get_map_by_id` - Get specific map details
- `get_maps_with_point_counts` - Maps with point distribution

### 2. Map Interface Updates

#### Map_modif.php (Admin Interface)
- **Map Selector**: Dropdown to choose current editing map
- **Map Information**: Display current map description and point count
- **Map Switching**: Load points specific to selected map
- **Unsaved Changes Warning**: Prevents data loss when switching maps
- **All CRUD Operations**: Now map-aware (create, read, update, delete)

#### Map_view.php (User Interface)  
- **Map Layer Selection**: User-friendly map chooser
- **Dynamic Loading**: Points load based on selected map
- **Seamless Switching**: Smooth transitions between map layers
- **Visual Feedback**: Map descriptions and point counts

### 3. Places Manager Integration
- **Map Information Display**: Shows which map each point belongs to
- **Cross-Map Point Loading**: Loads points from all maps simultaneously
- **Map Context**: Folder details include linked point's map location

## Key Features

### 🔄 Dynamic Map Switching
- Real-time map layer switching without page reload
- Points automatically filter by selected map
- Map images update dynamically
- Preserves user context and editing state

### 🛡️ Data Integrity
- Unsaved changes warning when switching maps
- All existing functionality preserved per map
- Proper foreign key relationships
- Orphaned point handling (points without assigned map)

### 🎨 User Experience  
- Visual map information and point counts
- Intuitive map selection interface
- Consistent behavior across admin and user interfaces
- Responsive design maintained

### 🔧 Admin Features
- Map-specific point management
- Individual map point statistics
- Seamless editing workflow
- Folder management shows map context

## File Changes

### New Files
- `pages/scriptes/maps_manager.php` - Map management API
- `images/maps/map_aerial.png` - Aerial map placeholder
- `images/maps/map_underground.png` - Underground map placeholder

### Modified Files
- `pages/Map_modif.php` - Added map selection and multi-map support
- `pages/Map_view.php` - Added map layer switching for users  
- `pages/places_manager.php` - Updated to show map information
- `pages/scriptes/map_save_points.php` - Already supported map_id parameter

## Database Migration
Existing installations automatically migrate:
1. Creates `maps` table with default entries
2. Adds `map_IP` column to `interest_points` if missing
3. Assigns existing points to Surface World (ID: 1)
4. Establishes foreign key relationships

## Usage Instructions

### For Administrators
1. Navigate to Map_modif.php
2. Use the "Map Layer Selection" dropdown to choose desired map
3. Add, edit, or manage points specific to that map
4. Switch between maps to manage different layers
5. All points are automatically associated with the current map

### For Users
1. Visit Map_view.php  
2. Use "Choose Your View" dropdown to select map layer
3. Explore points specific to that map layer
4. Switch between Surface, Aerial, and Underground views

### For Places Management
1. Open places_manager.php
2. View which map each point/folder belongs to
3. Map information appears in folder details and point listings

## Technical Notes

- **Backward Compatibility**: Existing points default to Surface World
- **Performance**: Efficient loading with map-specific queries
- **Security**: All map operations use existing security patterns
- **Extensibility**: Easy to add new map layers via database

## Future Enhancements
- Map-specific point types
- Cross-map point relationships
- Custom map image upload
- Map layer visibility toggles
- Advanced map management interface
