# Nymia Followers System

This folder contains all followers/following functionality separated from the main theme files.

## Structure

```
followers/
├── includes/
│   └── followers-functions.php  (Backend PHP functions & AJAX handlers)
├── js/
│   └── followers.js             (Frontend JavaScript with popup)
├── css/
│   └── followers.css            (Followers popup styles)
├── templates/
│   └── followers-popup.php      (Popup modal template)
└── README.md                    (This file)
```

## Features

### ✅ Follow/Unfollow Functionality
- Toggle follow status with a single click
- Real-time button state updates
- Secure AJAX requests with nonce verification

### ✅ Followers Popup Modal
- Beautiful custom modal design
- Shows list of followers or following users
- Click on any user to visit their profile
- Remove followers or unfollow users (if viewing own profile)
- Smooth animations and transitions

### ✅ User List Actions
- **View Followers**: Click on follower count to see all followers
- **View Following**: Click on following count to see who you're following
- **Unfollow**: Unfollow users from the following list
- **Remove Follower**: Remove users from your followers list

## How It Works

### Backend Functions (followers-functions.php)
- `nymia_is_following()` - Check if user A is following user B
- `nymia_get_followers_count()` - Get total follower count
- `nymia_get_following_count()` - Get total following count
- `nymia_toggle_follow_handler()` - AJAX handler for follow/unfollow
- `nymia_get_user_list_handler()` - AJAX handler to fetch followers/following list
- `nymia_remove_follower_handler()` - AJAX handler to remove a follower

### Frontend JavaScript (followers.js)
- `nymiaToggleFollow(button, user_id)` - Handle follow/unfollow button
- `nymiaShowUserList(userId, type, isOwnProfile)` - Open popup modal
- `nymiaCloseUserList()` - Close popup modal
- `nymiaUnfollowUser(userId, button)` - Unfollow from list
- `nymiaRemoveFollower(userId, button)` - Remove follower from list

### Usage Example

```php
// In your template, display follower counts:
$followers_count = nymia_get_followers_count($user_id);
$following_count = nymia_get_following_count($user_id);
```

```html
<!-- Clickable stats that open popup -->
<span onclick="nymiaShowUserList(<?php echo $user_id; ?>, 'followers', true)">
    <strong><?php echo $followers_count; ?></strong> Followers
</span>

<span onclick="nymiaShowUserList(<?php echo $user_id; ?>, 'following', true)">
    <strong><?php echo $following_count; ?></strong> Following
</span>
```

```html
<!-- Follow button -->
<button onclick="nymiaToggleFollow(this, <?php echo $user_id; ?>)">
    <?php echo nymia_is_following(get_current_user_id(), $user_id) ? 'Following' : 'Follow'; ?>
</button>
```

## Styling

The popup modal uses the following CSS classes:
- `.nymia-followers-modal` - Main modal overlay
- `.nymia-followers-modal-content` - Modal container
- `.nymia-followers-item-wrapper` - Individual user item
- `.nymia-followers-item` - User info (avatar + name)
- `.nymia-user-list-action-btn` - Action buttons (unfollow/remove)

## Security

- All AJAX requests use nonce verification
- User authentication checked on every action
- Input validation and sanitization
- User can only manage their own followers list

## Notes

- Follower data is stored in user meta as `nymia_followers` and `nymia_following`
- Maximum 100 users are shown in the popup
- Page refreshes after unfollow/remove actions
- Modal closes when clicking outside or on close button

