# Entity Auto-Linking System

## Overview
The Entity Auto-Linking system automatically converts entity names mentioned in idea content into clickable links that navigate to the appropriate pages.

## Supported Entity Types

### Characters
- **Source Table**: `characters`
- **Name Column**: `character_name`
- **Link Target**: `Character_display.php?character_id={id}`
- **CSS Class**: `entity-character` (green styling)

### Species
- **Source Table**: `species`
- **Name Column**: `specie_name`
- **Link Target**: `Races_display.php?specie={name}`
- **CSS Class**: `entity-species` (blue styling)

### Races
- **Source Table**: `races`
- **Name Column**: `race_name`
- **Link Target**: `Races_display.php?specie={specie_name}&race={race_name}`
- **CSS Class**: `entity-race` (purple styling)

### Places (Optional)
- **Source Table**: `interest_points`
- **Name Column**: `name_IP`
- **Link Target**: `Map_view.php?place={name}`
- **CSS Class**: `entity-place` (red styling)

## How It Works

### Automatic Processing
1. **During Idea Creation**: When a new idea is created, the content is automatically processed for entity links
2. **During Idea Editing**: When an idea is updated, the content is reprocessed for entity links

### Manual Processing
- **Batch Processing Button**: The "🔗 Process Entity Links" button in the Ideas page allows processing all existing ideas at once
- **Use Case**: Run this after adding new characters, species, races, or places to link them in existing ideas

## Technical Implementation

### Key Functions

#### `getAllEntityNames()`
Retrieves all entity names from different database tables and creates an array with:
- Entity name
- Entity type (character, species, race, place)
- Entity ID
- Target link URL

#### `processEntityLinks($content)`
Processes text content to convert entity names to HTML links:
- Sorts entities by name length (longest first) to avoid partial matches
- Uses regex for whole-word matching (case insensitive)
- Prevents double-linking of the same entity
- Avoids linking inside existing HTML tags

#### `processIdeaEntityLinks($ideaId)`
Processes a single idea's content for entity links and updates the database.

#### `processAllIdeasEntityLinks()`
Batch processes all ideas in the database for entity links.

### Link Format
```html
<a href="target_page.php" 
   class="entity-link entity-{type}" 
   data-entity-type="{type}" 
   data-entity-id="{id}" 
   title="{Type}: {Name}">
   {Entity Name}
</a>
```

### CSS Styling
Entity links are styled with:
- Type-specific colors and background highlights
- Hover effects with underlines and shadow
- Smooth transitions
- Tooltip showing entity type and name

## Performance Considerations

### Efficient Processing
- **On-Demand Processing**: Links are only processed when ideas are created/modified
- **Batch Processing**: Available for bulk updates but should be used sparingly
- **Duplicate Prevention**: Entities already linked are skipped to avoid reprocessing

### Regex Optimization
- Uses word boundaries (`\b`) for accurate matching
- Prevents partial word matches
- Case-insensitive matching for user-friendly behavior

## Editor Integration

### Content Editing
When editing an idea:
- The editor shows the original text without HTML links
- Entity links are stripped using regex: `/<a[^>]*class="entity-link[^"]*"[^>]*>(.*?)<\/a>/g`
- After saving, content is reprocessed for updated entity links

### Display Mode
When viewing ideas:
- Entity links are rendered as clickable HTML elements
- Links use the appropriate page structure for each entity type
- Visual styling helps distinguish different entity types

## API Endpoints

### Process Single Idea
```
POST scriptes/entity_linking.php
action=process_idea_links
idea_id={id}
```

### Process All Ideas
```
POST scriptes/entity_linking.php
action=process_all_ideas_links
```

### Get All Entities
```
GET scriptes/entity_linking.php
action=get_entities
```

## Usage Examples

### Text Input
```
Zaldrim was awake for 9000 years and ruled over the Elves in the Northern Kingdom.
```

### Processed Output
```
<a href="./Character_display.php?character_id=1" class="entity-link entity-character">Zaldrim</a> was awake for 9000 years and ruled over the <a href="./Races_display.php?specie=Fae_Folk&race=Elves" class="entity-link entity-race">Elves</a> in the <a href="./Map_view.php?place=Northern Kingdom" class="entity-link entity-place">Northern Kingdom</a>.
```

## Future Enhancements
- Tooltip hover previews (planned)
- Entity disambiguation for similar names
- Custom entity types (gods, items, etc.)
- Link validation and broken link detection
