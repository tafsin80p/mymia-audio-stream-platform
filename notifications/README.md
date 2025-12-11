# Nymia Notifications System

This folder contains all notification-related functionality separated from the main theme files.

## Structure

```
notifications/
├── includes/
│   └── notification-functions.php  (Backend PHP functions)
├── js/
│   └── notifications.js            (Frontend JavaScript)
├── css/
│   └── notifications.css           (Notification styles)
├── templates/
│   └── notification-panel.php      (Notification UI)
└── README.md                       (This file)
```

## Setup Instructions

### 1. Update functions.php
Add this line at the end of `functions.php` (after auth includes):
```php
require_once get_template_directory() . '/notifications/includes/notification-functions.php';
```

### 2. Update nymia_scripts() function
In `functions.php`, locate the `nymia_scripts()` function and add:
```php
// Enqueue Notification CSS
wp_enqueue_style('nymia-notifications-css', get_template_directory_uri() . '/notifications/css/notifications.css', array('nymia-style'), '1.0');

// Enqueue Notification JavaScript  
wp_enqueue_script('nymia-notifications-js', get_template_directory_uri() . '/notifications/js/notifications.js', array('jquery'), '1.0', true);
```

### 3. Update template-parts/header.php
Replace the notification wrapper div with:
```php
<?php get_template_part('notifications/templates/notification-panel'); ?>
```

### 4. Remove old notification code
From `functions.php`, remove lines 2012-2203 (the old notification functions).

From `js/main.js`, remove the `nymiaNotifications` object (lines ~1012-1236).

## Files Included

### notification-functions.php
- `nymia_create_notification()` - Creates new notifications
- `nymia_get_notifications_count_handler()` - AJAX handler for count
- `nymia_get_notifications_handler()` - AJAX handler for list
- `nymia_mark_notification_read_handler()` - AJAX handler for marking read
- `nymia_mark_all_notifications_read_handler()` - AJAX handler for marking all read

### notifications.js
- `nymiaNotifications` object with all UI interactions
- Toggle dropdown
- Load count and notifications via AJAX
- Render notification list
- Mark as read functionality

### notifications.css
- All notification styling
- Responsive design
- Dark theme support

### notification-panel.php
- HTML structure for notification dropdown
- Includes all necessary IDs for JavaScript

## Usage

Notifications work exactly as before. No changes to existing functionality.

## Testing

1. Check that notification count loads
2. Open notification dropdown
3. Verify notifications display
4. Test marking as read
5. Verify all responsive breakpoints

