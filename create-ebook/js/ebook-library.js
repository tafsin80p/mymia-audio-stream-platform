/**
 * ========================================
 * NYMIA EBOOK LIBRARY - JAVASCRIPT
 * ========================================
 * Handles filtering and interactions on the ebook library page
 * 
 * @package Nymia
 * @version 1.0
 */

document.addEventListener('DOMContentLoaded', function() {
    // ========================================
    // EBOOK FILTER FUNCTIONALITY
    // ========================================
    const filterTabs = document.querySelectorAll('.nymia-filter-tab');
    const ebookCards = document.querySelectorAll('.nymia-ebook-card');
    
    if (filterTabs.length > 0 && ebookCards.length > 0) {
        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                filterTabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Get filter value
                const filterValue = this.getAttribute('data-filter');
                
                // Get all category sections
                const categorySections = document.querySelectorAll('.nymia-ebook-category-section');
                
                // Filter category sections and cards
                categorySections.forEach(section => {
                    const sectionCategory = section.getAttribute('data-category-section');
                    const sectionCards = section.querySelectorAll('.nymia-ebook-card');
                    let sectionVisible = false;
                    
                    if (filterValue === 'all') {
                        // Show all sections and cards
                        section.style.display = 'block';
                        sectionCards.forEach(card => {
                            card.style.display = 'block';
                            card.style.opacity = '1';
                        });
                        sectionVisible = true;
                    } else if (filterValue === 'recent') {
                        // Show only the first section (most recent category)
                        if (section === categorySections[0]) {
                            section.style.display = 'block';
                            sectionCards.forEach(card => {
                                card.style.display = 'block';
                                card.style.opacity = '1';
                            });
                            sectionVisible = true;
                        } else {
                            section.style.display = 'none';
                        }
                } else if (filterValue === 'popular') {
                    // Show all sections (popular is sorted server-side)
                    section.style.display = 'block';
                    sectionCards.forEach(card => {
                        card.style.display = 'block';
                        card.style.opacity = '1';
                    });
                    sectionVisible = true;
                    } else if (filterValue === 'categories') {
                        // Show all category sections
                        section.style.display = 'block';
                        sectionCards.forEach(card => {
                            card.style.display = 'block';
                            card.style.opacity = '1';
                        });
                        sectionVisible = true;
                    } else {
                        // Category-specific filter - match category name
                        const filterCategoryNormalized = filterValue.replace(/\s+/g, '-').toLowerCase();
                        if (sectionCategory === filterCategoryNormalized || 
                            sectionCategory.includes(filterValue.toLowerCase()) ||
                            filterValue.toLowerCase().includes(sectionCategory)) {
                            section.style.display = 'block';
                            sectionCards.forEach(card => {
                                card.style.display = 'block';
                                card.style.opacity = '1';
                            });
                            sectionVisible = true;
                        } else {
                            section.style.display = 'none';
                        }
                    }
                });
                
                // Also filter individual cards for backward compatibility
                ebookCards.forEach(card => {
                    const cardCategory = card.getAttribute('data-category');
                    
                    // If parent section is visible, show the card
                    const parentSection = card.closest('.nymia-ebook-category-section');
                    if (parentSection && parentSection.style.display === 'none') {
                        card.style.display = 'none';
                        return;
                    }
                    
                    // Add fade-in animation
                    card.style.opacity = '0';
                    card.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => {
                        card.style.opacity = '1';
                    }, 10);
                });
                
                // Update grid to show empty state if no cards visible
                const visibleCards = Array.from(ebookCards).filter(card => card.style.display !== 'none');
                const emptyState = document.querySelector('.nymia-ebook-empty');
                const ebookGrid = document.querySelector('.nymia-ebook-grid');
                
                if (visibleCards.length === 0 && !emptyState && ebookGrid) {
                    const emptyDiv = document.createElement('div');
                    emptyDiv.className = 'nymia-ebook-empty';
                    emptyDiv.innerHTML = `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                        <h3>No Ebooks Found</h3>
                        <p>Try a different filter or create new ebooks!</p>
                    `;
                    ebookGrid.appendChild(emptyDiv);
                } else if (visibleCards.length > 0 && emptyState) {
                    emptyState.remove();
                }
                
                // Update "Show More" button visibility based on filter
                updateShowMoreButtons(filterValue);
            });
        });
    }
    // (Rating UI removed in revert)
    
    // ========================================
    // SHOW MORE FUNCTIONALITY
    // ========================================
    function updateShowMoreButtons(filterValue) {
        const showMoreButtons = document.querySelectorAll('.nymia-ebook-show-more-btn');
        showMoreButtons.forEach(btn => {
            const categorySection = btn.getAttribute('data-category');
            const parentSection = btn.closest('.nymia-ebook-category-section');
            
            if (filterValue === 'all' || filterValue === 'categories') {
                // Show button if parent section is visible
                if (parentSection && parentSection.style.display !== 'none') {
                    btn.closest('.nymia-ebook-show-more-wrapper').style.display = 'flex';
                } else {
                    btn.closest('.nymia-ebook-show-more-wrapper').style.display = 'none';
                }
            } else if (filterValue === 'popular') {
                // Show button for all visible sections
                if (parentSection && parentSection.style.display !== 'none') {
                    btn.closest('.nymia-ebook-show-more-wrapper').style.display = 'flex';
                } else {
                    btn.closest('.nymia-ebook-show-more-wrapper').style.display = 'none';
                }
            } else if (filterValue === 'recent') {
                // Only show for first section
                const firstSection = document.querySelector('.nymia-ebook-category-section');
                if (parentSection === firstSection && parentSection.style.display !== 'none') {
                    btn.closest('.nymia-ebook-show-more-wrapper').style.display = 'flex';
                } else {
                    btn.closest('.nymia-ebook-show-more-wrapper').style.display = 'none';
                }
            } else {
                // Category-specific - hide if section doesn't match
                btn.closest('.nymia-ebook-show-more-wrapper').style.display = 'none';
            }
        });
    }
    
    // Handle "Show More" button clicks
    const showMoreButtons = document.querySelectorAll('.nymia-ebook-show-more-btn');
    showMoreButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const categorySection = this.getAttribute('data-category');
            const shownCount = parseInt(this.getAttribute('data-shown')) || 4;
            const totalCount = parseInt(this.getAttribute('data-total')) || 0;
            const itemsPerPage = 4;
            const nextShown = shownCount + itemsPerPage;
            
            // Find all hidden ebooks in this category section
            const parentSection = this.closest('.nymia-ebook-category-section');
            const hiddenCards = parentSection.querySelectorAll('.nymia-ebook-card.nymia-ebook-hidden');
            
            // Show next 4 (or remaining) ebooks
            let shown = 0;
            hiddenCards.forEach((card, index) => {
                if (shown < itemsPerPage) {
                    card.classList.remove('nymia-ebook-hidden');
                    card.style.display = 'block';
                    // Fade in animation
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 50);
                    shown++;
                }
            });
            
            // Update button text or hide if all are shown
            const remaining = totalCount - nextShown;
            if (remaining > 0) {
                this.setAttribute('data-shown', nextShown);
                const countSpan = this.querySelector('.show-more-count');
                if (countSpan) {
                    countSpan.textContent = remaining + ' more';
                }
            } else {
                // Hide button if all ebooks are shown
                this.closest('.nymia-ebook-show-more-wrapper').style.display = 'none';
            }
        });
    });
    
    // Initialize - hide all "Show More" buttons if filter is not "all" or "categories"
    updateShowMoreButtons('all');
});

