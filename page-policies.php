<?php
/**
 * Template Name: Policies
 * Description: Policies and terms page template
 */

get_header();
?>

<div class="nymia-container">
    <?php get_sidebar(); ?>
    
    <div class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/filters'); ?>
        
        <div class="nymia-policies-container">
            <!-- Policies Header -->
            <div class="nymia-policies-header">
                <div class="nymia-policies-header-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h1><?php esc_html_e('Policies & Terms', 'nymia'); ?></h1>
                <p class="nymia-policies-subtitle"><?php esc_html_e('Your privacy and trust matter to us. Please read our policies carefully.', 'nymia'); ?></p>
                <p class="nymia-policies-date"><?php esc_html_e('Last updated: January 15, 2024', 'nymia'); ?></p>
            </div>

            <!-- Policies Layout with Sidebar -->
            <div class="nymia-policies-layout">
                <!-- Left Sidebar Navigation -->
                <aside class="nymia-policies-sidebar">
                    <div class="nymia-policies-sidebar-sticky">
                        <a href="#privacy" class="nymia-policy-tab-item active" data-tab="privacy">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <span><?php esc_html_e('Privacy Policy', 'nymia'); ?></span>
                        </a>
                        <a href="#terms" class="nymia-policy-tab-item" data-tab="terms">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14,2 14,8 20,8"></polyline>
                            </svg>
                            <span><?php esc_html_e('Terms of Service', 'nymia'); ?></span>
                        </a>
                        <a href="#cookies" class="nymia-policy-tab-item" data-tab="cookies">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 16v-4"></path>
                                <path d="M12 8h.01"></path>
                            </svg>
                            <span><?php esc_html_e('Cookie Policy', 'nymia'); ?></span>
                        </a>
                        <a href="#community" class="nymia-policy-tab-item" data-tab="community">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span><?php esc_html_e('Community Guidelines', 'nymia'); ?></span>
                        </a>
                    </div>
                </aside>

                <!-- Right Content Area -->
                <div class="nymia-policies-content">
                
                <!-- Privacy Policy -->
                <section id="privacy" class="nymia-policy-section">
                    <div class="nymia-policy-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <h2><?php esc_html_e('Privacy Policy', 'nymia'); ?></h2>
                    
                    <div class="nymia-policy-text">
                        <h3><?php esc_html_e('1. Information We Collect', 'nymia'); ?></h3>
                        <p>We collect information you provide directly to us, such as when you create an account, update your profile, upload content, or communicate with us. This includes your name, email address, username, and any other information you choose to provide.</p>

                        <h3><?php esc_html_e('2. How We Use Your Information', 'nymia'); ?></h3>
                        <p>We use the information we collect to provide, maintain, and improve our services, to develop new services, and to protect Nymia and our users. We also use this information to offer you tailored content and communications.</p>

                        <h3><?php esc_html_e('3. Information Sharing', 'nymia'); ?></h3>
                        <p>We do not share your personal information with companies, organizations, or individuals outside of Nymia except in the following cases: with your consent, for legal reasons, or to protect rights and safety.</p>

                        <h3><?php esc_html_e('4. Data Security', 'nymia'); ?></h3>
                        <p>We work hard to protect Nymia and our users from unauthorized access to or unauthorized alteration, disclosure, or destruction of information we hold. We encrypt many of our services using SSL and review our information collection practices regularly.</p>

                        <h3><?php esc_html_e('5. Your Rights', 'nymia'); ?></h3>
                        <p>You have the right to access, update, or delete your personal information at any time. You can do this by logging into your account settings or by contacting us directly.</p>
                    </div>
                </section>

                <!-- Terms of Service -->
                <section id="terms" class="nymia-policy-section">
                    <div class="nymia-policy-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14,2 14,8 20,8"></polyline>
                        </svg>
                    </div>
                    <h2><?php esc_html_e('Terms of Service', 'nymia'); ?></h2>
                    
                    <div class="nymia-policy-text">
                        <h3><?php esc_html_e('1. Acceptance of Terms', 'nymia'); ?></h3>
                        <p>By accessing and using Nymia, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to these terms, please do not use our services.</p>

                        <h3><?php esc_html_e('2. User Accounts', 'nymia'); ?></h3>
                        <p>You are responsible for maintaining the confidentiality of your account and password. You agree to accept responsibility for all activities that occur under your account or password.</p>

                        <h3><?php esc_html_e('3. Content Guidelines', 'nymia'); ?></h3>
                        <p>You are responsible for the content you upload to Nymia. You must not upload content that is illegal, offensive, or infringes on the rights of others. We reserve the right to remove any content that violates these terms.</p>

                        <h3><?php esc_html_e('4. Intellectual Property', 'nymia'); ?></h3>
                        <p>All content on Nymia, including text, graphics, logos, and software, is the property of Nymia or its content suppliers and is protected by copyright laws.</p>

                        <h3><?php esc_html_e('5. Termination', 'nymia'); ?></h3>
                        <p>We may terminate or suspend your account and access to our services immediately, without prior notice or liability, for any reason, including if you breach the Terms of Service.</p>
                    </div>
                </section>

                <!-- Cookie Policy -->
                <section id="cookies" class="nymia-policy-section">
                    <div class="nymia-policy-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 16v-4"></path>
                            <path d="M12 8h.01"></path>
                        </svg>
                    </div>
                    <h2><?php esc_html_e('Cookie Policy', 'nymia'); ?></h2>
                    
                    <div class="nymia-policy-text">
                        <h3><?php esc_html_e('1. What Are Cookies', 'nymia'); ?></h3>
                        <p>Cookies are small text files that are placed on your computer or mobile device when you visit our website. They help us provide you with a better experience and allow certain features to work.</p>

                        <h3><?php esc_html_e('2. How We Use Cookies', 'nymia'); ?></h3>
                        <p>We use cookies for authentication, preferences, analytics, and advertising. Essential cookies are necessary for the website to function, while optional cookies enhance your experience.</p>

                        <h3><?php esc_html_e('3. Types of Cookies We Use', 'nymia'); ?></h3>
                        <p><strong>Essential Cookies:</strong> Required for the website to function properly.<br>
                        <strong>Analytics Cookies:</strong> Help us understand how visitors use our website.<br>
                        <strong>Preference Cookies:</strong> Remember your settings and preferences.<br>
                        <strong>Marketing Cookies:</strong> Track your online activity to help advertisers deliver more relevant content.</p>

                        <h3><?php esc_html_e('4. Managing Cookies', 'nymia'); ?></h3>
                        <p>Most web browsers allow you to control cookies through their settings. However, if you limit the ability of websites to set cookies, you may impact your overall user experience.</p>
                    </div>
                </section>

                <!-- Community Guidelines -->
                <section id="community" class="nymia-policy-section">
                    <div class="nymia-policy-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h2><?php esc_html_e('Community Guidelines', 'nymia'); ?></h2>
                    
                    <div class="nymia-policy-text">
                        <h3><?php esc_html_e('1. Be Respectful', 'nymia'); ?></h3>
                        <p>Treat everyone with respect. No harassment, bullying, or hate speech. We believe in creating a safe and welcoming environment for all users.</p>

                        <h3><?php esc_html_e('2. Share Authentic Content', 'nymia'); ?></h3>
                        <p>Only share content that you have created or have the right to share. Respect intellectual property rights and give credit where it's due.</p>

                        <h3><?php esc_html_e('3. Protect Privacy', 'nymia'); ?></h3>
                        <p>Don't share private information about yourself or others without permission. This includes phone numbers, addresses, and other personal details.</p>

                        <h3><?php esc_html_e('4. Report Violations', 'nymia'); ?></h3>
                        <p>If you see content that violates our guidelines, please report it. We review all reports and take appropriate action to maintain a positive community.</p>

                        <h3><?php esc_html_e('5. Have Fun!', 'nymia'); ?></h3>
                        <p>Nymia is a place to discover, create, and share amazing audio content. Engage with the community, support creators, and enjoy the experience!</p>
                    </div>
                </section>

                </div>
            </div>

            <!-- Contact Section -->
            <div class="nymia-policies-contact">
                <div class="nymia-policies-contact-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </div>
                <h3><?php esc_html_e('Questions About Our Policies?', 'nymia'); ?></h3>
                <p><?php esc_html_e('If you have any questions about these policies, please contact us at:', 'nymia'); ?></p>
                <a href="mailto:support@nymia.com" class="nymia-contact-email">support@nymia.com</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.nymia-policy-tab-item');
    const sections = document.querySelectorAll('.nymia-policy-section');
    
    // Handle tab click
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Scroll to section
            const targetId = this.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);
            
            if (targetSection) {
                const offset = 100;
                const elementPosition = targetSection.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - offset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Update active tab on scroll
    let ticking = false;
    
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                updateActiveTab();
                ticking = false;
            });
            ticking = true;
        }
    });
    
    function updateActiveTab() {
        let currentSection = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 150;
            const sectionBottom = sectionTop + section.offsetHeight;
            
            if (window.pageYOffset >= sectionTop && window.pageYOffset < sectionBottom) {
                currentSection = section.getAttribute('id');
            }
        });
        
        if (currentSection) {
            tabs.forEach(tab => {
                tab.classList.remove('active');
                if (tab.getAttribute('href') === '#' + currentSection) {
                    tab.classList.add('active');
                }
            });
        }
    }
    
    // Initial check
    updateActiveTab();
});
</script>

<?php get_footer(); ?>

