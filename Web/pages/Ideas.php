<?php
require_once "./blueprints/page_init.php";
require_once "./blueprints/gl_ap_start.php";
?>

<style>
/* Ideas Management Specific Styles */
.ideas-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.ideas-header {
    text-align: center;
    margin-bottom: 30px;
    border-bottom: 3px solid #222088;
    padding-bottom: 20px;
}

.ideas-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 30px;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9ff 0%, #e8ecff 100%);
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(34, 32, 136, 0.1);
}

.control-group {
    display: flex;
    flex-direction: column;
    min-width: 150px;
}

.control-group label {
    font-weight: bold;
    color: #222088;
    margin-bottom: 5px;
}

.control-group select,
.control-group input {
    padding: 8px 12px;
    border: 2px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.control-group select:focus,
.control-group input:focus {
    border-color: #222088;
    outline: none;
}

.btn-primary {
    background: linear-gradient(135deg, #222088 0%, #4a47a3 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #1a1766 0%, #3d3a89 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(34, 32, 136, 0.3);
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
}

.ideas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.idea-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
    border-left: 5px solid #222088;
}

.idea-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(34, 32, 136, 0.2);
}

.idea-title {
    font-size: 18px;
    font-weight: bold;
    color: #222088;
    margin-bottom: 10px;
    line-height: 1.3;
}

.idea-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 15px;
}

.idea-badge {
    padding: 4px 8px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.badge-category {
    background: #e3f2fd;
    color: #1976d2;
}

.badge-certainty-idea { background: #fff3e0; color: #f57c00; }
.badge-certainty-not_sure { background: #ffebee; color: #d32f2f; }
.badge-certainty-developing { background: #e8f5e8; color: #388e3c; }
.badge-certainty-established { background: #e3f2fd; color: #1976d2; }
.badge-certainty-canon { background: #f3e5f5; color: #7b1fa2; }

.badge-priority-low { background: #f1f8e9; color: #689f38; }
.badge-priority-medium { background: #fff8e1; color: #ffa000; }
.badge-priority-high { background: #ffebee; color: #d32f2f; }
.badge-priority-critical { background: #4a148c; color: white; }

.idea-content {
    color: #555;
    line-height: 1.6;
    margin-bottom: 15px;
    max-height: 150px;
    overflow-y: auto;
    border-left: 3px solid #e0e0e0;
    padding-left: 15px;
}

.idea-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 15px;
}

.tag {
    background: #f5f5f5;
    color: #666;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 11px;
}

.idea-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.idea-hierarchy {
    margin-bottom: 10px;
    font-size: 12px;
    color: #666;
}

.parent-link {
    color: #222088;
    text-decoration: none;
    font-weight: bold;
}

.parent-link:hover {
    text-decoration: underline;
}

.child-count {
    background: #222088;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 10px;
    margin-left: 5px;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
}

.modal-content {
    background-color: white;
    margin: 2% auto;
    padding: 30px;
    border-radius: 15px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 2px solid #222088;
    padding-bottom: 15px;
}

.modal-title {
    color: #222088;
    font-size: 24px;
    font-weight: bold;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s;
}

.close:hover {
    color: #222088;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 2px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #222088;
    outline: none;
}

.form-group textarea {
    min-height: 120px;
    resize: vertical;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .ideas-grid {
        grid-template-columns: 1fr;
    }
    
    .ideas-controls {
        flex-direction: column;
    }
}

.stats-section {
    background: linear-gradient(135deg, #222088 0%, #4a47a3 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    text-align: center;
}

.stat-item h3 {
    font-size: 24px;
    margin-bottom: 5px;
}

.stat-item p {
    font-size: 14px;
    opacity: 0.9;
}

/* Import and Quick Add Modal Specific Styles */
.results-section {
    padding: 15px;
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
</style>

<div class="content-page">
    <div class="ideas-container">
        <div class="ideas-header">
            <h1>🌟 Universe Ideas Management</h1>
            <p>Centralized hub for all your fantasy universe concepts, lore, and creative ideas</p>
        </div>

        <!-- Statistics Section -->
        <div class="stats-section" id="statsSection">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3 id="totalIdeas">0</h3>
                    <p>Total Ideas</p>
                </div>
                <div class="stat-item">
                    <h3 id="canonIdeas">0</h3>
                    <p>Canon Ideas</p>
                </div>
                <div class="stat-item">
                    <h3 id="developingIdeas">0</h3>
                    <p>In Development</p>
                </div>
                <div class="stat-item">
                    <h3 id="categoriesCount">0</h3>
                    <p>Categories</p>
                </div>
            </div>
        </div>

        <!-- Controls Section -->
        <div class="ideas-controls">
            <div class="control-group">
                <label for="searchInput">Search Ideas</label>
                <input type="text" id="searchInput" placeholder="Search titles, content, tags...">
            </div>
            
            <div class="control-group">
                <label for="categoryFilter">Category</label>
                <select id="categoryFilter">
                    <option value="">All Categories</option>
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
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="control-group">
                <label for="certaintyFilter">Certainty Level</label>
                <select id="certaintyFilter">
                    <option value="">All Levels</option>
                    <option value="Idea">Idea</option>
                    <option value="Not_Sure">Not Sure</option>
                    <option value="Developing">Developing</option>
                    <option value="Established">Established</option>
                    <option value="Canon">Canon</option>
                </select>
            </div>
            
            <div class="control-group">
                <label for="priorityFilter">Priority</label>
                <select id="priorityFilter">
                    <option value="">All Priorities</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Critical">Critical</option>
                </select>
            </div>
            
            <div class="control-group">
                <label for="statusFilter">Status</label>
                <select id="statusFilter">
                    <option value="">All Status</option>
                    <option value="Draft">Draft</option>
                    <option value="In_Progress">In Progress</option>
                    <option value="Review">Review</option>
                    <option value="Finalized">Finalized</option>
                    <option value="Archived">Archived</option>
                </select>
            </div>
            
            <div class="control-group">
                <label>&nbsp;</label>
                <button class="btn-primary" onclick="openIdeaModal()">➕ Add New Idea</button>
            </div>
            
            <div class="control-group">
                <label>&nbsp;</label>
                <button class="btn-primary" onclick="openQuickAddModal()">✨ Quick Add</button>
            </div>
            
            <div class="control-group">
                <label>&nbsp;</label>
                <button class="btn-primary" onclick="openBulkImportModal()">📥 Bulk Import</button>
            </div>
            
            <div class="control-group">
                <label>&nbsp;</label>
                <button class="btn-secondary" onclick="exportIdeas()">� Export Ideas</button>
            </div>
        </div>

        <!-- Ideas Grid -->
        <div class="ideas-grid" id="ideasGrid">
            <!-- Ideas will be loaded here via JavaScript -->
        </div>

        <!-- Load More Button -->
        <div style="text-align: center; margin-top: 30px;">
            <button class="btn-secondary" id="loadMoreBtn" onclick="loadMoreIdeas()" style="display: none;">
                Load More Ideas
            </button>
        </div>
    </div>
</div>

<!-- Idea Modal -->
<div id="ideaModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Add New Idea</h2>
            <span class="close" onclick="closeIdeaModal()">&times;</span>
        </div>
        
        <form id="ideaForm">
            <input type="hidden" id="ideaId" name="ideaId">
            
            <div class="form-group">
                <label for="ideaTitle">Title *</label>
                <input type="text" id="ideaTitle" name="title" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ideaCategory">Category *</label>
                    <select id="ideaCategory" name="category" required>
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
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="ideaCertainty">Certainty Level *</label>
                    <select id="ideaCertainty" name="certainty_level" required>
                        <option value="Idea">Idea</option>
                        <option value="Not_Sure">Not Sure</option>
                        <option value="Developing">Developing</option>
                        <option value="Established">Established</option>
                        <option value="Canon">Canon</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ideaPriority">Priority</label>
                    <select id="ideaPriority" name="priority">
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="ideaStatus">Status</label>
                    <select id="ideaStatus" name="status">
                        <option value="Draft">Draft</option>
                        <option value="In_Progress">In Progress</option>
                        <option value="Review">Review</option>
                        <option value="Finalized">Finalized</option>
                        <option value="Archived">Archived</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ideaLanguage">Language</label>
                    <select id="ideaLanguage" name="language">
                        <option value="French">French</option>
                        <option value="English">English</option>
                        <option value="Mixed">Mixed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="ideaWorldImpact">World Impact</label>
                    <select id="ideaWorldImpact" name="world_impact">
                        <option value="Local">Local</option>
                        <option value="Regional">Regional</option>
                        <option value="Global">Global</option>
                        <option value="Universal">Universal</option>
                        <option value="Dimensional">Dimensional</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="ideaContent">Content *</label>
                <textarea id="ideaContent" name="content" required placeholder="Describe your idea in detail..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="ideaTags">Tags</label>
                <input type="text" id="ideaTags" name="tags" placeholder="Enter tags separated by commas: magic, demons, reality">
            </div>
            
            <div class="form-group">
                <label for="ideaParent">Parent Idea</label>
                <select id="ideaParent" name="parent_idea_id">
                    <option value="">None (Root Idea)</option>
                    <!-- Will be populated via JavaScript -->
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ideaEase">Ease of Modification</label>
                    <select id="ideaEase" name="ease_of_modification">
                        <option value="Easy">Easy</option>
                        <option value="Medium">Medium</option>
                        <option value="Hard">Hard</option>
                        <option value="Core_Element">Core Element</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="ideaSource">Inspiration Source</label>
                    <input type="text" id="ideaSource" name="inspiration_source" placeholder="Where did this idea come from?">
                </div>
            </div>
            
            <div class="form-group">
                <label for="ideaImplementation">Implementation Notes</label>
                <textarea id="ideaImplementation" name="implementation_notes" placeholder="How to implement or use this idea..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="ideaComments">Comments</label>
                <textarea id="ideaComments" name="comments" placeholder="Additional notes and comments..."></textarea>
            </div>
            
            <div style="text-align: right; margin-top: 30px;">
                <button type="button" class="btn-secondary" onclick="closeIdeaModal()">Cancel</button>
                <button type="submit" class="btn-primary" style="margin-left: 10px;">Save Idea</button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Add Modal -->
<div id="quickAddModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">✨ Quick Add Single Idea</h2>
            <span class="close" onclick="closeQuickAddModal()">&times;</span>
        </div>
        
        <form id="quickAddForm">
            <div class="form-group">
                <label for="quickTitle">Title *</label>
                <input type="text" id="quickTitle" name="quickTitle" required>
            </div>
            
            <div class="form-group">
                <label for="quickContent">Content *</label>
                <textarea id="quickContent" name="quickContent" style="min-height: 100px;" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="quickCategory">Category</label>
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
                    <label for="quickCertainty">Certainty</label>
                    <select id="quickCertainty" name="quickCertainty">
                        <option value="Idea">Idea</option>
                        <option value="Not_Sure">Not Sure</option>
                        <option value="Developing">Developing</option>
                        <option value="Established">Established</option>
                        <option value="Canon">Canon</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="quickTags">Tags (comma separated)</label>
                <input type="text" id="quickTags" name="quickTags" placeholder="tag1, tag2, tag3">
            </div>
            
            <div id="quickAddResults" class="results-section" style="display: none; margin-top: 15px;">
                <div id="quickResultsContent"></div>
            </div>
            
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" class="btn-secondary" onclick="closeQuickAddModal()">Cancel</button>
                <button type="submit" class="btn-primary" style="margin-left: 10px;">✨ Add Idea</button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Import Modal -->
<div id="bulkImportModal" class="modal">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h2 class="modal-title">📥 Bulk Import Ideas</h2>
            <span class="close" onclick="closeBulkImportModal()">&times;</span>
        </div>
        
        <div style="background: #f8f9ff; border-left: 4px solid #222088; padding: 15px; margin-bottom: 20px; border-radius: 0 8px 8px 0;">
            <div style="font-weight: bold; color: #222088; margin-bottom: 10px;">Import Format Instructions:</div>
            <p style="margin-bottom: 10px;">Enter your ideas in the text format below. Each idea should be separated by "---" on its own line.</p>
            <p style="margin-bottom: 10px;">You can use this format to import from Word, text files, or manually enter multiple ideas at once.</p>
            
            <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 13px; white-space: pre-line; margin-top: 10px; border: 1px solid #ddd;">Example format:

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
        
        <form id="bulkImportForm">
            <div class="form-group">
                <label for="ideasText">Ideas Text (use format above) *</label>
                <textarea id="ideasText" name="ideasText" placeholder="Paste your ideas here using the format shown above..." required style="min-height: 200px; font-family: monospace; font-size: 14px; line-height: 1.5;"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="defaultCategory">Default Category</label>
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
                    <label for="defaultCertainty">Default Certainty Level</label>
                    <select id="defaultCertainty" name="defaultCertainty">
                        <option value="Idea">Idea</option>
                        <option value="Not_Sure">Not Sure</option>
                        <option value="Developing">Developing</option>
                        <option value="Established">Established</option>
                        <option value="Canon">Canon</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="defaultLanguage">Default Language</label>
                <select id="defaultLanguage" name="defaultLanguage">
                    <option value="French">French</option>
                    <option value="English">English</option>
                    <option value="Mixed">Mixed</option>
                </select>
            </div>
            
            <div id="importResults" class="results-section" style="display: none; margin-top: 15px;">
                <h3>Import Results:</h3>
                <div id="resultsContent"></div>
            </div>
            
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" class="btn-secondary" onclick="closeBulkImportModal()">Cancel</button>
                <button type="submit" class="btn-primary" style="margin-left: 10px;">🚀 Import Ideas</button>
            </div>
        </form>
    </div>
</div>

<script>
// Global variables
let currentPage = 1;
let totalPages = 1;
let currentFilters = {};
let allIdeas = [];

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    loadIdeas();
    loadParentOptions();
    setupEventListeners();
    setupNewModalListeners();
});

function setupEventListeners() {
    // Search and filter event listeners
    document.getElementById('searchInput').addEventListener('input', debounce(applyFilters, 300));
    document.getElementById('categoryFilter').addEventListener('change', applyFilters);
    document.getElementById('certaintyFilter').addEventListener('change', applyFilters);
    document.getElementById('priorityFilter').addEventListener('change', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    
    // Form submission
    document.getElementById('ideaForm').addEventListener('submit', handleFormSubmit);
    
    // Modal close on outside click
    window.addEventListener('click', function(event) {
        const ideaModal = document.getElementById('ideaModal');
        const quickAddModal = document.getElementById('quickAddModal');
        const bulkImportModal = document.getElementById('bulkImportModal');
        
        if (event.target === ideaModal) {
            closeIdeaModal();
        } else if (event.target === quickAddModal) {
            closeQuickAddModal();
        } else if (event.target === bulkImportModal) {
            closeBulkImportModal();
        }
    });
    
    // ESC key support for all modals
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeIdeaModal();
            closeQuickAddModal();
            closeBulkImportModal();
        }
    });
}

function setupNewModalListeners() {
    // Quick Add Form Event Listener
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
                
                // Refresh the ideas grid
                loadIdeas();
                
                // Auto-close modal after 2 seconds
                setTimeout(() => {
                    closeQuickAddModal();
                }, 2000);
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
    
    // Bulk Import Form Event Listener
    document.getElementById('bulkImportForm').addEventListener('submit', async function(e) {
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
                
                // Refresh the ideas grid
                loadIdeas();
                
                // Auto-close modal after 3 seconds if no errors
                if (!data.errors || data.errors.length === 0) {
                    setTimeout(() => {
                        closeBulkImportModal();
                    }, 3000);
                }
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
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

async function loadIdeas(page = 1, append = false) {
    try {
        const params = new URLSearchParams({
            page: page,
            ...currentFilters
        });
        
        const response = await fetch(`scriptes/ideas_manager.php?action=get_ideas&${params}`);
        const data = await response.json();
        
        if (data.success) {
            if (!append) {
                allIdeas = data.ideas;
                currentPage = 1;
            } else {
                allIdeas = [...allIdeas, ...data.ideas];
                currentPage = page;
            }
            
            totalPages = data.total_pages;
            displayIdeas(append);
            updateStats(data.stats);
            
            // Show/hide load more button
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            loadMoreBtn.style.display = currentPage < totalPages ? 'block' : 'none';
        } else {
            console.error('Error loading ideas:', data.message);
        }
    } catch (error) {
        console.error('Error loading ideas:', error);
    }
}

function displayIdeas(append = false) {
    const grid = document.getElementById('ideasGrid');
    
    if (!append) {
        grid.innerHTML = '';
    }
    
    allIdeas.slice(append ? -20 : 0).forEach(idea => {
        const ideaCard = createIdeaCard(idea);
        grid.appendChild(ideaCard);
    });
}

function createIdeaCard(idea) {
    const card = document.createElement('div');
    card.className = 'idea-card';
    card.dataset.ideaId = idea.id_idea;
    
    const tags = idea.tags ? JSON.parse(idea.tags) : [];
    const hasChildren = parseInt(idea.child_count) > 0;
    
    card.innerHTML = `
        ${idea.parent_title && idea.parent_title !== 'Root Idea' ? 
            `<div class="idea-hierarchy">
                <span>Sub-idea of: <a href="#" class="parent-link" onclick="filterByParent(${idea.parent_idea_id})">${idea.parent_title}</a></span>
            </div>` : ''
        }
        
        <div class="idea-title">
            ${idea.title}
            ${hasChildren ? `<span class="child-count">${idea.child_count} sub-ideas</span>` : ''}
        </div>
        
        <div class="idea-meta">
            <span class="idea-badge badge-category">${idea.category.replace('_', ' ')}</span>
            <span class="idea-badge badge-certainty-${idea.certainty_level.toLowerCase()}">${idea.certainty_level.replace('_', ' ')}</span>
            <span class="idea-badge badge-priority-${idea.priority.toLowerCase()}">${idea.priority}</span>
            <span class="idea-badge" style="background: #f0f0f0; color: #666;">${idea.language}</span>
        </div>
        
        <div class="idea-content">${idea.content}</div>
        
        ${tags.length > 0 ? 
            `<div class="idea-tags">
                ${tags.map(tag => `<span class="tag">${tag}</span>`).join('')}
            </div>` : ''
        }
        
        <div class="idea-actions">
            <button class="btn-secondary" onclick="editIdea(${idea.id_idea})">✏️ Edit</button>
            <button class="btn-secondary" onclick="duplicateIdea(${idea.id_idea})">📋 Duplicate</button>
            <button class="btn-secondary" onclick="createSubIdea(${idea.id_idea})">➕ Sub-idea</button>
            <button class="btn-secondary" onclick="deleteIdea(${idea.id_idea})" style="background: #dc3545;">🗑️ Delete</button>
        </div>
    `;
    
    return card;
}

function updateStats(stats) {
    document.getElementById('totalIdeas').textContent = stats.total || 0;
    document.getElementById('canonIdeas').textContent = stats.canon || 0;
    document.getElementById('developingIdeas').textContent = stats.developing || 0;
    document.getElementById('categoriesCount').textContent = stats.categories || 0;
}

function applyFilters() {
    currentFilters = {
        search: document.getElementById('searchInput').value,
        category: document.getElementById('categoryFilter').value,
        certainty: document.getElementById('certaintyFilter').value,
        priority: document.getElementById('priorityFilter').value,
        status: document.getElementById('statusFilter').value
    };
    
    // Remove empty filters
    Object.keys(currentFilters).forEach(key => {
        if (currentFilters[key] === '') {
            delete currentFilters[key];
        }
    });
    
    loadIdeas(1, false);
}

function loadMoreIdeas() {
    loadIdeas(currentPage + 1, true);
}

// Modal functions
function openIdeaModal(ideaId = null) {
    const modal = document.getElementById('ideaModal');
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('ideaForm');
    
    form.reset();
    
    if (ideaId) {
        modalTitle.textContent = 'Edit Idea';
        loadIdeaForEdit(ideaId);
    } else {
        modalTitle.textContent = 'Add New Idea';
        document.getElementById('ideaId').value = '';
    }
    
    modal.style.display = 'block';
}

function closeIdeaModal() {
    document.getElementById('ideaModal').style.display = 'none';
}

async function loadIdeaForEdit(ideaId) {
    try {
        const response = await fetch(`scriptes/ideas_manager.php?action=get_idea&id=${ideaId}`);
        const data = await response.json();
        
        if (data.success) {
            const idea = data.idea;
            
            document.getElementById('ideaId').value = idea.id_idea;
            document.getElementById('ideaTitle').value = idea.title;
            document.getElementById('ideaCategory').value = idea.category;
            document.getElementById('ideaCertainty').value = idea.certainty_level;
            document.getElementById('ideaPriority').value = idea.priority;
            document.getElementById('ideaStatus').value = idea.status;
            document.getElementById('ideaLanguage').value = idea.language;
            document.getElementById('ideaWorldImpact').value = idea.world_impact;
            document.getElementById('ideaContent').value = idea.content;
            document.getElementById('ideaParent').value = idea.parent_idea_id || '';
            document.getElementById('ideaEase').value = idea.ease_of_modification;
            document.getElementById('ideaSource').value = idea.inspiration_source || '';
            document.getElementById('ideaImplementation').value = idea.implementation_notes || '';
            document.getElementById('ideaComments').value = idea.comments || '';
            
            // Handle tags
            if (idea.tags) {
                const tags = JSON.parse(idea.tags);
                document.getElementById('ideaTags').value = tags.join(', ');
            }
        }
    } catch (error) {
        console.error('Error loading idea for edit:', error);
    }
}

async function handleFormSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const ideaId = formData.get('ideaId');
    
    // Convert tags to JSON
    const tagsInput = formData.get('tags');
    if (tagsInput) {
        const tags = tagsInput.split(',').map(tag => tag.trim()).filter(tag => tag);
        formData.set('tags', JSON.stringify(tags));
    }
    
    const action = ideaId ? 'update_idea' : 'create_idea';
    formData.append('action', action);
    
    try {
        const response = await fetch('scriptes/ideas_manager.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeIdeaModal();
            loadIdeas();
            alert(ideaId ? 'Idea updated successfully!' : 'Idea created successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error saving idea:', error);
        alert('Error saving idea. Please try again.');
    }
}

async function loadParentOptions() {
    try {
        const response = await fetch('scriptes/ideas_manager.php?action=get_parent_options');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('ideaParent');
            select.innerHTML = '<option value="">None (Root Idea)</option>';
            
            data.ideas.forEach(idea => {
                const option = document.createElement('option');
                option.value = idea.id_idea;
                option.textContent = idea.title;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading parent options:', error);
    }
}

// Action functions
function editIdea(ideaId) {
    openIdeaModal(ideaId);
}

function duplicateIdea(ideaId) {
    const idea = allIdeas.find(i => i.id_idea == ideaId);
    if (idea) {
        openIdeaModal();
        
        // Fill form with duplicated data
        setTimeout(() => {
            document.getElementById('ideaTitle').value = idea.title + ' (Copy)';
            document.getElementById('ideaCategory').value = idea.category;
            document.getElementById('ideaCertainty').value = idea.certainty_level;
            document.getElementById('ideaPriority').value = idea.priority;
            document.getElementById('ideaStatus').value = 'Draft';
            document.getElementById('ideaLanguage').value = idea.language;
            document.getElementById('ideaWorldImpact').value = idea.world_impact;
            document.getElementById('ideaContent').value = idea.content;
            document.getElementById('ideaEase').value = idea.ease_of_modification;
            document.getElementById('ideaSource').value = idea.inspiration_source || '';
            document.getElementById('ideaImplementation').value = idea.implementation_notes || '';
            document.getElementById('ideaComments').value = idea.comments || '';
            
            if (idea.tags) {
                const tags = JSON.parse(idea.tags);
                document.getElementById('ideaTags').value = tags.join(', ');
            }
        }, 100);
    }
}

function createSubIdea(parentId) {
    openIdeaModal();
    
    setTimeout(() => {
        document.getElementById('ideaParent').value = parentId;
    }, 100);
}

async function deleteIdea(ideaId) {
    if (!confirm('Are you sure you want to delete this idea? This action cannot be undone.')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete_idea');
        formData.append('id', ideaId);
        
        const response = await fetch('scriptes/ideas_manager.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadIdeas();
            alert('Idea deleted successfully!');
        } else {
            alert('Error deleting idea: ' + data.message);
        }
    } catch (error) {
        console.error('Error deleting idea:', error);
        alert('Error deleting idea. Please try again.');
    }
}

function filterByParent(parentId) {
    // Reset other filters
    document.getElementById('searchInput').value = '';
    document.getElementById('categoryFilter').value = '';
    document.getElementById('certaintyFilter').value = '';
    document.getElementById('priorityFilter').value = '';
    document.getElementById('statusFilter').value = '';
    
    currentFilters = { parent_id: parentId };
    loadIdeas(1, false);
}

function exportIdeas() {
    window.open('scriptes/ideas_manager.php?action=export_ideas', '_blank');
}

// Quick Add Modal Functions
function openQuickAddModal() {
    document.getElementById('quickAddModal').style.display = 'block';
    document.getElementById('quickAddForm').reset();
    document.getElementById('quickAddResults').style.display = 'none';
}

function closeQuickAddModal() {
    document.getElementById('quickAddModal').style.display = 'none';
}

// Bulk Import Modal Functions
function openBulkImportModal() {
    document.getElementById('bulkImportModal').style.display = 'block';
    document.getElementById('bulkImportForm').reset();
    document.getElementById('importResults').style.display = 'none';
}

function closeBulkImportModal() {
    document.getElementById('bulkImportModal').style.display = 'none';
}


</script>

<?php
require_once "./blueprints/gl_ap_end.php";
?>
