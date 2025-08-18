# Troubleshooting: Automatic Map Layer Creation Issue

## Problem Description
When viewing place details (either from map_modif.php "view details" or places_manager.php "place details"), new map layers were being automatically created in the main maps table. This caused unwanted map layers to appear in the map selector, such as "Fostaquium" (a place name).

## Root Cause
The issue was in `pages/scriptes/place_map_manager.php` in the `getMapData()` function. This function was automatically creating new maps in the main maps table whenever a place detail page was viewed and no place-specific map existed.

### Problem Flow:
1. User clicks "view details" or "place details"
2. Browser navigates to `place_detail.php?id=X`
3. Page loads and calls `loadPlaceMap()` JavaScript function
4. This triggers a call to `place_map_manager.php` with action `get_map_data`
5. If no map exists for the place, `getMapData()` automatically created one
6. New map appeared in main map selector system

## Solution Applied
1. **Modified `getMapData()` function**: Instead of removing automatic map creation, modified it to create place-specific maps with `is_active = 0`. This ensures:
   - Place-specific maps are still created automatically (needed for place manager linking)
   - These maps don't appear in the main world map selector (which filters by `is_active = 1`)

2. **Updated `createMapForPlace()` function**: When maps ARE intentionally created for places, they now have `is_active = 0` to exclude them from the main map system.

3. **Updated `saveMapPoints()` function**: Similar fix to ensure place-specific maps are marked as inactive.

## Code Changes Made

### File: `pages/scriptes/place_map_manager.php`

#### getMapData() function:
- **Before**: Automatically created maps with default `is_active` value (appearing in main map selector)
- **After**: Automatically creates place-specific maps with `is_active = 0` (excluded from main map selector)

#### createMapForPlace() function:
- **Before**: Created maps with default `is_active` value
- **After**: Creates maps with `is_active = 0`

#### saveMapPoints() function:
- **Before**: Created maps with default `is_active` value  
- **After**: Creates maps with `is_active = 0`

## Prevention
- Place-specific maps are automatically created when viewing place details (maintains functionality)
- These maps are marked with `is_active = 0` to exclude them from main world map system
- Main map system filters by `WHERE is_active = 1` to show only intended world maps
- Places in place manager will be properly linked to their auto-created maps

## Testing
After applying the fix:
1. Visit any place detail page - should load without creating new maps
2. Check map selector in map_modif.php - should only show intended world maps
3. Verify existing place functionality still works

## Database Cleanup
If unwanted maps were already created, they can be identified by:
```sql
SELECT * FROM maps WHERE place_id IS NOT NULL AND is_active = 1;
```

These can be deactivated with:
```sql
UPDATE maps SET is_active = 0 WHERE place_id IS NOT NULL;
```

## Date Fixed
2025-08-18

## Files Modified
- `pages/scriptes/place_map_manager.php`
