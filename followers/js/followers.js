/**
 * ========================================
 * NYMIA FOLLOWERS SYSTEM - JAVASCRIPT
 * ========================================
 * Handles all followers/following UI interactions including popup modal
 * 
 * @package Nymia
 * @version 1.0
 */

// ==========================================
// FOLLOW/UNFOLLOW FUNCTIONALITY
// ==========================================
/**
 * TOGGLE FOLLOW STATUS
 * Handles follow/unfollow button clicks
 */
function nymiaToggleFollow(button, user_id) {
    // Prevent default action
    if (typeof event !== 'undefined') {
        event.stopPropagation();
    }

    // Get current state
    const currentText = button.textContent.trim();
    const isFollowing = currentText === 'Following';

    // Check if nymiaAjax is defined
    if (typeof nymiaAjax === 'undefined' || !nymiaAjax.followNonce) {
        alert('Configuration error. Please refresh the page.');
        return;
    }

    // Disable button during request
    button.disabled = true;
    button.textContent = isFollowing ? 'Unfollowing...' : 'Following...';

    // Prepare data for AJAX request
    const formData = new FormData();
    formData.append('action', 'nymia_toggle_follow');
    formData.append('user_id', user_id);
    formData.append('nonce', nymiaAjax.followNonce);

    // Send AJAX request
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
            // Enable button
            button.disabled = false;

            // Check if request was successful
            if (data.success) {
                // Update button text and state
                if (data.data.is_following) {
                    button.textContent = 'Following';
                    button.classList.add('following');
                } else {
                    button.textContent = 'Follow';
                    button.classList.remove('following');
                }
            } else {
                // Reset button to previous state
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

// ==========================================
// FOLLOWER LIST POPUP MODAL
// ==========================================
/**
 * Show User List Modal
 * Opens modal and fetches the list of followers or following users
 */
function nymiaShowUserList(userId, type, isOwnProfile) {
    const modal = document.getElementById('nymia-followers-modal');
    const title = document.getElementById('nymia-followers-modal-title');
    const body = document.getElementById('nymia-followers-modal-body');

    if (!modal || !title || !body) {
        console.error('Follower modal elements not found');
        return;
    }

    // Set title
    title.textContent = type === 'followers' ? 'Followers' : 'Following';

    // Show loading
    body.innerHTML = '<div style="text-align: center; padding: 40px; color: #999;">Loading...</div>';

    // Open modal
    modal.classList.add('active');

    // Store modal context
    window.nymiaModalContext = { userId, type, isOwnProfile };

    // Fetch user list via AJAX
    const ajaxUrl = (typeof nymiaAjax !== 'undefined' && nymiaAjax.ajaxurl) ? nymiaAjax.ajaxurl : '/wp-admin/admin-ajax.php';

    fetch(ajaxUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'nymia_get_user_list',
            user_id: userId,
            type: type
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayUserList(data.data.users);
            } else {
                body.innerHTML = '<div style="text-align: center; padding: 40px; color: #999;">' + data.data.message + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            body.innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;">Error loading users. Please try again.</div>';
        });
}

/**
 * Display User List
 * Renders the list of users in the modal
 */
function displayUserList(users) {
    const body = document.getElementById('nymia-followers-modal-body');
    if (!body) return;

    const context = window.nymiaModalContext || {};
    const type = context.type || 'followers';
    const isOwnProfile = context.isOwnProfile || false;

    if (!users || users.length === 0) {
        body.innerHTML = '<div style="text-align: center; padding: 40px; color: #999;">No users found.</div>';
        return;
    }

    let html = '';
    users.forEach(user => {
        const avatar = user.avatar || '';
        const profileUrl = '/profile/?user_id=' + user.id;

        // Show action button based on type
        let actionButton = '';
        const currentUserId = (typeof nymiaAjax !== 'undefined' && nymiaAjax.current_user_id) ? nymiaAjax.current_user_id : 0;
        const isLoggedIn = currentUserId > 0;

        if (isOwnProfile && type === 'following') {
            // Unfollow button on own following list
            actionButton = `
                <button class="nymia-user-list-action-btn nymia-unfollow-btn" onclick="nymiaUnfollowUser(${user.id}, this); event.stopPropagation();">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px; display: inline-block; vertical-align: middle;">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    <span>Unfollow</span>
                </button>
            `;
        } else if (isOwnProfile && type === 'followers') {
            // Remove button on own followers list
            actionButton = `
                <button class="nymia-user-list-action-btn" onclick="nymiaRemoveFollower(${user.id}, this); event.stopPropagation();">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px; display: inline-block; vertical-align: middle;">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    <span>Remove</span>
                </button>
            `;
        } else if (!isOwnProfile && type === 'following' && isLoggedIn && userId != currentUserId) {
            // Show follow/unfollow button when viewing someone else's following list
            actionButton = `
                <button class="nymia-user-list-action-btn nymia-follow-btn" onclick="nymiaToggleFollowInModal(${user.id}, this); event.stopPropagation();">
                    <span>Follow</span>
                </button>
            `;
        }

        html += `
            <div class="nymia-followers-item-wrapper">
                <div class="nymia-followers-item" onclick="window.location.href='${profileUrl}'">
                    <img src="${avatar}" alt="${user.name}" class="nymia-followers-item-avatar" onerror="this.src='/wp-content/themes/nymia-wp-theme/assets/images/profile.png';" />
                    <div class="nymia-followers-item-info">
                        <div class="nymia-followers-item-name">${user.name}</div>
                        <div class="nymia-followers-item-username">@${user.username}</div>
                    </div>
                </div>
                ${actionButton}
            </div>
        `;
    });

    body.innerHTML = html;
}

/**
 * Close User List Modal
 */
function nymiaCloseUserList() {
    const modal = document.getElementById('nymia-followers-modal');
    if (modal) {
        modal.classList.remove('active');
        window.nymiaModalContext = null;
    }
}

/**
 * Unfollow User
 * Unfollows a user from the list
 */
function nymiaUnfollowUser(userId, button) {
    if (typeof event !== 'undefined') {
        event.stopPropagation();
    }

    // Get user name from the button's parent item
    const item = button.closest('.nymia-followers-item-wrapper');
    const userName = item ? item.querySelector('.nymia-followers-item-name')?.textContent || 'this user' : 'this user';

    // Show custom confirmation dialog
    nymiaShowConfirmDialog(
        'Unfollow ' + userName,
        'Are you sure you want to unfollow ' + userName + '?',
        function () {
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Unfollowing...';

            const ajaxUrl = (typeof nymiaAjax !== 'undefined' && nymiaAjax.ajaxurl) ? nymiaAjax.ajaxurl : '/wp-admin/admin-ajax.php';
            const followNonce = (typeof nymiaAjax !== 'undefined' && nymiaAjax.followNonce) ? nymiaAjax.followNonce : '';

            fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'nymia_toggle_follow',
                    user_id: userId,
                    nonce: followNonce
                })
            })
                .then(response => response.json())
                .then(data => {
                    button.disabled = false;

                    if (data.success) {
                        const item = button.closest('.nymia-followers-item-wrapper');
                        if (item) {
                            item.style.opacity = '0';
                            item.style.transform = 'translateX(-20px)';
                            item.style.transition = 'all 0.3s ease';
                            setTimeout(() => {
                                item.remove();
                                location.reload();
                            }, 300);
                        }
                    } else {
                        button.textContent = originalText;
                        alert('Failed to unfollow user. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    button.disabled = false;
                    button.textContent = originalText;
                    alert('Network error. Please try again.');
                });
        }
    );
}

/**
 * Remove Follower
 * Removes a follower from the list
 */
function nymiaRemoveFollower(userId, button) {
    if (typeof event !== 'undefined') {
        event.stopPropagation();
    }

    // Get user name from the button's parent item
    const item = button.closest('.nymia-followers-item-wrapper');
    const userName = item ? item.querySelector('.nymia-followers-item-name')?.textContent || 'this follower' : 'this follower';

    // Show custom confirmation dialog
    nymiaShowConfirmDialog(
        'Remove ' + userName,
        'Are you sure you want to remove ' + userName + ' from your followers?',
        function () {
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Removing...';

            const ajaxUrl = (typeof nymiaAjax !== 'undefined' && nymiaAjax.ajaxurl) ? nymiaAjax.ajaxurl : '/wp-admin/admin-ajax.php';

            fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'nymia_remove_follower',
                    follower_id: userId
                })
            })
                .then(response => response.json())
                .then(data => {
                    button.disabled = false;

                    if (data.success) {
                        const item = button.closest('.nymia-followers-item-wrapper');
                        if (item) {
                            item.style.opacity = '0';
                            item.style.transform = 'translateX(-20px)';
                            item.style.transition = 'all 0.3s ease';
                            setTimeout(() => {
                                item.remove();
                                location.reload();
                            }, 300);
                        }
                    } else {
                        button.textContent = originalText;
                        alert('Failed to remove follower. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    button.disabled = false;
                    button.textContent = originalText;
                    alert('Network error. Please try again.');
                });
        }
    );
}

/**
 * Toggle Follow Status in Modal
 * Toggles follow/unfollow status from within the modal
 */
function nymiaToggleFollowInModal(userId, button) {
    if (typeof event !== 'undefined') {
        event.stopPropagation();
    }

    const isFollowing = button.textContent.trim() === 'Following';
    const originalText = button.textContent;

    button.disabled = true;
    button.textContent = isFollowing ? 'Unfollowing...' : 'Following...';

    const ajaxUrl = (typeof nymiaAjax !== 'undefined' && nymiaAjax.ajaxurl) ? nymiaAjax.ajaxurl : '/wp-admin/admin-ajax.php';
    const followNonce = (typeof nymiaAjax !== 'undefined' && nymiaAjax.followNonce) ? nymiaAjax.followNonce : '';

    fetch(ajaxUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'nymia_toggle_follow',
            user_id: userId,
            nonce: followNonce
        })
    })
        .then(response => response.json())
        .then(data => {
            button.disabled = false;

            if (data.success) {
                if (data.data.is_following) {
                    button.innerHTML = '<span>Following</span>';
                    button.classList.remove('nymia-follow-btn');
                    button.classList.add('nymia-unfollow-btn');
                } else {
                    button.innerHTML = '<span>Follow</span>';
                    button.classList.remove('nymia-unfollow-btn');
                    button.classList.add('nymia-follow-btn');
                }
            } else {
                button.textContent = originalText;
                alert(data.data.message || 'Something went wrong. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.disabled = false;
            button.textContent = originalText;
            alert('Network error. Please try again.');
        });
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('nymia-followers-modal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                nymiaCloseUserList();
            }
        });
    }

    // Close confirmation dialog when clicking outside
    const confirmDialog = document.getElementById('nymia-confirm-dialog');
    if (confirmDialog) {
        confirmDialog.addEventListener('click', function (e) {
            if (e.target === confirmDialog) {
                nymiaConfirmDialogClose();
            }
        });
    }
});

/**
 * Show Custom Confirmation Dialog
 */
function nymiaShowConfirmDialog(title, message, onConfirm) {
    const dialog = document.getElementById('nymia-confirm-dialog');
    const titleElement = dialog.querySelector('.nymia-confirm-dialog-title');
    const messageElement = dialog.querySelector('.nymia-confirm-dialog-message');

    if (!dialog) {
        // Fallback to browser confirm if dialog not found
        if (confirm(message)) {
            onConfirm();
        }
        return;
    }

    titleElement.textContent = title;
    messageElement.textContent = message;

    // Store callback
    window.nymiaConfirmCallback = onConfirm;

    // Show dialog
    dialog.classList.add('active');
}

/**
 * Close Confirmation Dialog
 */
function nymiaConfirmDialogClose() {
    const dialog = document.getElementById('nymia-confirm-dialog');
    if (dialog) {
        dialog.classList.remove('active');
    }
    window.nymiaConfirmCallback = null;
}

/**
 * Execute Confirmation
 */
function nymiaConfirmDialogExecute() {
    if (window.nymiaConfirmCallback && typeof window.nymiaConfirmCallback === 'function') {
        window.nymiaConfirmCallback();
    }
    nymiaConfirmDialogClose();
}

