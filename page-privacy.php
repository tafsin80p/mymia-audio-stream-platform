<?php
/**
 * Template Name: Privacy Policy
 * Description: Privacy Policy page template
 */

get_header();
?>

<div class="nymia-container">
    <?php get_sidebar(); ?>
    
    <div class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/filters'); ?>
        
        <div class="nymia-privacy-container">
            <!-- Privacy Header -->
            <div class="nymia-privacy-header">
                <div class="nymia-privacy-header-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <h1><?php esc_html_e('Privacy Policy', 'nymia'); ?></h1>
                <p class="nymia-privacy-subtitle"><?php esc_html_e('Your privacy and trust matter to us. Please read our privacy policy carefully.', 'nymia'); ?></p>
                <p class="nymia-privacy-date"><?php esc_html_e('Last updated: November 20, 2025', 'nymia'); ?></p>
            </div>

            <!-- Privacy Content -->
            <div class="nymia-privacy-content">
                
                <section class="nymia-privacy-section">
                    <div class="nymia-privacy-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h2><?php esc_html_e('1. Information We Collect', 'nymia'); ?></h2>
                    
                    <div class="nymia-privacy-text">
                        <p><?php esc_html_e('We collect information you provide directly to us, such as when you:', 'nymia'); ?></p>
                        <ul>
                            <li><?php esc_html_e('Create an account or update your profile', 'nymia'); ?></li>
                            <li><?php esc_html_e('Upload content (audio files, ebooks, posts)', 'nymia'); ?></li>
                            <li><?php esc_html_e('Make purchases or transactions', 'nymia'); ?></li>
                            <li><?php esc_html_e('Communicate with us or other users', 'nymia'); ?></li>
                            <li><?php esc_html_e('Participate in surveys or promotions', 'nymia'); ?></li>
                        </ul>
                        
                        <p><strong><?php esc_html_e('Personal Information:', 'nymia'); ?></strong> <?php esc_html_e('Name, email address, username, phone number, date of birth, and profile picture.', 'nymia'); ?></p>
                        <p><strong><?php esc_html_e('Content Information:', 'nymia'); ?></strong> <?php esc_html_e('Audio files, ebooks, images, posts, comments, and other content you upload or create.', 'nymia'); ?></p>
                        <p><strong><?php esc_html_e('Payment Information:', 'nymia'); ?></strong> <?php esc_html_e('Payment method details, billing address, and transaction history (processed securely through third-party payment processors).', 'nymia'); ?></p>
                        <p><strong><?php esc_html_e('Usage Information:', 'nymia'); ?></strong> <?php esc_html_e('How you interact with our services, including pages visited, features used, and time spent.', 'nymia'); ?></p>
                    </div>
                </section>

                <section class="nymia-privacy-section">
                    <div class="nymia-privacy-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h2><?php esc_html_e('2. How We Use Your Information', 'nymia'); ?></h2>
                    
                    <div class="nymia-privacy-text">
                        <p><?php esc_html_e('We use the information we collect to:', 'nymia'); ?></p>
                        <ul>
                            <li><?php esc_html_e('Provide, maintain, and improve our services', 'nymia'); ?></li>
                            <li><?php esc_html_e('Process transactions and send related information', 'nymia'); ?></li>
                            <li><?php esc_html_e('Send technical notices, updates, and support messages', 'nymia'); ?></li>
                            <li><?php esc_html_e('Respond to your comments, questions, and requests', 'nymia'); ?></li>
                            <li><?php esc_html_e('Develop new features and services', 'nymia'); ?></li>
                            <li><?php esc_html_e('Monitor and analyze trends, usage, and activities', 'nymia'); ?></li>
                            <li><?php esc_html_e('Detect, prevent, and address technical issues', 'nymia'); ?></li>
                            <li><?php esc_html_e('Personalize your experience and show you relevant content', 'nymia'); ?></li>
                            <li><?php esc_html_e('Send you promotional communications (with your consent)', 'nymia'); ?></li>
                        </ul>
                    </div>
                </section>

                <section class="nymia-privacy-section">
                    <div class="nymia-privacy-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h2><?php esc_html_e('3. Information Sharing and Disclosure', 'nymia'); ?></h2>
                    
                    <div class="nymia-privacy-text">
                        <p><?php esc_html_e('We do not sell your personal information. We may share your information in the following circumstances:', 'nymia'); ?></p>
                        
                        <h3><?php esc_html_e('With Your Consent', 'nymia'); ?></h3>
                        <p><?php esc_html_e('We may share your information when you explicitly consent to such sharing.', 'nymia'); ?></p>
                        
                        <h3><?php esc_html_e('Service Providers', 'nymia'); ?></h3>
                        <p><?php esc_html_e('We may share information with third-party service providers who perform services on our behalf, such as payment processing, data analysis, email delivery, and hosting services.', 'nymia'); ?></p>
                        
                        <h3><?php esc_html_e('Legal Requirements', 'nymia'); ?></h3>
                        <p><?php esc_html_e('We may disclose your information if required by law or in response to valid requests by public authorities (e.g., court orders, government agencies).', 'nymia'); ?></p>
                        
                        <h3><?php esc_html_e('Protection of Rights', 'nymia'); ?></h3>
                        <p><?php esc_html_e('We may share information when we believe disclosure is necessary to protect our rights, protect your safety or the safety of others, investigate fraud, or respond to a government request.', 'nymia'); ?></p>
                        
                        <h3><?php esc_html_e('Business Transfers', 'nymia'); ?></h3>
                        <p><?php esc_html_e('In the event of a merger, acquisition, or sale of assets, your information may be transferred as part of that transaction.', 'nymia'); ?></p>
                    </div>
                </section>

                <section class="nymia-privacy-section">
                    <div class="nymia-privacy-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <h2><?php esc_html_e('4. Data Security', 'nymia'); ?></h2>
                    
                    <div class="nymia-privacy-text">
                        <p><?php esc_html_e('We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:', 'nymia'); ?></p>
                        <ul>
                            <li><?php esc_html_e('Encryption of data in transit using SSL/TLS', 'nymia'); ?></li>
                            <li><?php esc_html_e('Secure storage of sensitive information', 'nymia'); ?></li>
                            <li><?php esc_html_e('Regular security assessments and updates', 'nymia'); ?></li>
                            <li><?php esc_html_e('Access controls and authentication mechanisms', 'nymia'); ?></li>
                            <li><?php esc_html_e('Employee training on data protection', 'nymia'); ?></li>
                        </ul>
                        <p><?php esc_html_e('However, no method of transmission over the Internet or electronic storage is 100% secure. While we strive to use commercially acceptable means to protect your information, we cannot guarantee absolute security.', 'nymia'); ?></p>
                    </div>
                </section>

                <section class="nymia-privacy-section">
                    <div class="nymia-privacy-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            <path d="M9 12l2 2 4-4"></path>
                        </svg>
                    </div>
                    <h2><?php esc_html_e('5. Your Rights and Choices', 'nymia'); ?></h2>
                    
                    <div class="nymia-privacy-text">
                        <p><?php esc_html_e('You have the following rights regarding your personal information:', 'nymia'); ?></p>
                        
                        <h3><?php esc_html_e('Access and Portability', 'nymia'); ?></h3>
                        <p><?php esc_html_e('You can access and download a copy of your personal data through your account settings.', 'nymia'); ?></p>
                        
                        <h3><?php esc_html_e('Correction', 'nymia'); ?></h3>
                        <p><?php esc_html_e('You can update or correct your personal information at any time through your account settings.', 'nymia'); ?></p>
                        
                        <h3><?php esc_html_e('Deletion', 'nymia'); ?></h3>
                        <p><?php esc_html_e('You can request deletion of your account and personal information by contacting us or using the account deletion feature in settings.', 'nymia'); ?></p>
                        
                        <h3><?php esc_html_e('Opt-Out', 'nymia'); ?></h3>
                        <p><?php esc_html_e('You can opt-out of receiving promotional emails by clicking the unsubscribe link in any email or updating your preferences in account settings.', 'nymia'); ?></p>
                        
                        <h3><?php esc_html_e('Cookie Preferences', 'nymia'); ?></h3>
                        <p><?php esc_html_e('Most web browsers allow you to control cookies through their settings. You can adjust your browser settings to refuse cookies or alert you when cookies are being sent.', 'nymia'); ?></p>
                    </div>
                </section>

                <section class="nymia-privacy-section">
                    <div class="nymia-privacy-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <h2><?php esc_html_e('6. Data Retention', 'nymia'); ?></h2>
                    
                    <div class="nymia-privacy-text">
                        <p><?php esc_html_e('We retain your personal information for as long as necessary to provide our services and fulfill the purposes described in this policy. We may retain certain information:', 'nymia'); ?></p>
                        <ul>
                            <li><?php esc_html_e('As required by law or for legitimate business purposes', 'nymia'); ?></li>
                            <li><?php esc_html_e('To resolve disputes and enforce our agreements', 'nymia'); ?></li>
                            <li><?php esc_html_e('For safety and security purposes', 'nymia'); ?></li>
                        </ul>
                        <p><?php esc_html_e('When you delete your account, we will delete or anonymize your personal information, except where we are required to retain it by law.', 'nymia'); ?></p>
                    </div>
                </section>

                <section class="nymia-privacy-section">
                    <div class="nymia-privacy-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <h2><?php esc_html_e('7. Children\'s Privacy', 'nymia'); ?></h2>
                    
                    <div class="nymia-privacy-text">
                        <p><?php esc_html_e('Our services are not intended for children under the age of 13. We do not knowingly collect personal information from children under 13. If we become aware that we have collected personal information from a child under 13, we will take steps to delete such information promptly.', 'nymia'); ?></p>
                        <p><?php esc_html_e('If you are a parent or guardian and believe your child has provided us with personal information, please contact us immediately.', 'nymia'); ?></p>
                    </div>
                </section>

                <section class="nymia-privacy-section">
                    <div class="nymia-privacy-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            <path d="M12 8v4"></path>
                            <path d="M12 16h.01"></path>
                        </svg>
                    </div>
                    <h2><?php esc_html_e('8. Changes to This Privacy Policy', 'nymia'); ?></h2>
                    
                    <div class="nymia-privacy-text">
                        <p><?php esc_html_e('We may update this Privacy Policy from time to time. We will notify you of any material changes by:', 'nymia'); ?></p>
                        <ul>
                            <li><?php esc_html_e('Posting the new Privacy Policy on this page', 'nymia'); ?></li>
                            <li><?php esc_html_e('Updating the "Last updated" date at the top of this policy', 'nymia'); ?></li>
                            <li><?php esc_html_e('Sending you an email notification (for significant changes)', 'nymia'); ?></li>
                            <li><?php esc_html_e('Displaying a notice on our website', 'nymia'); ?></li>
                        </ul>
                        <p><?php esc_html_e('We encourage you to review this Privacy Policy periodically. Your continued use of our services after changes become effective constitutes your acceptance of the updated Privacy Policy.', 'nymia'); ?></p>
                    </div>
                </section>

            </div>

            <!-- Contact Section -->
            <div class="nymia-privacy-contact">
                <div class="nymia-privacy-contact-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </div>
                <h3><?php esc_html_e('Questions About Our Privacy Policy?', 'nymia'); ?></h3>
                <p><?php esc_html_e('If you have any questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us at:', 'nymia'); ?></p>
                <a href="mailto:support@nymia.com" class="nymia-contact-email">support@nymia.com</a>
            </div>
        </div>
    </div>
</div>

<style>
.nymia-privacy-container {
    padding: 40px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

/* Privacy Header */
.nymia-privacy-header {
    text-align: center;
    padding: 60px 20px 40px;
    margin-bottom: 40px;
}

.nymia-privacy-header-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 24px;
    background: linear-gradient(135deg, rgba(191, 76, 26, 0.2), rgba(159, 43, 26, 0.2));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #BF4C1A;
}

.nymia-privacy-header-icon svg {
    width: 40px;
    height: 40px;
}

.nymia-privacy-header h1 {
    font-size: 2.5rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 16px;
    letter-spacing: -0.02em;
}

.nymia-privacy-subtitle {
    font-size: 1.125rem;
    color: rgba(255, 255, 255, 0.8);
    margin: 0 0 12px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.nymia-privacy-date {
    font-size: 0.9375rem;
    color: rgba(255, 255, 255, 0.6);
    margin: 0;
}

/* Privacy Content */
.nymia-privacy-content {
    max-width: 900px;
    margin: 0 auto;
}

.nymia-privacy-section {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    transition: all 0.3s ease;
}

.nymia-privacy-section:hover {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(255, 255, 255, 0.1);
}

.nymia-privacy-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, rgba(191, 76, 26, 0.15), rgba(159, 43, 26, 0.15));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #BF4C1A;
    margin-bottom: 20px;
}

.nymia-privacy-icon svg {
    width: 24px;
    height: 24px;
}

.nymia-privacy-section h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #fff;
    margin: 0 0 20px;
}

.nymia-privacy-section h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #fff;
    margin: 24px 0 12px;
}

.nymia-privacy-text {
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.7;
    font-size: 1rem;
}

.nymia-privacy-text p {
    margin: 0 0 16px;
}

.nymia-privacy-text p:last-child {
    margin-bottom: 0;
}

.nymia-privacy-text ul {
    margin: 16px 0;
    padding-left: 24px;
}

.nymia-privacy-text li {
    margin-bottom: 10px;
    color: rgba(255, 255, 255, 0.85);
}

.nymia-privacy-text strong {
    color: #fff;
    font-weight: 600;
}

/* Contact Section */
.nymia-privacy-contact {
    text-align: center;
    padding: 60px 20px;
    margin-top: 60px;
    background: linear-gradient(135deg, rgba(191, 76, 26, 0.1), rgba(159, 43, 26, 0.1));
    border: 1px solid rgba(191, 76, 26, 0.2);
    border-radius: 20px;
}

.nymia-privacy-contact-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 24px;
    background: linear-gradient(135deg, rgba(191, 76, 26, 0.2), rgba(159, 43, 26, 0.2));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #BF4C1A;
}

.nymia-privacy-contact-icon svg {
    width: 32px;
    height: 32px;
}

.nymia-privacy-contact h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #fff;
    margin: 0 0 12px;
}

.nymia-privacy-contact p {
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.8);
    margin: 0 0 20px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.nymia-contact-email {
    display: inline-block;
    padding: 12px 32px;
    background: linear-gradient(135deg, #BF4C1A, #9F2B1A);
    color: #fff;
    text-decoration: none;
    border-radius: 24px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.nymia-contact-email:hover {
    background: linear-gradient(135deg, #D14619, #8B2A0F);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(191, 76, 26, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .nymia-privacy-container {
        padding: 20px 16px;
    }

    .nymia-privacy-header {
        padding: 40px 16px 32px;
    }

    .nymia-privacy-header h1 {
        font-size: 2rem;
    }

    .nymia-privacy-section {
        padding: 24px 20px;
    }

    .nymia-privacy-section h2 {
        font-size: 1.25rem;
    }

    .nymia-privacy-contact {
        padding: 40px 20px;
    }
}
</style>

<?php get_footer(); ?>

