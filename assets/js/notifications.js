/**
 * Notification System
 * Handles fetching, displaying, and managing user notifications
 */

class NotificationSystem {
    constructor() {
        this.notifBtn = document.getElementById('notifBtn');
        this.notifDropdown = document.getElementById('notifDropdown');
        this.notifBadge = this.notifBtn?.querySelector('.app-nav__badge');
        this.refreshInterval = 30000; // Refresh every 30 seconds
        this.init();
    }

    init() {
        if (this.notifBtn && this.notifDropdown) {
            this.loadNotifications();
            
            // Auto-refresh notifications
            setInterval(() => this.loadNotifications(), this.refreshInterval);
            
            // Setup dropdown close on mark all read
            const markAllReadLink = this.notifDropdown.querySelector('a');
            if (markAllReadLink) {
                markAllReadLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.markAllAsRead();
                });
            }
        }
    }

    async loadNotifications() {
        try {
            const response = await fetch('/SINTA/public/api-notification.php?action=get_unread&limit=10');
            const data = await response.json();
            
            if (data.success) {
                this.updateBadge(data.unread_count);
                this.renderNotifications(data.notifications);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    updateBadge(count) {
        if (this.notifBadge) {
            if (count > 0) {
                this.notifBadge.textContent = count > 99 ? '99+' : count;
                this.notifBadge.style.display = 'inline-block';
            } else {
                this.notifBadge.style.display = 'none';
            }
        }
    }

    renderNotifications(notifications) {
        const container = this.notifDropdown.querySelector('.notif-dropdown__container');
        
        if (!container) {
            // If container doesn't exist, create it after the header
            const header = this.notifDropdown.querySelector('.notif-dropdown__header');
            const newContainer = document.createElement('div');
            newContainer.className = 'notif-dropdown__container';
            header.after(newContainer);
        }
        
        const container2 = this.notifDropdown.querySelector('.notif-dropdown__container');
        
        if (notifications.length === 0) {
            container2.innerHTML = '<div class="notif-empty"><i class="fas fa-inbox"></i><span>No notifications</span></div>';
            return;
        }
        
        container2.innerHTML = notifications.map(notif => this.createNotificationHTML(notif)).join('');
        
        // Add click handlers to notifications
        container2.querySelectorAll('.notif-dropdown__item').forEach((item, index) => {
            const notificationId = notifications[index].notification_id;
            item.addEventListener('click', () => this.markAsRead(notificationId));
        });
    }

    createNotificationHTML(notif) {
        const typeIcons = {
            'message_reply': 'fas fa-envelope',
            'feedback_reply': 'fas fa-comments',
            'payment_due': 'fas fa-credit-card',
            'system_update': 'fas fa-star',
            'booking_confirmation': 'fas fa-calendar-check',
            'receipt': 'fas fa-receipt',
            'ratings': 'fas fa-star-half-alt',
            'new_updates': 'fas fa-refresh',
            'realtime': 'fas fa-circle-notch',
            'default': 'fas fa-bell'
        };
        
        const icon = typeIcons[notif.type] || typeIcons['default'];
        const isRead = notif.is_read === 1;
        const timeStr = this.formatTime(notif.created_at);
        
        return `
            <div class="notif-dropdown__item ${isRead ? '' : 'unread'}" data-id="${notif.notification_id}">
                <div class="notif-icon">
                    <i class="${icon}"></i>
                </div>
                <div class="notif-content">
                    <div class="notif-title">${this.escapeHtml(notif.title)}</div>
                    <div class="notif-message">${this.escapeHtml(notif.message)}</div>
                    <div class="notif-time">${timeStr}</div>
                </div>
                <button class="notif-close" onclick="notificationSystem.deleteNotification(event, ${notif.notification_id})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    }

    formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);
        
        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        if (diffDays === 1) return 'Yesterday';
        if (diffDays < 7) return `${diffDays}d ago`;
        
        return date.toLocaleDateString();
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch('/SINTA/public/api-notification.php?action=mark_as_read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `notification_id=${notificationId}`
            });
            
            const data = await response.json();
            if (data.success) {
                this.loadNotifications();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/SINTA/public/api-notification.php?action=mark_all_as_read', {
                method: 'POST'
            });
            
            const data = await response.json();
            if (data.success) {
                this.loadNotifications();
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    deleteNotification(event, notificationId) {
        event.stopPropagation();
        
        if (confirm('Delete this notification?')) {
            fetch('/SINTA/public/api-notification.php?action=delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `notification_id=${notificationId}`
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      this.loadNotifications();
                  }
              })
              .catch(error => console.error('Error deleting notification:', error));
        }
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.notificationSystem = new NotificationSystem();
});
