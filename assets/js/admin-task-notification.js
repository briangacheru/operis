// Track shown notifications to prevent duplicates (persistent across checks)
let shownNotifications = new Set();

function showToast(message, type = 'success', taskId = null, duration = 30000) {
    // Create unique identifier for this notification
    const notificationKey = taskId ? `task-${taskId}` : message;

    // Check if we've already shown this notification
    if (shownNotifications.has(notificationKey)) {
        return; // Don't show duplicate - this prevents repeated toasts
    }

    // Mark as shown (this persists across all future checks)
    shownNotifications.add(notificationKey);

    // Remove existing toasts if too many to avoid clutter
    const existingToasts = document.querySelectorAll('.custom-toast');
    if (existingToasts.length >= 5) { // Max 5 toasts at once
        existingToasts[0].remove(); // Remove oldest
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `custom-toast toast-${type}`;
    toast.setAttribute('data-notification-key', notificationKey);
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-icon">${type === 'success' ? '📝' : '⚠️'}</span>
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="removeToast(this, '${notificationKey}')">×</button>
        </div>
    `;

    // Add to page
    document.body.appendChild(toast);

    // Apply styles
    toast.style.cssText = `
        position: fixed !important;
        top: 20px !important;
        right: 20px !important;
        background: white !important;
        color: #333 !important;
        padding: 15px !important;
        z-index: 99999 !important;
        border-left: 4px solid #28a745 !important;
        min-width: 300px !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        animation: slideInRight 0.3s ease-out !important;
    `;

    // Play notification sound only if no other toasts are currently visible
    if (existingToasts.length === 0) {
        playTaskNotificationSound();
    }

    // Auto remove with timeout
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
            // NOTE: We DON'T remove from shownNotifications here
            // This ensures the task won't show toast again
        }
    }, duration);
}

// Helper function to remove toast (keeps tracking to prevent re-showing)
function removeToast(element, notificationKey) {
    const toast = element.tagName === 'BUTTON' ? element.parentElement.parentElement : element;
    if (toast && toast.parentNode) {
        toast.remove();
        // NOTE: We DON'T remove from shownNotifications here
        // This ensures the task won't show toast again even if manually closed
    }
}

// Play custom task notification sound
function playTaskNotificationSound() {
    try {
        const audio = new Audio('../audio/task-notification.mp3');
        audio.volume = 0.8;
        audio.play().catch(e => {
            // Fallback: try alternative notification
            if (window.speechSynthesis) {
                const utterance = new SpeechSynthesisUtterance('Task submitted');
                utterance.rate = 1.2;
                utterance.volume = 0.3;
                window.speechSynthesis.speak(utterance);
            }
        });
    } catch (error) {
        // Silent fallback
    }
}

// Check for new tasks every 30 seconds
function checkForNewTasks() {
    fetch('check_new_tasks.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.tasks && data.tasks.length > 0) {
                // Filter out tasks we've already shown toasts for
                const newTasks = data.tasks.filter(task => !shownNotifications.has(`task-${task.id}`));

                if (newTasks.length > 0) {
                    newTasks.forEach((task, index) => {
                        setTimeout(() => {
                            showToast(`Task submitted: ${task.topic}`, 'success', task.id);
                        }, index * 500); // 500ms delay between toasts
                    });

                    setTimeout(() => {
                        updateNotificationBadge();
                    }, newTasks.length * 500 + 1000);
                }
            }
        })
        .catch(error => {
            // Silent error handling
        });
}

// Update notification badge count
function updateNotificationBadge() {
    fetch('get_notification_counts.php')
        .then(response => response.json())
        .then(data => {
            // Update new tasks badge
            const newTasksBadge = document.querySelector('#navbarDropdownNewTasks .notification-indicator-number');
            if (data.newTasksCount > 0) {
                if (newTasksBadge) {
                    newTasksBadge.textContent = data.newTasksCount;
                } else {
                    // Create badge if it doesn't exist
                    const badge = document.createElement('span');
                    badge.className = 'notification-indicator-number';
                    badge.textContent = data.newTasksCount;
                    document.querySelector('#navbarDropdownNewTasks').appendChild(badge);
                }
            } else {
                if (newTasksBadge) {
                    newTasksBadge.remove();
                }
            }

            // Update late tasks badge
            const lateTasksBadge = document.querySelector('#navbarDropdownNotification .notification-indicator-number');
            if (lateTasksBadge) {
                lateTasksBadge.textContent = data.lateTasksCount;
            }
        })
        .catch(error => {
            // Silent error handling
        });
}

// Mark individual task as read
function markTaskAsRead(taskId) {
    fetch('mark_task_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `task_id=${taskId}`
    })
        .then(response => response.json())
        .catch(error => {
            // Silent error handling
        });
}

// Mark all tasks as read
function markAllAsRead() {
    fetch('mark_all_tasks_read.php', {
        method: 'POST'
    })
        .then(response => response.json())
        .then(data => {
            updateNotificationBadge();
        })
        .catch(error => {
            // Silent error handling
        });
}

// Optional: Clear shown notifications (only use when needed)
function clearShownNotifications() {
    shownNotifications.clear();
}

// Optional: Clear notifications only when user logs out or session ends
function onUserLogout() {
    clearShownNotifications();
}

// Start checking for new tasks when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Check immediately
    checkForNewTasks();

    // Then check every 30 seconds
    setInterval(checkForNewTasks, 30000);
});

// Optional: Manual function to clear all toasts and reset tracking (for testing)
function clearAllToastsAndReset() {
    const toasts = document.querySelectorAll('.custom-toast');
    toasts.forEach(toast => toast.remove());
    clearShownNotifications();
}