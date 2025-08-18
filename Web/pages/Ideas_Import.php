<?php
require_once "./blueprints/page_init.php";
require_once "./blueprints/gl_ap_start.php";
?>

<style>
.import-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.import-section {
    background: white;
    border-radius: 10px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.import-title {
    color: #222088;
    font-size: 24px;
    margin-bottom: 20px;
    border-bottom: 2px solid #222088;
    padding-bottom: 10px;
}

.import-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: bold;
    color: #333;
    margin-bottom: 8px;
}

.form-group textarea {
    min-height: 200px;
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-family: monospace;
    font-size: 14px;
    line-height: 1.5;
    resize: vertical;
}

.form-group select,
.form-group input {
    padding: 10px 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
}

.btn-import {
    background: linear-gradient(135deg, #222088 0%, #4a47a3 100%);
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-import:hover {
    background: linear-gradient(135deg, #1a1766 0%, #3d3a89 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(34, 32, 136, 0.3);
}

.help-box {
    background: #f8f9ff;
    border-left: 4px solid #222088;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 0 8px 8px 0;
}

.help-title {
    font-weight: bold;
    color: #222088;
    margin-bottom: 10px;
}

.example-format {
    background: #f5f5f5;
    padding: 15px;
    border-radius: 5px;
    font-family: monospace;
    font-size: 13px;
    white-space: pre-line;
    margin-top: 10px;
    border: 1px solid #ddd;
}

.results-section {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9ff;
    border-radius: 8px;
    border: 1px solid #222088;
}

.success {
    color: #28a745;
    font-weight: bold;
}

.error {
    color: #dc3545;
    font-weight: bold;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="content-page">
    <div class="import-container">
        <h1 style="text-align: center; color: #222088; margin-bottom: 30px;">📥 Ideas Import Tool</h1>
        
        <div class="import-section">
            <h2 class="import-title">Quick Add Single Idea</h2>
            
            <form class="import-form" id="quickAddForm">
                <div class="form-group">
                    <label for="quickTitle">Title:</label>
                    <input type="text" id="quickTitle" name="quickTitle" required>
                </div>
                
                <div class="form-group">
                    <label for="quickContent">Content:</label>
                    <textarea id="quickContent" name="quickContent" style="min-height: 100px;" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="quickCategory">Category:</label>
                        <select id="quickCategory" name="quickCategory">
                            <option value="Other">Other</option>
                            <option value="Magic_Systems">Magic Systems</option>
                            <option value="Creatures">Creatures</option>
                            <option value="Gods_Demons">Gods & Demons</option>
                            <option value="Dimensions_Realms">Dimensions & Realms</option>
                            <option value="Physics_Reality">Physics & Reality</option>
                            <option value="Races_Beings">Races & Beings</option>
                            <option value="Items_Artifacts">Items & Artifacts</option>
                            <option value="Lore_History">Lore & History</option>
                            <option value="Geography">Geography</option>
                            <option value="Politics">Politics</option>
                            <option value="Technology">Technology</option>
                            <option value="Culture">Culture</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="quickCertainty">Certainty:</label>
                        <select id="quickCertainty" name="quickCertainty">
                            <option value="Idea">Idea</option>
                            <option value="Not_Sure">Not Sure</option>
                            <option value="Developing">Developing</option>
                            <option value="Established">Established</option>
                            <option value="Canon">Canon</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="quickTags">Tags (comma separated):</label>
                        <input type="text" id="quickTags" name="quickTags" placeholder="tag1, tag2, tag3">
                    </div>
                </div>
                
                <button type="submit" class="btn-import">✨ Add Idea</button>
            </form>
            
            <div id="quickAddResults" class="results-section" style="display: none;">
                <h3>Add Result:</h3>
                <div id="quickResultsContent"></div>
            </div>
        </div>
        
        <div class="import-section">
            <h2 class="import-title">Bulk Import Ideas</h2>
            
            <div class="help-box">
                <div class="help-title">Import Format Instructions:</div>
                <p>Enter your ideas in the text format below. Each idea should be separated by "---" on its own line.</p>
                <p>You can use this format to import from Word, text files, or manually enter multiple ideas at once.</p>
                
                <div class="example-format">Example format:

Title: Magic Origin - Sleeping Demon
Content: La magie viendrait du démon endormi qui, en rêvant, projette volontairement ou non ses rêves dans le monde réel ce qui produit la production de mana et l'apparition d'évènements et de créatures paranormales.
Tags: magic, demons, mana, dreams, planes

---

Title: Dragon Evolution
Content: When some dragons started to live in forests, as time passed their wings became useless and began to disappear, making them slowly the firsts drakes.
Tags: dragons, drakes, evolution, forest

---

Title: Another Idea
Content: Your idea content here...
Tags: tag1, tag2, tag3</div>
            </div>
            
            <form class="import-form" id="importForm">
                <div class="form-group">
                    <label for="ideasText">Ideas Text (use format above):</label>
                    <textarea id="ideasText" name="ideasText" placeholder="Paste your ideas here using the format shown above..." required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="defaultCategory">Default Category:</label>
                        <select id="defaultCategory" name="defaultCategory">
                            <option value="Other">Other</option>
                            <option value="Magic_Systems">Magic Systems</option>
                            <option value="Creatures">Creatures</option>
                            <option value="Gods_Demons">Gods & Demons</option>
                            <option value="Dimensions_Realms">Dimensions & Realms</option>
                            <option value="Physics_Reality">Physics & Reality</option>
                            <option value="Races_Beings">Races & Beings</option>
                            <option value="Items_Artifacts">Items & Artifacts</option>
                            <option value="Lore_History">Lore & History</option>
                            <option value="Geography">Geography</option>
                            <option value="Politics">Politics</option>
                            <option value="Technology">Technology</option>
                            <option value="Culture">Culture</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="defaultCertainty">Default Certainty Level:</label>
                        <select id="defaultCertainty" name="defaultCertainty">
                            <option value="Idea">Idea</option>
                            <option value="Not_Sure">Not Sure</option>
                            <option value="Developing">Developing</option>
                            <option value="Established">Established</option>
                            <option value="Canon">Canon</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="defaultLanguage">Default Language:</label>
                        <select id="defaultLanguage" name="defaultLanguage">
                            <option value="French">French</option>
                            <option value="English">English</option>
                            <option value="Mixed">Mixed</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn-import">🚀 Import Ideas</button>
            </form>
            
            <div id="importResults" class="results-section" style="display: none;">
                <h3>Import Results:</h3>
                <div id="resultsContent"></div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="Ideas.php" style="color: #222088; text-decoration: none; font-weight: bold; font-size: 18px;">
                ← Back to Ideas Management
            </a>
        </div>
    </div>
</div>

<script>
// Bulk import form handler
document.getElementById('importForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('action', 'bulk_import');
    
    try {
        const response = await fetch('scriptes/ideas_manager.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        const resultsDiv = document.getElementById('importResults');
        const contentDiv = document.getElementById('resultsContent');
        
        if (data.success) {
            contentDiv.innerHTML = `
                <div class="success">✅ Successfully imported ${data.imported_count} ideas!</div>
                ${data.details ? `<div style="margin-top: 10px;">${data.details}</div>` : ''}
                ${data.errors && data.errors.length > 0 ? 
                    `<div class="error" style="margin-top: 10px;">Errors:<br>${data.errors.join('<br>')}</div>` : ''
                }
            `;
        } else {
            contentDiv.innerHTML = `<div class="error">❌ Import failed: ${data.message}</div>`;
        }
        
        resultsDiv.style.display = 'block';
        
    } catch (error) {
        console.error('Import error:', error);
        document.getElementById('importResults').style.display = 'block';
        document.getElementById('resultsContent').innerHTML = 
            `<div class="error">❌ Import failed: ${error.message}</div>`;
    }
});

// Quick add form handler
document.getElementById('quickAddForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'create_idea');
    formData.append('title', document.getElementById('quickTitle').value);
    formData.append('content', document.getElementById('quickContent').value);
    formData.append('category', document.getElementById('quickCategory').value);
    formData.append('certainty_level', document.getElementById('quickCertainty').value);
    formData.append('language', 'French'); // Default language
    
    const tagsInput = document.getElementById('quickTags').value;
    if (tagsInput) {
        const tags = tagsInput.split(',').map(tag => tag.trim()).filter(tag => tag);
        formData.append('tags', JSON.stringify(tags));
    }
    
    try {
        const response = await fetch('scriptes/ideas_manager.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        const resultsDiv = document.getElementById('quickAddResults');
        const contentDiv = document.getElementById('quickResultsContent');
        
        if (data.success) {
            contentDiv.innerHTML = `<div class="success">✅ Idea added successfully!</div>`;
            document.getElementById('quickAddForm').reset();
        } else {
            contentDiv.innerHTML = `<div class="error">❌ Failed to add idea: ${data.message}</div>`;
        }
        
        resultsDiv.style.display = 'block';
        
    } catch (error) {
        console.error('Add error:', error);
        document.getElementById('quickAddResults').style.display = 'block';
        document.getElementById('quickResultsContent').innerHTML = 
            `<div class="error">❌ Failed to add idea: ${error.message}</div>`;
    }
});
</script>

<?php
require_once "./blueprints/gl_ap_end.php";
?>
