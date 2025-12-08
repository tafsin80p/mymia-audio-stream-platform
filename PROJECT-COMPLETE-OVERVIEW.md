# Nymia WordPress Theme - Complete Project Overview

**Generated:** December 2024  
**Theme Version:** 3.8.0  
**WordPress Version Required:** 5.0+  
**PHP Version Required:** 7.4+

---

## 📋 Executive Summary

The **Nymia WordPress Theme** is a comprehensive audio content platform theme designed for creators to upload, share, and monetize audio content, ebooks, and live streaming sessions. It features a modern dark theme with orange accent colors, comprehensive admin dashboard, and extensive monetization capabilities.

### Key Highlights:
- ✅ **Modern Dark Theme** with orange accent colors (#BF4C1A, #C7541A)
- ✅ **Multi-feature Platform**: Audio, Ebooks, Live Streaming, Social Posts
- ✅ **Monetization**: Stripe integration for paid content and creator payouts
- ✅ **User Management**: Followers, chat, notifications, profiles
- ✅ **Admin Dashboard**: 13-page comprehensive admin panel
- ✅ **Public Dashboard**: Accessible to non-logged-in users
- ✅ **KYC System**: Creator verification with document upload
- ✅ **Translation Ready**: Loco Translate support (English & Italian)
- ⚠️ **Code Organization**: Very large core files (functions.php ~13,570+ lines)
- ⚠️ **Data Storage**: Uses transients for some critical data

---

## 🏗️ Project Structure

### Core Files
```
nymia-wp-theme/
├── style.css (14,392 lines - Main stylesheet)
├── functions.php (13,570 lines - Core functionality)
├── index.php (Main template/router)
├── header.php (HTML head)
├── footer.php (Footer + modals)
├── sidebar.php (Left navigation)
└── page.php (Generic page template)
```

### Page Templates (20+ templates)
- `page-login.php` - Authentication page
- `page-profile.php` - User profiles with edit functionality
- `page-audio.php` - Audio library
- `page-single-audio.php` - Audio player page
- `page-create.php` - Content creation hub (Live, Audio, Ebook, Text)
- `page-earnings.php` - Creator earnings dashboard
- `page-live-audio.php` - Live streaming page
- `page-ebook.php` - Ebook library
- `page-single-ebook.php` - Ebook reader
- `page-contact.php` - Contact form
- `page-policies.php` - Legal pages
- `page-privacy.php` - Privacy settings
- `page-settings.php` - User settings
- `page-verify-email.php` - Email verification
- `page-checkout.php` - Payment processing
- `page-payment-methods.php` - Payment methods management
- `page-reset-password.php` - Password reset
- `page-event-calendar.php` - Event calendar
- `page-live-streams.php` - Live streams listing
- `page-audiobook.php` - Audiobook library
- `page-single-post.php` - Social post display
- `page-pdf.php` - PDF library

### Template Parts (`template-parts/`)
- `header.php` - Main header with search and actions
- `dashboard.php` - Logged-in dashboard
- `dashboard-public.php` - Public dashboard
- `sidebar-right.php` - User suggestions sidebar
- `filters.php` - Content filter buttons
- `content-page.php` - Generic content template
- `content-none.php` - Empty state
- `back-button.php` - Navigation back button
- `login-modal.php` - Login/signup modal
- `become-creator-modal.php` - Creator application modal
- `chat-modal.php` - Messaging system
- `settings-modal.php` - Settings modal
- `profile-modal.php` - Profile edit modal
- `live-booking-modal.php` - Live session booking
- `notification-success.php` - Success notifications

### Feature Modules

#### Authentication (`auth/`)
- `auth-login.php` - Login form
- `auth-signup.php` - Signup form with KYC fields
- `auth-scripts.php` - Authentication JavaScript
- `auth-styles.php` - Authentication styles
- `auth-functions.php` - Authentication helpers

#### Admin Panel (`admin/`)
- `admin-menu.php` - Admin menu registration
- `dashboard-settings.php` - Dashboard overview
- `general-settings.php` - General settings
- `social-login-settings.php` - Social login config
- `stripe-settings.php` - Stripe payment config
- `zegocloud-settings.php` - ZEGO Cloud streaming config
- `footer-menu-settings.php` - Footer menu management
- `user-management.php` - User management
- `product-management.php` - Content management
- `financial-management.php` - Financial reports
- `payout-requests.php` - Payout management
- `moderation-control.php` - Content moderation
- `analytics-reporting.php` - Analytics
- `marketing-promotions.php` - Marketing tools
- `stream-settings.php` - Streaming settings

#### Followers System (`followers/`)
- `includes/followers-functions.php` - Follow/unfollow logic
- `js/followers.js` - Frontend JavaScript
- `css/followers.css` - Styling
- `templates/followers-popup.php` - Followers modal

#### Notifications (`notifications/`)
- `includes/notification-functions.php` - Notification logic
- `js/notifications.js` - Frontend JavaScript
- `css/notifications.css` - Styling
- `templates/notification-panel.php` - Notification dropdown
- `notification-sound/` - Sound files

#### Ebook System (`create-ebook/` & `ebook-archive/`)
- `create-ebook/includes/ebook-functions.php` - Ebook upload/management
- `create-ebook/js/ebook.js` - Ebook creation JavaScript
- `create-ebook/css/ebook.css` - Ebook styles
- `ebook-archive/includes/ebook-archive-functions.php` - Library functions
- `ebook-archive/js/ebook-library.js` - Library JavaScript

### Assets
- `js/main.js` (1,234+ lines) - Main JavaScript functionality
- `assets/images/` - Theme images and logos
- `languages/` - Translation files directory

---

## 🎯 Core Features

### 1. User Authentication & Management

#### Registration System
- **Account Types**: User (client) and Creator
- **KYC Verification**: Required for creators
  - ID Type selection
  - ID Number
  - Document upload (file or camera capture)
  - Camera access with fallback
- **Email Verification**: 6-digit code system
- **Password Strength**: Real-time indicator
- **Social Login UI**: Google, Facebook, Apple (UI ready)
- **Remember Me**: Persistent login sessions
- **Account Status**: Active, suspended, banned states

#### User Profiles
- **Profile Editing**: Avatar, cover image (banner), bio, social links
- **Stats Display**: Followers, following, posts, favorites
- **Creator Badge**: Visual indicator for verified creators
- **Email Status**: Verification status display
- **Profile URL**: `/profile/?username=username`
- **Privacy Settings**: Separate privacy configuration page
- **Banner Image**: 1500x500px (3:1 ratio) recommended

### 2. Audio Content System

#### Audio Upload
- **Supported Formats**: MP3, WAV, OGG, M4A, FLAC
- **Recording**: Web Audio API for browser recording
- **Cover Images**: Custom cover art upload
- **Metadata**: Title, category, subcategory, language
- **Pricing**: Paid/free access control with pricing
- **File Validation**: Type and format checking
- **Storage**: `/wp-content/uploads/nymia-audio/`
- **Edit Functionality**: Edit title, category, language, cover, price after upload

#### Audio Library
- **Grid Display**: Creator-focused grid layout
- **Filtering**: All, Trending, New Releases, Most Popular
- **Creator Profiles**: Top 3 audio files per creator
- **Access Control**: Paid content gating
- **Audio Player**: Full-featured player with controls

### 3. Ebook System

#### Ebook Upload
- **Supported Formats**: PDF, EPUB, MOBI, TXT
- **Thumbnail Upload**: Custom cover images
- **Metadata**: Title, description, category, subcategory, language
- **Pricing**: Paid/free access control
- **Storage**: `/wp-content/uploads/nymia-ebook/`
- **Edit Functionality**: Edit title, description, category, language, thumbnail, price after upload

#### Ebook Library
- **Grid Display**: Category-grouped layout
- **Filtering**: All, Recent, Popular, Categories
- **Reader**: PDF viewer with page navigation
- **Download**: PDF download functionality
- **Bookmarking**: Save for later

### 4. Live Streaming System

#### ZEGO Cloud Integration
- **Live Audio Streaming**: Real-time audio streaming
- **Room Management**: Create and manage streaming rooms
- **Scheduling**: Schedule future live sessions
- **Thumbnail Upload**: Custom stream thumbnails
- **One-to-One Sessions**: Private paid sessions
- **Booking System**: Calendar-based booking
- **Duration Tracking**: Track minutes for billing

### 5. Social Posts System

#### Text Posts
- **Post Creation**: Title, description, image upload
- **Likes**: Like/unlike functionality
- **Comments**: Comment system
- **Edit Functionality**: Edit posts after creation
- **Delete**: Post deletion
- **Post Type**: Custom post type `nymia_social_post`

### 6. Monetization System

#### Stripe Integration
- **Payment Processing**: Stripe Checkout integration
- **Paid Content**: Audio, ebooks, live sessions
- **Commission System**: Platform commission (configurable)
- **Creator Payouts**: Stripe Connect for payouts
- **Currency Support**: Multi-currency (USD, EUR, etc.)
- **Transaction History**: Complete transaction logs
- **Earnings Dashboard**: Revenue charts and breakdowns

#### Earnings Features
- **Total Earnings**: Lifetime earnings
- **Monthly Earnings**: Current month revenue
- **Pending Earnings**: Awaiting payout
- **Available Balance**: Ready to withdraw
- **Breakdown by Type**: Audio, Ebook, Live, Tips, Subscriptions
- **Sales Count**: Number of items sold
- **Average Price**: Average price per item
- **Minutes Sold**: Total minutes for live sessions
- **Chart Visualization**: Chart.js revenue graphs

### 7. User Interaction Features

#### Followers System
- **Follow/Unfollow**: Follow creators and users
- **Followers List**: View followers and following
- **Mutual Connections**: Smart suggestions based on connections
- **Follow Status**: Real-time follow status updates

#### Notifications System
- **Real-time Notifications**: AJAX-based notification system
- **Notification Types**: Follow, like, comment, purchase, etc.
- **Unread Count**: Badge showing unread count
- **Notification Panel**: Dropdown with all notifications
- **Sound Alerts**: Audio notification sounds
- **Auto-refresh**: Periodic notification updates

#### Chat System
- **Messaging**: Direct messaging between users
- **Conversations**: Conversation list
- **Online Status**: User online/offline status
- **Chat Modal**: Full-screen chat interface

### 8. Content Discovery

#### Suggestions System
- **Smart Algorithm**: Facebook-like suggestion algorithm
- **Mutual Connections**: Suggests based on mutual friends
- **Content Similarity**: Suggests based on liked content
- **Popular Creators**: Suggests active creators
- **Filter**: Only shows verified creators (KYC approved)
- **Public Suggestions**: Top-rated creators for non-logged-in users

#### Search System
- **Live Search**: Real-time search results
- **Username Search**: @username search redirects to profile
- **Content Search**: Search audio, ebooks, users
- **Search Dropdown**: Live search results dropdown

### 9. Admin Dashboard

#### Admin Pages (13 pages)
1. **Dashboard Overview**: Site statistics and metrics
2. **General Settings**: Theme configuration
3. **Social Login**: OAuth configuration
4. **ZEGO Cloud**: Streaming configuration
5. **Stripe Payments**: Payment configuration
6. **Footer Menu**: Footer navigation management
7. **User Management**: User administration
8. **Product Management**: Content management
9. **Financial Management**: Financial reports
10. **Payout Requests**: Payout approval
11. **Moderation Control**: Content moderation
12. **Analytics Reporting**: Site analytics
13. **Marketing Promotions**: Marketing tools

### 10. Translation System

#### Loco Translate Integration
- **Text Domain**: `nymia`
- **Domain Path**: `/languages`
- **Supported Languages**: English (en_US), Italian (it_IT)
- **Language Switcher**: Cookie-based language switching
- **Locale Filter**: Automatic locale detection
- **Translation Files**: `.po` and `.mo` files in `/languages/`

---

## 🔧 Technical Implementation

### Data Storage

#### Transients (WordPress Transients API)
- `nymia_user_audio_{user_id}` - User's audio posts
- `nymia_all_audio` - All audio posts
- `nymia_user_ebook_{user_id}` - User's ebook posts
- `nymia_all_ebooks` - All ebook posts
- `nymia_contact_form_logs` - Contact form submissions (last 100)

#### User Meta
- `nymia_following` - List of followed user IDs
- `nymia_followers` - List of follower user IDs
- `nymia_notifications` - User notifications array
- `nymia_creator_kyc_status` - KYC status (pending, approved, rejected)
- `nymia_creator_kyc_data` - KYC form data
- `nymia_creator_kyc_photo` - KYC document photo
- `custom_avatar` - User avatar URL
- `cover_image` - User cover/banner image URL
- `account_type` - Account type (user, creator)
- `email_verified` - Email verification status
- `verification_code` - Email verification code

#### Post Meta
- `_nymia_audio_*` - Audio post metadata
- `_nymia_ebook_*` - Ebook post metadata
- `_nymia_liked_posts` - User's liked posts

#### Options
- `nymia_stripe_*` - Stripe configuration
- `nymia_zego_*` - ZEGO Cloud configuration
- `nymia_contact_*` - Contact information
- `nymia_footer_*` - Footer menu configuration
- `nymia_creator_pending_requests` - Pending KYC requests

### AJAX Endpoints (77+ handlers)

#### Authentication
- `nymia_user_register` - User registration
- `nymia_user_login` - User login
- `nymia_verify_email` - Email verification
- `nymia_resend_verification` - Resend verification code
- `nymia_reset_password` - Password reset

#### Content Management
- `nymia_upload_audio` - Audio upload
- `nymia_edit_audio` - Edit audio post
- `nymia_get_single_audio` - Get audio data
- `nymia_upload_ebook` - Ebook upload
- `nymia_edit_ebook` - Edit ebook post
- `nymia_get_single_ebook` - Get ebook data
- `nymia_submit_social_post` - Create text post
- `nymia_edit_text_post` - Edit text post
- `nymia_delete_social_post` - Delete post
- `nymia_fetch_social_posts` - Get social posts

#### User Interaction
- `nymia_toggle_follow` - Follow/unfollow user
- `nymia_get_followers` - Get followers list
- `nymia_get_following` - Get following list
- `nymia_toggle_post_like` - Like/unlike post
- `nymia_submit_post_comment` - Add comment
- `nymia_get_post_comments` - Get comments
- `nymia_toggle_comment_like` - Like/unlike comment

#### Notifications
- `nymia_get_notifications` - Get notifications
- `nymia_get_notifications_count` - Get unread count
- `nymia_mark_notification_read` - Mark as read
- `nymia_mark_all_notifications_read` - Mark all as read

#### Chat
- `nymia_get_chat_conversations` - Get conversations
- `nymia_get_chat_messages` - Get messages
- `nymia_send_chat_message` - Send message

#### Earnings & Payments
- `nymia_get_creator_transactions` - Get transactions
- `nymia_get_creator_earnings_chart` - Get chart data
- `nymia_create_checkout_session` - Create Stripe checkout
- `nymia_process_payment` - Process payment
- `nymia_request_payout` - Request payout

#### Live Streaming
- `nymia_create_live_stream` - Create stream
- `nymia_schedule_live_stream` - Schedule stream
- `nymia_delete_stream_schedule` - Delete schedule
- `nymia_book_private_session` - Book session

#### Profile
- `nymia_update_profile` - Update profile
- `nymia_remove_cover_image` - Remove banner

#### Contact
- `nymia_send_contact_message` - Send contact form

#### Admin
- `nymia_admin_review_kyc` - Review KYC requests
- `nymia_ajax_add_category` - Add category
- `nymia_ajax_delete_category` - Delete category
- `nymia_ajax_add_ebook_language` - Add ebook language
- `nymia_ajax_delete_ebook_language` - Delete ebook language

### Security Features

#### Input Sanitization
- `sanitize_text_field()` - Text fields
- `sanitize_email()` - Email addresses
- `sanitize_textarea_field()` - Textarea fields
- `sanitize_user()` - Usernames
- `wp_kses_post()` - HTML content
- `intval()` - Integer values
- `floatval()` - Float values

#### Output Escaping
- `esc_html()` - HTML content
- `esc_url()` - URLs
- `esc_attr()` - HTML attributes
- `esc_js()` - JavaScript strings

#### Nonce Verification
- All AJAX handlers use `check_ajax_referer()`
- Form submissions use `wp_nonce_field()`
- Nonce names: `nymia_audio_upload`, `nymia_upload_ebook`, `nymia_follow_action`, etc.

#### Access Control
- `is_user_logged_in()` - Login check
- `current_user_can()` - Capability checks
- `user_can()` - User capability checks
- Role-based access control

### JavaScript Functionality

#### Main Features (`js/main.js`)
- Mobile menu toggle
- Sidebar navigation
- Audio player controls
- Filter functionality
- Live search
- Follow/unfollow
- Modal management
- Form validation
- Camera access (KYC)

#### Feature-Specific JS
- `followers/js/followers.js` - Followers system
- `notifications/js/notifications.js` - Notifications
- `create-ebook/js/ebook.js` - Ebook creation
- `ebook-archive/js/ebook-library.js` - Ebook library
- `admin/js/admin-nav.js` - Admin navigation
- `admin/js/dashboard-overview.js` - Admin dashboard

---

## 📊 Code Statistics

- **Total PHP Files**: 74 files
- **Total JavaScript Files**: 9 files
- **Total CSS Files**: 6 files
- **Total Lines of Code**: ~35,000+ lines
- **Functions in functions.php**: 273+ functions
- **AJAX Handlers**: 77+ endpoints
- **Page Templates**: 20+ templates
- **Template Parts**: 15+ components
- **Custom Post Types**: 2 (`nymia_social_post`, `nymia_payout_request`)
- **Admin Pages**: 13 pages
- **WordPress Hooks**: 145+ hooks (add_action, add_filter)

---

## 🔐 Security Implementation

### Authentication Security
- ✅ Nonce verification on all forms
- ✅ CSRF protection
- ✅ Input sanitization
- ✅ Output escaping
- ✅ SQL injection prevention (WordPress functions)
- ✅ XSS prevention
- ✅ File upload validation
- ✅ User capability checks
- ✅ Email verification system

### Payment Security
- ✅ Stripe API key validation
- ✅ Payment amount validation
- ✅ Transaction logging
- ✅ Secure checkout sessions
- ✅ Metadata validation

---

## 🌐 Internationalization

### Translation Support
- **Text Domain**: `nymia`
- **Domain Path**: `/languages`
- **Translation Functions**: All strings use `__()`, `esc_html_e()`, `esc_attr_e()`
- **Loco Translate**: Fully compatible
- **Supported Languages**: English (en_US), Italian (it_IT)
- **Language Switcher**: Cookie-based switching

---

## 🎨 Design System

### Color Scheme
- **Background**: `hsl(0, 0%, 7%)` - Dark background
- **Foreground**: `hsl(0, 0%, 98%)` - White text
- **Primary**: `hsl(18, 75%, 58%)` - Orange accent (#BF4C1A)
- **Card**: `hsl(0, 0%, 11%)` - Card background
- **Border**: `rgba(255, 255, 255, 0.1)` - Subtle borders

### Typography
- **Font Family**: System fonts (sans-serif)
- **Font Sizes**: Responsive scaling
- **Font Weights**: 400 (normal), 500 (medium), 600 (semibold), 700 (bold)

### Components
- **Buttons**: Gradient buttons, outline buttons
- **Cards**: Content cards, suggestion cards
- **Modals**: Full-screen modals with overlay
- **Forms**: Styled form inputs and selects
- **Navigation**: Sidebar navigation, header actions

---

## 📱 Responsive Design

### Breakpoints
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

### Mobile Features
- Mobile menu toggle
- Responsive grid layouts
- Touch-friendly buttons
- Mobile-optimized forms
- Hidden elements on mobile (language selector)

---

## 🔄 Recent Updates & Fixes

### Implemented Features
1. ✅ **KYC System**: Creator verification with document upload and camera capture
2. ✅ **Banner Image**: Profile banner upload and removal
3. ✅ **Earnings Breakdown**: Detailed earnings with sales count and minutes
4. ✅ **Edit Functionality**: Edit audio and ebook posts after creation
5. ✅ **Contact Form**: Email sending with user confirmation
6. ✅ **Suggestions Filter**: Only show verified creators
7. ✅ **Admin Bar**: Hidden for all users on frontend
8. ✅ **Loco Translate**: Full translation support

### Known Issues
- ⚠️ Large `functions.php` file (could be split)
- ⚠️ Some data stored in transients (consider database)
- ⚠️ Social login backend pending (UI ready)

---

## 🚀 Performance Considerations

### Optimization
- Transients for caching
- AJAX for dynamic content
- Lazy loading for images
- Minified assets (if applicable)
- Efficient database queries

### Areas for Improvement
- Split large files into modules
- Implement proper caching
- Optimize image loading
- Database indexing for user meta

---

## 📝 Development Notes

### Code Organization
- Functions grouped by feature
- Clear function naming conventions
- Comprehensive comments
- Security best practices
- WordPress coding standards

### Dependencies
- WordPress 5.0+
- PHP 7.4+
- jQuery (WordPress bundled)
- Chart.js (for earnings charts)
- ZEGO Cloud SDK (for live streaming)
- Stripe PHP SDK (for payments)

---

## 🎯 Future Enhancements

### Potential Improvements
1. Split `functions.php` into modules
2. Implement proper database tables for content
3. Add more social login backends
4. Implement advanced analytics
5. Add more payment gateways
6. Enhance mobile app support
7. Add video content support
8. Implement advanced search

---

**Last Updated**: December 2024  
**Theme Version**: 3.8.0  
**Status**: Production Ready

