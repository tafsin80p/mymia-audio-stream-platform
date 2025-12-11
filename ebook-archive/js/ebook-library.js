/**
 * ========================================
 * NYMIA EBOOK LIBRARY - JAVASCRIPT (Archive Module)
 * ========================================
 * Handles filtering and interactions on the ebook library page
 * (moved under ebook-archive/js)
 */

document.addEventListener('DOMContentLoaded', function() {
    const filterTabs = document.querySelectorAll('.nymia-filter-tab');
    const ebookCards = document.querySelectorAll('.nymia-ebook-card');

    if (filterTabs.length > 0 && ebookCards.length > 0) {
        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const filterValue = this.getAttribute('data-filter');
                const categorySections = document.querySelectorAll('.nymia-ebook-category-section');

                categorySections.forEach(section => {
                    const sectionCategory = section.getAttribute('data-category-section');
                    const sectionCards = section.querySelectorAll('.nymia-ebook-card');

                    if (filterValue === 'all') {
                        section.style.display = 'block';
                        sectionCards.forEach(card => { card.style.display = 'block'; card.style.opacity = '1'; });
                    } else if (filterValue === 'recent') {
                        if (section === categorySections[0]) {
                            section.style.display = 'block';
                            sectionCards.forEach(card => { card.style.display = 'block'; card.style.opacity = '1'; });
                        } else {
                            section.style.display = 'none';
                        }
                    } else if (filterValue === 'popular' || filterValue === 'categories') {
                        section.style.display = 'block';
                        sectionCards.forEach(card => { card.style.display = 'block'; card.style.opacity = '1'; });
                    } else {
                        const filterCategoryNormalized = filterValue.replace(/\s+/g, '-').toLowerCase();
                        if (sectionCategory === filterCategoryNormalized ||
                            sectionCategory.includes(filterValue.toLowerCase()) ||
                            filterValue.toLowerCase().includes(sectionCategory)) {
                            section.style.display = 'block';
                            sectionCards.forEach(card => { card.style.display = 'block'; card.style.opacity = '1'; });
                        } else {
                            section.style.display = 'none';
                        }
                    }
                });

                ebookCards.forEach(card => {
                    const parentSection = card.closest('.nymia-ebook-category-section');
                    if (parentSection && parentSection.style.display === 'none') {
                        card.style.display = 'none';
                        return;
                    }
                    card.style.opacity = '0';
                    card.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => { card.style.opacity = '1'; }, 10);
                });

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

                updateShowMoreButtons(filterValue);
            });
        });
    }

    function updateShowMoreButtons(filterValue) {
        const showMoreButtons = document.querySelectorAll('.nymia-ebook-show-more-btn');
        showMoreButtons.forEach(btn => {
            const parentSection = btn.closest('.nymia-ebook-category-section');
            if (filterValue === 'all' || filterValue === 'categories' || filterValue === 'popular') {
                btn.closest('.nymia-ebook-show-more-wrapper').style.display = (parentSection && parentSection.style.display !== 'none') ? 'flex' : 'none';
            } else if (filterValue === 'recent') {
                const firstSection = document.querySelector('.nymia-ebook-category-section');
                btn.closest('.nymia-ebook-show-more-wrapper').style.display = (parentSection === firstSection && parentSection && parentSection.style.display !== 'none') ? 'flex' : 'none';
            } else {
                btn.closest('.nymia-ebook-show-more-wrapper').style.display = 'none';
            }
        });
    }

    const showMoreButtons = document.querySelectorAll('.nymia-ebook-show-more-btn');
    showMoreButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const shownCount = parseInt(this.getAttribute('data-shown')) || 4;
            const totalCount = parseInt(this.getAttribute('data-total')) || 0;
            const itemsPerPage = 4;
            const nextShown = shownCount + itemsPerPage;

            const parentSection = this.closest('.nymia-ebook-category-section');
            const hiddenCards = parentSection.querySelectorAll('.nymia-ebook-card.nymia-ebook-hidden');

            let shown = 0;
            hiddenCards.forEach((card, index) => {
                if (shown < itemsPerPage) {
                    card.classList.remove('nymia-ebook-hidden');
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 50);
                    shown++;
                }
            });

            const remaining = totalCount - nextShown;
            if (remaining > 0) {
                this.setAttribute('data-shown', nextShown);
                const countSpan = this.querySelector('.show-more-count');
                if (countSpan) { countSpan.textContent = remaining + ' more'; }
            } else {
                this.closest('.nymia-ebook-show-more-wrapper').style.display = 'none';
            }
        });
    });

    updateShowMoreButtons('all');
});


