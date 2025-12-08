/**
 * NYMIA THEME - MAIN JAVASCRIPT FILE
 * ===================================
 * This file contains all JavaScript functionality for the theme including:
 * - Mobile menu toggle
 * - Sidebar navigation
 * - Audio player controls
 * - Filter functionality
 * - Live streaming controls
 * - Chat functionality
 * 
 * @package Nymia
 * @version 1.0
 */

// ==========================================
// DOCUMENT READY - INITIALIZE ALL FUNCTIONALITY
// ==========================================
document.addEventListener('DOMContentLoaded', function () {
    // ========================================
    // MOBILE MENU TOGGLE
    // ========================================
    const mobileMenuToggle = document.querySelector('.nymia-mobile-menu-toggle');
    const sidebar = document.querySelector('.nymia-sidebar');
    const body = document.body;

    // Create mobile menu toggle button if it doesn't exist
    if (!mobileMenuToggle && sidebar) {
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'nymia-mobile-menu-toggle';
        toggleBtn.setAttribute('aria-label', 'Toggle Menu');
        toggleBtn.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        `;
        body.appendChild(toggleBtn);

        // Add click event to toggle button
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
        });
    } else if (mobileMenuToggle && sidebar) {
        mobileMenuToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
        });
    }

    // Close sidebar when clicking outside (on overlay)
    if (sidebar) {
        sidebar.addEventListener('click', function (e) {
            if (e.target === sidebar && sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                body.style.overflow = '';
            }
        });

        // Close sidebar when clicking on the overlay (::before pseudo-element)
        document.addEventListener('click', function (e) {
            if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && !e.target.closest('.nymia-mobile-menu-toggle')) {
                sidebar.classList.remove('open');
                body.style.overflow = '';
            }
        });

        // Close sidebar on navigation (tablet and mobile)
        const navLinks = sidebar.querySelectorAll('.nymia-nav-item:not(.nymia-nav-item-toggle)');
        navLinks.forEach(link => {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 1024) {
                    sidebar.classList.remove('open');
                    body.style.overflow = '';
                }
            });
        });
    }

    // ========================================
    // SUBMENU TOGGLE FUNCTIONALITY
    // ========================================
    const submenuToggles = document.querySelectorAll('.nymia-nav-item-toggle');

    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function () {
            const submenuId = this.getAttribute('data-submenu');
            const submenu = document.getElementById('submenu-' + submenuId);

            // Toggle active class on button
            this.classList.toggle('active');

            // Toggle active class on submenu
            if (submenu) {
                submenu.classList.toggle('active');
            }

            // Close other submenus
            submenuToggles.forEach(otherToggle => {
                if (otherToggle !== this) {
                    const otherSubmenuId = otherToggle.getAttribute('data-submenu');
                    const otherSubmenu = document.getElementById('submenu-' + otherSubmenuId);
                    otherToggle.classList.remove('active');
                    if (otherSubmenu) {
                        otherSubmenu.classList.remove('active');
                    }
                }
            });
        });
    });

    // ========================================
    // AUDIO CARD CLICK FUNCTIONALITY
    // ========================================
    // Note: Audio cards now link directly to creator profile via href attribute
    // This JavaScript is kept for backward compatibility but should not override the href
    const audioCards = document.querySelectorAll('.nymia-audio-card, .nymia-creator-card');
    audioCards.forEach(card => {
        // Only add click handler if card doesn't already have an href
        if (card.tagName !== 'A' || !card.getAttribute('href')) {
            card.addEventListener('click', function () {
                // Get creator data from the card
                const creatorName = this.querySelector('.nymia-creator-name')?.textContent || 'Creator';
                const creatorImage = this.querySelector('.nymia-card-image img')?.src || '';
                const views = this.querySelector('.nymia-creator-views span')?.textContent || '1.2k';

                // Build profile URL
                const profileUrl = '/profile/?username=' + encodeURIComponent(creatorName);
                window.location.href = profileUrl;
            });
        }
    });

    // ========================================
    // AUDIO PLAYER FUNCTIONALITY (BASIC)
    // ========================================
    let currentTrack = null;
    let currentTime = 0;
    let isPlaying = false;
    let progressInterval = null;

    function showAccessPrompt(title, price) {
        const titleText = title ? `"${title}" ` : '';
        const priceValue = price && !Number.isNaN(parseFloat(price)) ? parseFloat(price).toFixed(2) : '';
        const priceText = priceValue ? `($${priceValue}) ` : '';
        const messagePrefix = `${titleText}${priceText}`.trim();
        const message = messagePrefix
            ? `${messagePrefix}<?php echo esc_js(__(' is premium content. Please purchase to listen.', 'nymia')); ?>`
            : `<?php echo esc_js(__('This track is premium content. Please purchase to listen.', 'nymia')); ?>`;
        alert(message);
    }
    window.nymiaShowAccessPrompt = showAccessPrompt;

    function canPlayPaidAudio(element) {
        if (!element) {
            return true;
        }

        const requiresPayment = element.getAttribute('data-paid') === 'yes';
        const price = parseFloat(element.getAttribute('data-price') || '0');

        if (!requiresPayment || !(price > 0)) {
            return true;
        }

        const creatorId = parseInt(element.getAttribute('data-creator-id') || '0', 10);
        const ajaxData = window.nymiaAjax || {};
        const currentUserId = ajaxData.currentUserId ? parseInt(ajaxData.currentUserId, 10) : 0;
        const currentRoles = Array.isArray(ajaxData.currentUserRoles) ? ajaxData.currentUserRoles : [];
        const privilegedRoles = Array.isArray(ajaxData.paidAudioPrivilegedRoles) ? ajaxData.paidAudioPrivilegedRoles : [];

        let allowed = false;

        if (currentUserId && creatorId && currentUserId === creatorId) {
            allowed = true;
        } else if (currentRoles.length && privilegedRoles.length) {
            allowed = currentRoles.some(role => privilegedRoles.includes(role));
        }

        if (typeof window.nymiaCanAccessPaidAudio === 'function') {
            allowed = window.nymiaCanAccessPaidAudio(allowed, element);
        }

        return allowed;
    }

    // Play/Pause functionality
    const playButtons = document.querySelectorAll('.nymia-play-btn');
    playButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.stopPropagation();
            const trackNumber = this.getAttribute('data-track');
            const trackItem = this.closest('.nymia-audio-track-item');

            if (!canPlayPaidAudio(trackItem)) {
                return;
            }

            // If clicking the same track, toggle play/pause
            if (currentTrack === trackNumber) {
                togglePlayPause();
            } else {
                // Stop current track and play new one
                stopCurrentTrack();
                playTrack(trackNumber, trackItem);
            }
        });
    });

    function playTrack(trackNumber, trackItem) {
        if (!canPlayPaidAudio(trackItem)) {
            return;
        }

        currentTrack = trackNumber;
        isPlaying = true;

        // Update UI
        trackItem.classList.add('playing');
        updatePlayButton(trackItem, true);

        // Start progress simulation
        startProgress(trackItem);

        console.log('Playing track:', trackNumber);
    }

    function togglePlayPause() {
        if (isPlaying) {
            pauseTrack();
        } else {
            resumeTrack();
        }
    }

    function pauseTrack() {
        isPlaying = false;
        const currentTrackItem = document.querySelector(`[data-track="${currentTrack}"]`);
        if (currentTrackItem) {
            updatePlayButton(currentTrackItem, false);
            stopProgress();
        }
        console.log('Paused track:', currentTrack);
    }

    function resumeTrack() {
        isPlaying = true;
        const currentTrackItem = document.querySelector(`[data-track="${currentTrack}"]`);
        if (currentTrackItem) {
            updatePlayButton(currentTrackItem, true);
            startProgress(currentTrackItem);
        }
        console.log('Resumed track:', currentTrack);
    }

    function stopCurrentTrack() {
        if (currentTrack) {
            const currentTrackItem = document.querySelector(`[data-track="${currentTrack}"]`);
            if (currentTrackItem) {
                currentTrackItem.classList.remove('playing');
                updatePlayButton(currentTrackItem, false);
                resetProgress(currentTrackItem);
            }
        }
        stopProgress();
        currentTime = 0;
    }

    function updatePlayButton(trackItem, isPlaying) {
        const playBtn = trackItem.querySelector('.nymia-play-btn');
        const playIcon = playBtn.querySelector('.play-icon');
        const pauseIcon = playBtn.querySelector('.pause-icon');

        if (isPlaying) {
            playIcon.style.display = 'none';
            pauseIcon.style.display = 'block';
        } else {
            playIcon.style.display = 'block';
            pauseIcon.style.display = 'none';
        }
    }

    function startProgress(trackItem) {
        const duration = trackItem.getAttribute('data-duration');
        const durationSeconds = parseDuration(duration);

        progressInterval = setInterval(() => {
            if (isPlaying) {
                currentTime += 0.1;
                const progressPercent = (currentTime / durationSeconds) * 100;

                updateProgressBar(trackItem, progressPercent);
                updateTimeDisplay(trackItem, currentTime);

                // Auto-stop when track ends
                if (currentTime >= durationSeconds) {
                    stopCurrentTrack();
                    // Auto-play next track
                    playNextTrack();
                }
            }
        }, 100);
    }

    function stopProgress() {
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
    }

    function resetProgress(trackItem) {
        updateProgressBar(trackItem, 0);
        updateTimeDisplay(trackItem, 0);
    }

    function updateProgressBar(trackItem, percent) {
        const progressFill = trackItem.querySelector('.nymia-progress-fill');
        if (progressFill) {
            progressFill.style.width = Math.min(percent, 100) + '%';
        }
    }

    function updateTimeDisplay(trackItem, seconds) {
        const timeDisplay = trackItem.querySelector('.nymia-progress-time');
        if (timeDisplay) {
            timeDisplay.textContent = formatTime(seconds);
        }
    }

    function parseDuration(duration) {
        const parts = duration.split(':');
        return parseInt(parts[0]) * 60 + parseInt(parts[1]);
    }

    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

    function playNextTrack() {
        if (currentTrack) {
            const nextTrackNumber = parseInt(currentTrack) + 1;
            const nextTrackItem = document.querySelector(`[data-track="${nextTrackNumber}"]`);
            if (nextTrackItem) {
                playTrack(nextTrackNumber, nextTrackItem);
            }
        }
    }

    // Enhanced Audio Player Functionality
    let audioPlayer = {
        currentTrack: null,
        currentTime: 0,
        duration: 0,
        isPlaying: false,
        volume: 0.7,
        progressInterval: null,

        init() {
            this.setupEventListeners();
            this.updateVolumeDisplay();
        },

        setupEventListeners() {
            // Main play button
            const mainPlayBtn = document.getElementById('mainPlayBtn');
            if (mainPlayBtn) {
                mainPlayBtn.addEventListener('click', () => this.toggleMainPlay());
            }

            // Previous/Next buttons
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            if (prevBtn) prevBtn.addEventListener('click', () => this.previousTrack());
            if (nextBtn) nextBtn.addEventListener('click', () => this.nextTrack());

            // Volume controls
            const volumeBtn = document.getElementById('volumeBtn');
            const volumeSlider = document.getElementById('volumeSlider');
            if (volumeBtn) volumeBtn.addEventListener('click', () => this.toggleMute());
            if (volumeSlider) {
                volumeSlider.addEventListener('input', (e) => this.setVolume(e.target.value / 100));
            }

            // Progress bar
            const progressBar = document.getElementById('mainProgressBar');
            if (progressBar) {
                progressBar.addEventListener('click', (e) => this.seekTo(e));
            }
        },

        toggleMainPlay() {
            if (this.isPlaying) {
                this.pause();
            } else {
                if (this.currentTrack) {
                    this.play();
                } else {
                    // Play first track if none selected
                    const firstTrack = document.querySelector('[data-track="1"]');
                    if (firstTrack) {
                        const trackNumber = firstTrack.getAttribute('data-track');
                        playTrack(trackNumber, firstTrack);
                    }
                }
            }
        },

        play() {
            this.isPlaying = true;
            this.updateMainPlayButton(true);
            this.startProgress();
        },

        pause() {
            this.isPlaying = false;
            this.updateMainPlayButton(false);
            this.stopProgress();
        },

        updateMainPlayButton(isPlaying) {
            const mainPlayBtn = document.getElementById('mainPlayBtn');
            if (mainPlayBtn) {
                const playIcon = mainPlayBtn.querySelector('.play-icon');
                const pauseIcon = mainPlayBtn.querySelector('.pause-icon');

                if (isPlaying) {
                    playIcon.style.display = 'none';
                    pauseIcon.style.display = 'block';
                } else {
                    playIcon.style.display = 'block';
                    pauseIcon.style.display = 'none';
                }
            }
        },

        setCurrentTrack(trackNumber, trackItem) {
            this.currentTrack = trackNumber;
            this.duration = this.parseDuration(trackItem.getAttribute('data-duration'));
            this.currentTime = 0;

            // Update current track info
            const title = trackItem.getAttribute('data-title');
            const artist = trackItem.getAttribute('data-artist');
            this.updateCurrentTrackInfo(title, artist);
            this.updateTotalTime();
        },

        updateCurrentTrackInfo(title, artist) {
            const titleEl = document.querySelector('.nymia-current-track-title');
            const artistEl = document.querySelector('.nymia-current-track-artist');

            if (titleEl) titleEl.textContent = title;
            if (artistEl) artistEl.textContent = artist;
        },

        startProgress() {
            this.progressInterval = setInterval(() => {
                if (this.isPlaying) {
                    this.currentTime += 0.1;
                    const progressPercent = (this.currentTime / this.duration) * 100;

                    this.updateMainProgress(progressPercent);
                    this.updateCurrentTime();

                    // Auto-stop when track ends
                    if (this.currentTime >= this.duration) {
                        this.pause();
                        this.nextTrack();
                    }
                }
            }, 100);
        },

        stopProgress() {
            if (this.progressInterval) {
                clearInterval(this.progressInterval);
                this.progressInterval = null;
            }
        },

        updateMainProgress(percent) {
            const progressFill = document.getElementById('mainProgressFill');
            if (progressFill) {
                progressFill.style.width = Math.min(percent, 100) + '%';
            }
        },

        updateCurrentTime() {
            const currentTimeEl = document.querySelector('.nymia-current-time');
            if (currentTimeEl) {
                currentTimeEl.textContent = this.formatTime(this.currentTime);
            }
        },

        updateTotalTime() {
            const totalTimeEl = document.querySelector('.nymia-total-time');
            if (totalTimeEl) {
                totalTimeEl.textContent = this.formatTime(this.duration);
            }
        },

        seekTo(event) {
            const progressBar = event.currentTarget;
            const rect = progressBar.getBoundingClientRect();
            const clickX = event.clientX - rect.left;
            const width = rect.width;
            const percentage = clickX / width;

            this.currentTime = this.duration * percentage;
            const progressPercent = (this.currentTime / this.duration) * 100;
            this.updateMainProgress(progressPercent);
            this.updateCurrentTime();
        },

        setVolume(volume) {
            this.volume = volume;
            this.updateVolumeDisplay();
        },

        toggleMute() {
            if (this.volume > 0) {
                this.lastVolume = this.volume;
                this.setVolume(0);
            } else {
                this.setVolume(this.lastVolume || 0.7);
            }
        },

        updateVolumeDisplay() {
            const volumeBtn = document.getElementById('volumeBtn');
            const volumeHigh = volumeBtn?.querySelector('.volume-high');
            const volumeMute = volumeBtn?.querySelector('.volume-mute');

            if (this.volume === 0) {
                if (volumeHigh) volumeHigh.style.display = 'none';
                if (volumeMute) volumeMute.style.display = 'block';
            } else {
                if (volumeHigh) volumeHigh.style.display = 'block';
                if (volumeMute) volumeMute.style.display = 'none';
            }

            const volumeSlider = document.getElementById('volumeSlider');
            if (volumeSlider) {
                volumeSlider.value = this.volume * 100;
            }
        },

        previousTrack() {
            if (this.currentTrack) {
                const prevTrackNumber = parseInt(this.currentTrack) - 1;
                const prevTrackItem = document.querySelector(`[data-track="${prevTrackNumber}"]`);
                if (prevTrackItem) {
                    playTrack(prevTrackNumber, prevTrackItem);
                }
            }
        },

        nextTrack() {
            if (this.currentTrack) {
                const nextTrackNumber = parseInt(this.currentTrack) + 1;
                const nextTrackItem = document.querySelector(`[data-track="${nextTrackNumber}"]`);
                if (nextTrackItem) {
                    playTrack(nextTrackNumber, nextTrackItem);
                }
            }
        },

        parseDuration(duration) {
            const parts = duration.split(':');
            return parseInt(parts[0]) * 60 + parseInt(parts[1]);
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }
    };

    // Initialize audio player when DOM is loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => audioPlayer.init());
    } else {
        audioPlayer.init();
    }

    // ========================================
    // REAL AUDIO ELEMENT HANDLING
    // ========================================
    let currentAudio = null;
    let audioElements = {};

    // Initialize audio elements
    function initAudioElements() {
        for (let i = 1; i <= 5; i++) {
            const audio = document.getElementById(`track${i}`);
            if (audio) {
                audioElements[i] = audio;

                // Add event listeners for audio events
                audio.addEventListener('loadeddata', () => {
                    console.log(`Track ${i} loaded`);
                });

                audio.addEventListener('ended', () => {
                    console.log(`Track ${i} ended`);
                    stopCurrentTrack();
                    playNextTrack();
                });

                audio.addEventListener('error', (e) => {
                    console.log(`Error loading track ${i}:`, e);
                });
            }
        }
    }

    // Update the existing playTrack function to work with real audio
    const originalPlayTrack = playTrack;
    playTrack = function (trackNumber, trackItem) {
        const trackTitle = trackItem ? trackItem.getAttribute('data-title') : '';
        const trackPrice = trackItem ? trackItem.getAttribute('data-price') : '';

        if (!canPlayPaidAudio(trackItem)) {
            const accessBtn = trackItem ? trackItem.querySelector('.nymia-track-access-btn') : null;
            if (accessBtn) {
                accessBtn.focus();
                accessBtn.classList.add('pulse');
                setTimeout(() => accessBtn.classList.remove('pulse'), 1000);
            }
            showAccessPrompt(trackTitle, trackPrice);
            return;
        }

        // Stop current track
        stopCurrentTrack();

        // Set up new track
        currentTrack = trackNumber;
        isPlaying = true;

        // Update UI
        trackItem.classList.add('playing');
        updatePlayButton(trackItem, true);

        // Set up audio player
        audioPlayer.setCurrentTrack(trackNumber, trackItem);
        audioPlayer.play();

        // Play real audio
        playRealAudio(trackNumber);

        // Start progress simulation
        startProgress(trackItem);

        console.log('Playing track:', trackNumber);
    };

    function playRealAudio(trackNumber) {
        // Stop any currently playing audio
        if (currentAudio) {
            currentAudio.pause();
            currentAudio.currentTime = 0;
        }

        // Get the audio element for this track
        const audio = audioElements[trackNumber];
        if (audio) {
            currentAudio = audio;

            // Try to play the audio
            audio.play().then(() => {
                console.log(`Playing real audio for track ${trackNumber}`);
            }).catch((error) => {
                console.log(`Could not play audio for track ${trackNumber}:`, error);
                // Fallback: create a simple beep sound
                createBeepSound();
            });
        } else {
            console.log(`No audio element found for track ${trackNumber}`);
            // Fallback: create a simple beep sound
            createBeepSound();
        }
    }

    function createBeepSound() {
        // Create a simple beep sound using Web Audio API
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.setValueAtTime(440, audioContext.currentTime); // A4 note
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);

            console.log('Playing beep sound');
        } catch (error) {
            console.log('Could not create beep sound:', error);
        }
    }

    function stopRealAudio() {
        if (currentAudio) {
            currentAudio.pause();
            currentAudio.currentTime = 0;
            currentAudio = null;
        }
    }

    // Update the stopCurrentTrack function to stop real audio
    const originalStopCurrentTrack = stopCurrentTrack;
    stopCurrentTrack = function () {
        originalStopCurrentTrack();
        stopRealAudio();
    };

    // Initialize audio elements when DOM is loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAudioElements);
    } else {
        initAudioElements();
    }

    // ========================================
    // FILTER BUTTON FUNCTIONALITY
    // ========================================
    // Applies to: Dashboard, Audio page, Ebook page
    const filterButtons = document.querySelectorAll('.nymia-filter-btn, .nymia-filter-tab');
    const sections = document.querySelectorAll('.nymia-section, .nymia-dashboard-section');
    const ebookCards = document.querySelectorAll('.nymia-ebook-card');

    // Debug logging
    console.log('Filter system initialized');
    console.log('Filter buttons:', filterButtons.length);
    console.log('Ebook cards:', ebookCards.length);

    // Function to apply filter
    function applyFilter(filterValue) {
        // Remove active class from all buttons
        filterButtons.forEach(btn => btn.classList.remove('active'));

        // Add active class to matching button
        filterButtons.forEach(btn => {
            const btnFilter = btn.getAttribute('data-filter');
            if (btnFilter === filterValue) {
                btn.classList.add('active');
            }
        });

        // Show/hide sections based on filter (for dashboard)
        sections.forEach(section => {
            const categories = section.getAttribute('data-category');

            if (!categories) return;

            const categoryList = categories.split(' ');

            if (filterValue === 'all') {
                // Show all sections when "All" is selected
                section.style.display = 'block';
            } else if (categoryList.includes(filterValue)) {
                // Show matching section
                section.style.display = 'block';
            } else {
                // Hide non-matching sections
                section.style.display = 'none';
            }
        });

        // Show/hide audio cards based on filter (for audio page)
        audioCards.forEach((card, index) => {
            const categories = card.getAttribute('data-category');

            if (!categories) return;

            const categoryList = categories.split(' ');

            if (filterValue === 'all') {
                // Show all cards with animation
                setTimeout(() => {
                    card.style.display = 'block';
                    card.style.animation = 'fadeInCard 0.4s ease forwards';
                }, index * 50);
            } else if (categoryList.includes(filterValue)) {
                // Show matching card with animation
                setTimeout(() => {
                    card.style.display = 'block';
                    card.style.animation = 'fadeInCard 0.4s ease forwards';
                }, index * 50);
            } else {
                // Hide non-matching card
                card.style.opacity = '0';
                setTimeout(() => {
                    card.style.display = 'none';
                }, 300);
            }
        });

        // Show/hide Ebook cards based on filter (for Ebook page)
        if (ebookCards.length > 0) {
            console.log('Filtering Ebook cards, filter value:', filterValue);

            // First, hide all cards immediately
            ebookCards.forEach((card) => {
                card.style.opacity = '0';
            });

            // Then show matching cards with animation
            let visibleIndex = 0;
            ebookCards.forEach((card, index) => {
                const categories = card.getAttribute('data-category');
                console.log(`Card ${index} category:`, categories);

                if (!categories) {
                    card.style.display = 'none';
                    console.log(`Card ${index}: No category, hiding`);
                    return;
                }

                const categoryList = categories.split(' ');
                const shouldShow = filterValue === 'all' || categoryList.includes(filterValue);
                console.log(`Card ${index}: Should show = ${shouldShow}`);

                if (shouldShow) {
                    // Show matching card with staggered animation
                    setTimeout(() => {
                        card.style.display = 'block';
                        card.style.opacity = '1';
                        card.style.animation = 'fadeInCard 0.4s ease forwards';
                    }, visibleIndex * 50);
                    visibleIndex++;
                } else {
                    // Hide non-matching card
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        }
    }

    // Check for filter parameter in URL (for dashboard)
    const urlParams = new URLSearchParams(window.location.search);
    const filterParam = urlParams.get('filter');
    if (filterParam && sections.length > 0) {
        // Apply filter from URL parameter
        applyFilter(filterParam);
    }

    filterButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Get the filter value
            const filterValue = this.getAttribute('data-filter');
            console.log('Filter clicked:', filterValue);

            if (filterValue) {
                applyFilter(filterValue);
            }
        });
    });

    // ========================================
    // SMOOTH SCROLLING FOR ANCHOR LINKS
    // ========================================
    const anchorLinks = document.querySelectorAll('a[href^="#"]');

    anchorLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // ========================================
    // LOADING ANIMATION FOR CONTENT CARDS
    // ========================================
    const contentCards = document.querySelectorAll('.nymia-content-card');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    contentCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // ========================================
    // LIVE AUDIO STREAM CONTROLS
    // ========================================
    const volumeBtn = document.querySelector('.nymia-stream-controls .nymia-control-btn:nth-child(1)');
    const muteBtn = document.querySelector('.nymia-stream-controls .nymia-control-btn:nth-child(2)');
    const shareBtn = document.querySelector('.nymia-stream-controls .nymia-control-btn:nth-child(3)');
    const leaveBtn = document.querySelector('.nymia-stream-controls .nymia-control-leave');
    const audioButtons = document.querySelectorAll('.nymia-audio-btn');
    const fullscreenBtn = document.querySelector('.nymia-fullscreen-btn');

    // Volume toggle
    if (volumeBtn) {
        let volumeOn = true;
        volumeBtn.addEventListener('click', function () {
            volumeOn = !volumeOn;
            console.log('Volume:', volumeOn ? 'On' : 'Off');
            // You can add visual feedback here
        });
    }

    // Mute toggle
    if (muteBtn) {
        let isMuted = false;
        muteBtn.addEventListener('click', function () {
            isMuted = !isMuted;
            console.log('Muted:', isMuted);
            this.style.opacity = isMuted ? '0.5' : '1';
        });
    }

    // Share button
    if (shareBtn) {
        shareBtn.addEventListener('click', function () {
            console.log('Share stream');
            alert('Share link copied to clipboard!');
        });
    }

    // Leave stream
    if (leaveBtn) {
        leaveBtn.addEventListener('click', function () {
            if (confirm('Are you sure you want to leave this stream?')) {
                console.log('Leaving stream');
                window.location.href = '/';
            }
        });
    }

    // Audio buttons on thumbnails
    audioButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            console.log('Toggle audio for thumbnail');
            this.classList.toggle('muted');
        });
    });

    // Fullscreen button
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', function () {
            const videoArea = document.querySelector('.nymia-main-video');
            if (videoArea) {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                } else {
                    videoArea.requestFullscreen().catch(err => {
                        console.log('Fullscreen error:', err);
                    });
                }
            }
        });
    }

    document.addEventListener('click', function (e) {
        const accessBtn = e.target.closest('.nymia-track-access-btn, .nymia-audio-access-btn');
        if (!accessBtn) return;
        const parent = accessBtn.closest('[data-title]');
        const title = accessBtn.getAttribute('data-title') || (parent ? parent.getAttribute('data-title') : '');
        const price = accessBtn.getAttribute('data-price') || (parent ? parent.getAttribute('data-price') : '');
        showAccessPrompt(title, price);
    });

    document.addEventListener('click', function (e) {
        const externalPlay = e.target.closest('.nymia-play-button, .nymia-trending-play');
        if (!externalPlay) return;
        const card = externalPlay.closest('[data-audio-url]');
        if (!card) return;
        if (canPlayPaidAudio(card)) {
            return;
        }
        const title = card.getAttribute('data-title') || '';
        const price = card.getAttribute('data-price') || '';
        showAccessPrompt(title, price);
        externalPlay.blur();
        e.preventDefault();
    });

    // ========================================
    // LIVE CHAT FUNCTIONALITY
    // ========================================
    const chatInput = document.querySelector('.nymia-chat-input-field');
    const sendBtn = document.querySelector('.nymia-send-btn');
    const chatMessages = document.querySelector('.nymia-chat-messages');

    if (sendBtn && chatInput && chatMessages) {
        sendBtn.addEventListener('click', function () {
            const message = chatInput.value.trim();
            if (message) {
                console.log('Sending message:', message);
                // Add message to chat (you can implement this)
                chatInput.value = '';
            }
        });

        chatInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                sendBtn.click();
            }
        });
    }

    // ========================================
    // LIVE SEARCH FUNCTIONALITY
    // ========================================
    const searchInput = document.getElementById('nymia-header-search');
    const searchResults = document.getElementById('nymia-live-search-results');
    const searchContent = searchResults ? searchResults.querySelector('.nymia-live-search-content') : null;
    const searchLoading = searchResults ? searchResults.querySelector('.nymia-live-search-loading') : null;
    let searchTimeout = null;

    if (searchInput && searchResults && searchContent) {
        // Handle search input
        searchInput.addEventListener('input', function (e) {
            const query = e.target.value.trim();

            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            // Hide dropdown if query is too short
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            // Show loading
            if (searchLoading) {
                searchLoading.style.display = 'block';
            }
            if (searchContent) {
                searchContent.innerHTML = '';
            }
            searchResults.style.display = 'block';

            // Debounce search (wait 300ms after user stops typing)
            searchTimeout = setTimeout(function () {
                performLiveSearch(query);
            }, 300);
        });

        // Handle search form submit
        const searchForm = searchInput ? searchInput.closest('form') : null;
        if (searchForm) {
            searchForm.addEventListener('submit', function (e) {
                const query = searchInput.value.trim();
                // If dropdown is open and user presses enter, prevent default and let dropdown handle it
                if (searchResults.style.display === 'block' && query.length >= 2) {
                    const firstResult = searchContent.querySelector('.nymia-live-search-item');
                    if (firstResult && firstResult.getAttribute('href')) {
                        e.preventDefault();
                        window.location.href = firstResult.getAttribute('href');
                    }
                }
            });
        }

        // Hide dropdown when clicking outside
        document.addEventListener('click', function (e) {
            const searchWrapper = searchInput ? searchInput.closest('.nymia-header-search-wrapper') : null;
            if (searchWrapper && !searchWrapper.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        // Hide dropdown on Escape key
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                searchResults.style.display = 'none';
            }
        });
    }

    /**
     * Perform Live Search
     * Sends AJAX request to search for users, posts, audio, and ebooks
     */
    function performLiveSearch(query) {
        if (!window.nymiaAjax || !window.nymiaAjax.searchNonce) {
            console.error('Search configuration not available');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'nymia_live_search');
        formData.append('query', query);
        formData.append('nonce', window.nymiaAjax.searchNonce);

        fetch(window.nymiaAjax.ajaxurl, {
            method: 'POST',
            body: formData
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (searchLoading) {
                    searchLoading.style.display = 'none';
                }

                if (data.success && data.data) {
                    renderSearchResults(data.data);
                } else {
                    if (searchContent) {
                        searchContent.innerHTML = '<div style="padding: 20px; text-align: center; color: rgba(255,255,255,0.6);">' +
                            (data.data && data.data.message ? data.data.message : 'No results found.') +
                            '</div>';
                    }
                }
            })
            .catch(function (error) {
                console.error('Live search error:', error);
                if (searchLoading) {
                    searchLoading.style.display = 'none';
                }
                if (searchContent) {
                    searchContent.innerHTML = '<div style="padding: 20px; text-align: center; color: rgba(255,0,0,0.6);">Search error. Please try again.</div>';
                }
            });
    }

    /**
     * Render Search Results
     * Displays search results in dropdown
     */
    function renderSearchResults(results) {
        if (!searchContent) return;

        let html = '';
        let hasResults = false;

        // Users section
        if (results.users && results.users.length > 0) {
            html += '<div class="nymia-live-search-section"><div class="nymia-live-search-section-title">Users</div>';
            results.users.forEach(function (user) {
                html += '<a href="' + user.profile_url + '" class="nymia-live-search-item nymia-live-search-user">';
                html += '<img src="' + (user.avatar || '/wp-content/themes/nymia-wp-theme/assets/images/profile.png') + '" alt="' + user.name + '" class="nymia-live-search-avatar" />';
                html += '<div class="nymia-live-search-item-info">';
                html += '<div class="nymia-live-search-item-title">' + user.name + '</div>';
                html += '<div class="nymia-live-search-item-meta">' + user.username + '</div>';
                html += '</div></a>';
            });
            html += '</div>';
            hasResults = true;
        }

        // Posts section
        if (results.posts && results.posts.length > 0) {
            html += '<div class="nymia-live-search-section"><div class="nymia-live-search-section-title">Posts</div>';
            results.posts.forEach(function (post) {
                html += '<a href="' + post.url + '" class="nymia-live-search-item nymia-live-search-post">';
                if (post.image) {
                    html += '<img src="' + post.image + '" alt="' + post.title + '" class="nymia-live-search-image" />';
                }
                html += '<div class="nymia-live-search-item-info">';
                html += '<div class="nymia-live-search-item-title">' + post.title + '</div>';
                html += '<div class="nymia-live-search-item-meta">by ' + post.author_name + '</div>';
                html += '</div></a>';
            });
            html += '</div>';
            hasResults = true;
        }

        // Audio section
        if (results.audio && results.audio.length > 0) {
            html += '<div class="nymia-live-search-section"><div class="nymia-live-search-section-title">Audio</div>';
            results.audio.forEach(function (audio) {
                html += '<a href="' + audio.url + '" class="nymia-live-search-item nymia-live-search-audio">';
                html += '<img src="' + (audio.image || '/wp-content/themes/nymia-wp-theme/assets/images/audio-placeholder.jpg') + '" alt="' + audio.title + '" class="nymia-live-search-image" />';
                html += '<div class="nymia-live-search-item-info">';
                html += '<div class="nymia-live-search-item-title">' + audio.title + '</div>';
                html += '<div class="nymia-live-search-item-meta">by ' + audio.creator + '</div>';
                html += '</div></a>';
            });
            html += '</div>';
            hasResults = true;
        }

        // Ebooks section
        if (results.ebooks && results.ebooks.length > 0) {
            html += '<div class="nymia-live-search-section"><div class="nymia-live-search-section-title">Ebooks</div>';
            results.ebooks.forEach(function (ebook) {
                html += '<a href="' + ebook.url + '" class="nymia-live-search-item nymia-live-search-ebook">';
                html += '<img src="' + (ebook.thumbnail || '/wp-content/themes/nymia-wp-theme/assets/images/ebook-placeholder.jpg') + '" alt="' + ebook.title + '" class="nymia-live-search-image" />';
                html += '<div class="nymia-live-search-item-info">';
                html += '<div class="nymia-live-search-item-title">' + ebook.title + '</div>';
                html += '<div class="nymia-live-search-item-meta">by ' + ebook.author + '</div>';
                html += '</div></a>';
            });
            html += '</div>';
            hasResults = true;
        }

        if (!hasResults) {
            html = '<div style="padding: 20px; text-align: center; color: rgba(255,255,255,0.6);">No results found.</div>';
        }

        searchContent.innerHTML = html;
    }
});

// ==========================================
// FOLLOW/UNFOLLOW FUNCTIONALITY
// ==========================================

/**
 * TOGGLE FOLLOW STATUS
 * --------------------
 * Handles follow/unfollow button clicks
 * Sends AJAX request to toggle follow status
 * Updates button text and state dynamically
 * 
 * @param {HTMLElement} button The button element that was clicked
 * @param {int} user_id The user ID to follow/unfollow
 */
function nymiaToggleFollow(button, user_id) {
    // PREVENT: Default action
    if (typeof event !== 'undefined') {
        event.stopPropagation();
    }

    // GET: Current state
    const currentText = button.textContent.trim();
    const isFollowing = currentText === 'Following';

    // CHECK: If nymiaAjax is defined
    if (typeof nymiaAjax === 'undefined' || !nymiaAjax.followNonce) {
        alert('Configuration error. Please refresh the page.');
        return;
    }

    // DISABLE: Button during request
    button.disabled = true;
    button.textContent = isFollowing ? 'Unfollowing...' : 'Following...';

    // PREPARE: Data for AJAX request
    const formData = new FormData();
    formData.append('action', 'nymia_toggle_follow');
    formData.append('user_id', user_id);
    formData.append('nonce', nymiaAjax.followNonce);

    // SEND: AJAX request
    fetch(nymiaAjax.ajaxurl, {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // ENABLE: Button
            button.disabled = false;

            // CHECK: If request was successful
            if (data.success) {
                // UPDATE: Button text and state
                if (data.data.is_following) {
                    button.textContent = 'Following';
                    button.classList.add('following');
                } else {
                    button.textContent = 'Follow';
                    button.classList.remove('following');
                }
            } else {
                // RESET: Button to previous state
                button.textContent = isFollowing ? 'Following' : 'Follow';
                alert(data.data.message || 'Something went wrong. Please try again.');
            }
        })
        .catch(error => {
            console.error('Follow toggle error:', error);
            button.disabled = false;
            button.textContent = isFollowing ? 'Following' : 'Follow';
            alert('Network error. Please check console for details and try again.');
        });
}
