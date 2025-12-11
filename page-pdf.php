<?php
/**
 * Template Name: PDF
 * 
 * PDF Library Page Template
 */

get_header(); 
get_sidebar();
?>

<div class="nymia-container">
    <main class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/back-button'); ?>
        
        <?php get_template_part('template-parts/filters'); ?>
        
        <div class="nymia-pdf-page">
            <!-- PDF Header -->
            <div class="nymia-pdf-header">
                <div class="nymia-pdf-header-content">
                    <h1 class="nymia-pdf-main-title">
                        <div class="nymia-title-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </div>
                        PDF Library
                    </h1>
                    <p class="nymia-pdf-subtitle">Browse and read premium PDF content</p>
                </div>

                <!-- Filter Tabs -->
                <div class="nymia-filter-tabs">
                    <button class="nymia-filter-tab active" data-filter="all">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                        <span>All</span>
                    </button>
                    <button class="nymia-filter-tab" data-filter="recent">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>Recent</span>
                    </button>
                    <button class="nymia-filter-tab" data-filter="popular">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span>Popular</span>
                    </button>
                    <button class="nymia-filter-tab" data-filter="categories">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span>Categories</span>
                    </button>
                </div>
            </div>

            <!-- Content Layout -->
            <div class="nymia-pdf-layout">
                <!-- Main Grid Content -->
                <div class="nymia-pdf-grid">
                    <?php 
                    // Dummy data for PDF posts
                    $pdf_posts = array(
                        array('title' => 'Digital Marketing Guide 2024', 'author' => 'Sarah Johnson', 'pages' => '156', 'size' => '12 MB', 'image' => 'https://images.unsplash.com/photo-1432888498266-38ffec3eaf0a?w=400&h=500&fit=crop', 'category' => 'recent'),
                        array('title' => 'Photography Masterclass', 'author' => 'Mike Peterson', 'pages' => '243', 'size' => '28 MB', 'image' => 'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?w=400&h=500&fit=crop', 'category' => 'popular'),
                        array('title' => 'Business Strategy Handbook', 'author' => 'Emily Chen', 'pages' => '198', 'size' => '15 MB', 'image' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=400&h=500&fit=crop', 'category' => 'recent'),
                        array('title' => 'Modern Web Design', 'author' => 'David Brown', 'pages' => '312', 'size' => '42 MB', 'image' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=400&h=500&fit=crop', 'category' => 'popular'),
                        array('title' => 'Financial Freedom Blueprint', 'author' => 'Amanda Davis', 'pages' => '178', 'size' => '11 MB', 'image' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=400&h=500&fit=crop', 'category' => 'categories'),
                        array('title' => 'Healthy Living Guide', 'author' => 'Rachel Green', 'pages' => '224', 'size' => '18 MB', 'image' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=400&h=500&fit=crop', 'category' => 'recent'),
                        array('title' => 'Creative Writing Essentials', 'author' => 'Lisa Anderson', 'pages' => '189', 'size' => '9 MB', 'image' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=400&h=500&fit=crop', 'category' => 'popular'),
                        array('title' => 'Travel Photography Tips', 'author' => 'Monica Taylor', 'pages' => '267', 'size' => '35 MB', 'image' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=400&h=500&fit=crop', 'category' => 'categories'),
                        array('title' => 'Coding for Beginners', 'author' => 'Sophie Brown', 'pages' => '342', 'size' => '21 MB', 'image' => 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=400&h=500&fit=crop', 'category' => 'recent'),
                        array('title' => 'Entrepreneurship 101', 'author' => 'Emma Wilson', 'pages' => '298', 'size' => '16 MB', 'image' => 'https://images.unsplash.com/photo-1506880018603-83d5b814b5a6?w=400&h=500&fit=crop', 'category' => 'popular'),
                        array('title' => 'Yoga & Meditation Guide', 'author' => 'Olivia Moore', 'pages' => '156', 'size' => '14 MB', 'image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&h=500&fit=crop', 'category' => 'categories'),
                        array('title' => 'Social Media Marketing', 'author' => 'Isabella Martin', 'pages' => '234', 'size' => '19 MB', 'image' => 'https://images.unsplash.com/photo-1522199755839-a2bacb67c546?w=400&h=500&fit=crop', 'category' => 'recent'),
                    );
                    
                    foreach ($pdf_posts as $pdf): ?>
                    <div class="nymia-pdf-card" data-category="<?php echo esc_attr($pdf['category']); ?>">
                        <div class="nymia-pdf-cover">
                            <img src="<?php echo esc_url($pdf['image']); ?>" alt="<?php echo esc_attr($pdf['title']); ?>" />
                            
                            <!-- PDF Badge -->
                            <div class="nymia-pdf-badge">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                                <span>PDF</span>
                            </div>
                            
                            <!-- Read Button -->
                            <div class="nymia-pdf-read-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                </svg>
                                <span>Read Now</span>
                            </div>
                            
                            <!-- Pages & Size Info -->
                            <div class="nymia-pdf-info-overlay">
                                <div class="nymia-pdf-pages">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                    <span><?php echo esc_html($pdf['pages']); ?> pages</span>
                                </div>
                                <div class="nymia-pdf-size">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    <span><?php echo esc_html($pdf['size']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="nymia-pdf-details">
                            <h3 class="nymia-pdf-title"><?php echo esc_html($pdf['title']); ?></h3>
                            <p class="nymia-pdf-author">by <?php echo esc_html($pdf['author']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Trending Sidebar -->
                <aside class="nymia-pdf-sidebar">
                    <h3 class="nymia-pdf-sidebar-title">📚 Popular PDFs</h3>
                    <div class="nymia-pdf-sidebar-list">
                        <?php 
                        $popular_pdfs = array(
                            array('title' => 'Complete SEO Guide', 'author' => 'John Doe', 'downloads' => '12.5k'),
                            array('title' => 'Python Programming', 'author' => 'Jane Smith', 'downloads' => '10.2k'),
                            array('title' => 'UI/UX Design Principles', 'author' => 'Alex Turner', 'downloads' => '9.8k'),
                            array('title' => 'Investment Strategies', 'author' => 'Mark Wilson', 'downloads' => '8.5k'),
                            array('title' => 'Content Marketing', 'author' => 'Lisa Brown', 'downloads' => '7.9k'),
                        );
                        
                        $rank = 1;
                        foreach ($popular_pdfs as $popular): ?>
                        <div class="nymia-pdf-sidebar-item">
                            <div class="nymia-pdf-rank"><?php echo $rank; ?></div>
                            <div class="nymia-pdf-sidebar-info">
                                <h4 class="nymia-pdf-sidebar-name"><?php echo esc_html($popular['title']); ?></h4>
                                <p class="nymia-pdf-sidebar-author"><?php echo esc_html($popular['author']); ?></p>
                                <span class="nymia-pdf-downloads">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    <?php echo esc_html($popular['downloads']); ?> downloads
                                </span>
                            </div>
                        </div>
                        <?php 
                        $rank++;
                        endforeach; ?>
                    </div>
                </aside>
            </div>
        </div>
    </main>
</div>

<?php get_footer(); ?>

