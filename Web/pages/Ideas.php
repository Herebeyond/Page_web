<?php
require_once "./blueprints/page_init.php";
require_once "./blueprints/gl_ap_start.php";
?>

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
                <label for="statusFilter">Status</label>
                <select id="statusFilter">
                    <option value="">All Status</option>
                    <option value="Draft">Draft</option>
                    <option value="Need_Correction">Need Correction</option>
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

            <div class="control-group">
                <label>&nbsp;</label>
                <button class="btn-warning" onclick="processAllEntityLinks()" id="processLinksBtn">🔗 Process Entity Links</button>
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
        <!-- Fixed Close Button -->
        <span class="close close-fixed" onclick="closeIdeaModal()" title="Close">&times;</span>
        
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Add New Idea</h2>
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
            
            <div class="form-group">
                <label for="ideaStatus">Status</label>
                <select id="ideaStatus" name="status">
                    <option value="Draft">Draft</option>
                    <option value="Need_Correction">Need Correction</option>
                    <option value="In_Progress">In Progress</option>
                    <option value="Review">Review</option>
                    <option value="Finalized">Finalized</option>
                    <option value="Archived">Archived</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="ideaContent">Content *</label>
                <textarea id="ideaContent" name="content" required placeholder="Describe your idea in detail..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="ideaTags">Tags</label>
                <input type="text" id="ideaTags" name="tags" placeholder="Enter tags separated by commas: magic, demons, reality">
                <div id="existingTagsContainer" class="existing-tags-container">
                    <label>Existing Tags:</label>
                    <div id="existingTagsList" class="existing-tags-list">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="ideaParent">Parent Idea</label>
                <select id="ideaParent" name="parent_idea_id">
                    <option value="">None (Root Idea)</option>
                    <!-- Will be populated via JavaScript -->
                </select>
            </div>
            
            <div class="form-group">
                <label for="ideaSource">Inspiration Source</label>
                <input type="text" id="ideaSource" name="inspiration_source" placeholder="Where did this idea come from?">
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
        <!-- Fixed Close Button -->
        <span class="close close-fixed" onclick="closeQuickAddModal()" title="Close">&times;</span>
        
        <div class="modal-header">
            <h2 class="modal-title">✨ Quick Add Single Idea</h2>
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
                <div id="quickExistingTagsContainer" class="existing-tags-container">
                    <label>Existing Tags:</label>
                    <div id="quickExistingTagsList" class="existing-tags-list">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
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
        <!-- Fixed Close Button -->
        <span class="close close-fixed" onclick="closeBulkImportModal()" title="Close">&times;</span>
        
        <div class="modal-header">
            <h2 class="modal-title">📥 Bulk Import Ideas</h2>
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
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    
    // Form submission
    document.getElementById('ideaForm').addEventListener('submit', handleFormSubmit);
    
    // Modal close on outside click - DISABLED to prevent accidental closing
    // Users can only close modals using the × button or ESC key
    
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
        
        // Get the submit button and add loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span style="display: inline-flex; align-items: center; gap: 8px;"><div style="width: 16px; height: 16px; border: 2px solid #fff; border-top: 2px solid transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div>Adding...</span>';
        
        const formData = new FormData();
        formData.append('action', 'create_idea');
        formData.append('title', document.getElementById('quickTitle').value);
        formData.append('content', document.getElementById('quickContent').value);
        formData.append('category', document.getElementById('quickCategory').value);
        formData.append('certainty_level', document.getElementById('quickCertainty').value);
        
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
        } finally {
            // Restore button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
    
    // Bulk Import Form Event Listener
    document.getElementById('bulkImportForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Get the submit button and add loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span style="display: inline-flex; align-items: center; gap: 8px;"><div style="width: 16px; height: 16px; border: 2px solid #fff; border-top: 2px solid transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div>Importing...</span>';
        
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
        } finally {
            // Restore button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
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
    
    // Group ideas by parent-child relationships
    const ideaGroups = organizeIdeasByHierarchy(allIdeas.slice(append ? -20 : 0));
    
    ideaGroups.forEach(group => {
        const groupElement = createIdeaGroup(group);
        grid.appendChild(groupElement);
    });
}

function organizeIdeasByHierarchy(ideas) {
    const groups = [];
    const processedIds = new Set();
    
    // Helper function to recursively find all children of a given idea
    function findChildrenRecursively(parentId) {
        const directChildren = ideas.filter(child => 
            child.parent_idea_id == parentId && 
            child.parent_title !== 'Root Idea'
        ).sort((a, b) => a.id_idea - b.id_idea);
        
        return directChildren.map(child => {
            processedIds.add(child.id_idea);
            return {
                ...child,
                children: findChildrenRecursively(child.id_idea)
            };
        });
    }
    
    // Sort ideas: parents first (by creation order), then orphaned children
    const sortedIdeas = [...ideas].sort((a, b) => {
        const aIsParent = !(a.parent_title && a.parent_title !== 'Root Idea');
        const bIsParent = !(b.parent_title && b.parent_title !== 'Root Idea');
        
        // Parents come first
        if (aIsParent && !bIsParent) return -1;
        if (!aIsParent && bIsParent) return 1;
        
        // Within same type, sort by ID (creation order)
        return a.id_idea - b.id_idea;
    });
    
    sortedIdeas.forEach(idea => {
        if (processedIds.has(idea.id_idea)) return;
        
        const isSubIdea = idea.parent_title && idea.parent_title !== 'Root Idea';
        
        if (!isSubIdea) {
            // This is a top-level parent idea - find all its nested children
            const nestedChildren = findChildrenRecursively(idea.id_idea);
            
            groups.push({
                parent: idea,
                children: nestedChildren
            });
            
            // Mark this idea as processed
            processedIds.add(idea.id_idea);
        } else if (!processedIds.has(idea.id_idea)) {
            // This is an orphaned sub-idea (parent not in current view)
            groups.push({
                parent: null,
                children: [idea]
            });
            processedIds.add(idea.id_idea);
        }
    });
    
    return groups;
}

function createIdeaGroup(group) {
    const groupDiv = document.createElement('div');
    groupDiv.className = 'idea-group';
    
    if (group.parent) {
        // Add parent class for proper styling
        groupDiv.classList.add('parent-idea');
        
        // Add wrapper div for visual grouping when there are children
        if (group.children.length > 0) {
            groupDiv.classList.add('has-children-wrapper');
        }
        
        // Create parent card
        const parentCard = createIdeaCard(group.parent, group.children.length > 0);
        groupDiv.appendChild(parentCard);
        
        // Always create and show sub-ideas container for children
        if (group.children.length > 0) {
            const subContainer = document.createElement('div');
            subContainer.className = 'children-container';
            subContainer.id = `sub-ideas-${group.parent.id_idea}`;
            
            group.children.forEach((child, index) => {
                const childElement = createNestedIdeaElement(child, index === group.children.length - 1);
                subContainer.appendChild(childElement);
            });
            
            groupDiv.appendChild(subContainer);
        }
    } else {
        // Orphaned sub-ideas (should not happen in tree view, but keep for safety)
        group.children.forEach(child => {
            const childCard = createIdeaCard(child, false, true);
            groupDiv.appendChild(childCard);
        });
    }
    
    return groupDiv;
}

// Helper function to create nested idea elements recursively
function createNestedIdeaElement(ideaWithChildren, isLastSibling = false) {
    const container = document.createElement('div');
    container.className = 'nested-idea-container';
    
    // Create the idea card itself
    const hasChildren = ideaWithChildren.children && ideaWithChildren.children.length > 0;
    const childCard = createIdeaCard(ideaWithChildren, hasChildren, true);
    
    // Add tree connector classes based on position
    if (isLastSibling) {
        childCard.classList.add('last-child');
    }
    
    container.appendChild(childCard);
    
    // If this idea has children, create a nested container for them
    if (hasChildren) {
        const nestedSubContainer = document.createElement('div');
        nestedSubContainer.className = 'nested-children-container';
        nestedSubContainer.id = `nested-sub-ideas-${ideaWithChildren.id_idea}`;
        
        ideaWithChildren.children.forEach((nestedChild, index) => {
            const nestedElement = createNestedIdeaElement(
                nestedChild, 
                index === ideaWithChildren.children.length - 1
            );
            nestedSubContainer.appendChild(nestedElement);
        });
        
        container.appendChild(nestedSubContainer);
    }
    
    return container;
}

function createIdeaCard(idea, hasChildren = false, isSubIdea = false) {
    const card = document.createElement('div');
    const actualIsSubIdea = isSubIdea || (idea.parent_title && idea.parent_title !== 'Root Idea');
    const actualHasChildren = hasChildren || parseInt(idea.child_count) > 0;
    
    // Apply different styling for parent vs child ideas
    if (actualIsSubIdea) {
        card.className = 'idea-card sub-idea-card';
    } else {
        card.className = `idea-card parent-idea-card${actualHasChildren ? ' has-children' : ''}`;
    }
    
    card.dataset.ideaId = idea.id_idea;
    
    const tags = idea.tags ? JSON.parse(idea.tags) : [];
    
    card.innerHTML = `
        <div class="idea-title">
            ${actualHasChildren && !actualIsSubIdea ? '📁 ' : (actualIsSubIdea ? '' : '📄 ')}${highlightSearchTerms(idea.title, getSearchTerms())}
        </div>
        
        <div class="idea-meta">
            <div class="meta-item">
                <span class="meta-label">Category</span>
                <span class="idea-badge badge-category">${idea.category.replace('_', ' ')}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Certainty</span>
                <span class="idea-badge badge-certainty-${idea.certainty_level.toLowerCase()}">${idea.certainty_level.replace('_', ' ')}</span>
            </div>
            ${idea.status ? 
                `<div class="meta-item">
                    <span class="meta-label">Status</span>
                    <span class="idea-badge badge-status">${idea.status}</span>
                </div>` : ''
            }
        </div>
        
        <div class="idea-content">${highlightSearchTerms(idea.content, getSearchTerms())}</div>
        
        ${idea.inspiration_source ? 
            `<div class="idea-inspiration">
                <strong>💡 Inspiration Source:</strong> ${highlightSearchTerms(idea.inspiration_source, getSearchTerms())}
            </div>` : ''
        }
        
        ${idea.comments ? 
            `<div class="idea-comments">
                <strong>💬 Comments:</strong> ${highlightSearchTerms(idea.comments, getSearchTerms())}
            </div>` : ''
        }
        
        ${tags.length > 0 ? 
            `<div class="idea-tags">
                ${tags.map(tag => `<span class="tag">${tag}</span>`).join('')}
            </div>` : ''
        }
        
        <div class="idea-dates">
            <span class="idea-date">📅 Created: ${new Date(idea.created_at).toLocaleDateString()}</span>
            ${idea.updated_at && idea.updated_at !== idea.created_at ? 
                `<span class="idea-date">✏️ Modified: ${new Date(idea.updated_at).toLocaleDateString()}</span>` : ''
            }
        </div>
        
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
        // For editing, we'll call editIdea function directly
        modal.style.display = 'none'; // Close first, editIdea will reopen it
        editIdea(ideaId);
        return;
    } else {
        modalTitle.textContent = 'Add New Idea';
        document.getElementById('ideaId').value = '';
        loadExistingTags(); // Load existing tags for new idea
        loadParentOptions(); // Load parent options
    }
    
    modal.style.display = 'block';
}

function closeIdeaModal() {
    document.getElementById('ideaModal').style.display = 'none';
}

async function handleFormSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const ideaId = formData.get('ideaId');
    
    // Get the submit button and add loading state
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span style="display: inline-flex; align-items: center; gap: 8px;"><div style="width: 16px; height: 16px; border: 2px solid #fff; border-top: 2px solid transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div>Saving...</span>';
    
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
    } finally {
        // Restore button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
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
    loadExistingTags(); // Load existing tags
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

// Load existing tags for display in forms
async function loadExistingTags() {
    try {
        const response = await fetch('scriptes/ideas_manager.php?action=get_all_tags');
        const data = await response.json();
        
        if (data.success) {
            displayExistingTags(data.tags);
        }
    } catch (error) {
        console.error('Error loading existing tags:', error);
    }
}

// Display existing tags in both modals
function displayExistingTags(tags) {
    const containers = [
        document.getElementById('existingTagsList'),
        document.getElementById('quickExistingTagsList')
    ];
    
    containers.forEach(container => {
        if (container) {
            container.innerHTML = '';
            tags.forEach(tag => {
                const tagElement = document.createElement('span');
                tagElement.className = 'existing-tag';
                tagElement.textContent = tag;
                tagElement.onclick = () => addTagToInput(tag, container.closest('.modal').querySelector('input[name*="tags"]'));
                container.appendChild(tagElement);
            });
        }
    });
}

// Add tag to input field
function addTagToInput(tag, inputElement) {
    if (!inputElement) return;
    
    const currentTags = inputElement.value.split(',').map(t => t.trim()).filter(t => t);
    if (!currentTags.includes(tag)) {
        currentTags.push(tag);
        inputElement.value = currentTags.join(', ');
    }
}

// Edit idea function with prefilled data
async function editIdea(ideaId) {
    try {
        const response = await fetch(`scriptes/ideas_manager.php?action=get_idea&id=${ideaId}`);
        const data = await response.json();
        
        if (data.success) {
            const idea = data.idea;
            
            // Open modal and set modal title
            document.getElementById('ideaModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Edit Idea';
            
            // Set the hidden ideaId field for form submission
            document.getElementById('ideaId').value = ideaId;
            
            // Prefill form fields
            document.getElementById('ideaTitle').value = idea.title || '';
            // Strip HTML links from content for editing (show original text)
            const contentWithoutLinks = idea.content ? idea.content.replace(/<a[^>]*class="entity-link[^"]*"[^>]*>(.*?)<\/a>/g, '$1') : '';
            document.getElementById('ideaContent').value = contentWithoutLinks;
            document.getElementById('ideaCategory').value = idea.category || '';
            document.getElementById('ideaCertainty').value = idea.certainty_level || '';
            document.getElementById('ideaStatus').value = idea.status || '';
            document.getElementById('ideaComments').value = idea.comments || '';
            document.getElementById('ideaSource').value = idea.inspiration_source || '';
            document.getElementById('ideaParent').value = idea.parent_idea_id || '';
            
            // Handle tags
            if (idea.tags) {
                const tags = JSON.parse(idea.tags);
                document.getElementById('ideaTags').value = tags.join(', ');
            } else {
                document.getElementById('ideaTags').value = '';
            }
            
            // Load existing tags
            await loadExistingTags();
            
            // Load parent options
            await loadParentOptions();
        }
    } catch (error) {
        console.error('Error loading idea for editing:', error);
        alert('Error loading idea data');
    }
}

// Process all ideas for entity links
async function processAllEntityLinks() {
    if (!confirm('This will process all ideas to add entity links. This may take a while for large databases. Continue?')) {
        return;
    }
    
    const btn = document.getElementById('processLinksBtn');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = '⏳ Processing...';
    
    try {
        const response = await fetch('scriptes/ideas_manager.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'action=process_all_entity_links'
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`Entity links processed successfully!\n${data.message}`);
            // Reload ideas to show the updated content with links
            loadIdeas(1, false);
        } else {
            alert('Error processing entity links: ' + data.message);
        }
    } catch (error) {
        console.error('Error processing entity links:', error);
        alert('Error processing entity links. Please try again.');
    } finally {
        btn.disabled = false;
        btn.textContent = originalText;
    }
}

// Helper function to get current search terms
function getSearchTerms() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput || !searchInput.value.trim()) {
        return [];
    }
    
    // Split by spaces and filter out empty strings
    return searchInput.value.trim().toLowerCase().split(/\s+/).filter(term => term.length > 0);
}

// Helper function to highlight search terms in text
function highlightSearchTerms(text, searchTerms) {
    if (!text || !searchTerms || searchTerms.length === 0) {
        return text;
    }
    
    // Create a temporary DOM element to parse HTML safely
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = text;
    
    // Function to recursively process text nodes only
    function processTextNodes(node) {
        if (node.nodeType === Node.TEXT_NODE) {
            // This is a text node - apply highlighting
            let nodeText = node.textContent;
            let highlightedText = nodeText;
            
            searchTerms.forEach(term => {
                // Escape special regex characters
                const escapedTerm = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const regex = new RegExp(`(${escapedTerm})`, 'gi');
                highlightedText = highlightedText.replace(regex, '<mark class="search-highlight">$1</mark>');
            });
            
            // If highlighting was applied, replace the text node with HTML
            if (highlightedText !== nodeText) {
                const span = document.createElement('span');
                span.innerHTML = highlightedText;
                node.parentNode.replaceChild(span, node);
            }
        } else if (node.nodeType === Node.ELEMENT_NODE) {
            // This is an element node - process its children
            // Convert to array to avoid live NodeList issues during replacement
            const children = Array.from(node.childNodes);
            children.forEach(child => processTextNodes(child));
        }
    }
    
    // Process all text nodes in the temporary div
    processTextNodes(tempDiv);
    
    // Return the processed HTML
    return tempDiv.innerHTML;
}

</script>



<?php
require_once "./blueprints/gl_ap_end.php";
?>
