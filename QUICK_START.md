# 🚀 SINTA Modern User Site - Quick Start Guide

## ⚡ 5-Minute Setup

### Step 1: Link CSS & Icons
Add to your page header:
```html
<link rel="stylesheet" href="/SINTA/public/assets/css/user-modern.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
```

### Step 2: Update Your Router (index.php)
```php
<?php
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/public/routes-modern.php';

$route = $_GET['route'] ?? 'landing';

if (modernRouteExists($route)) {
    include getModernRoute($route);
} else {
    include ROOT_PATH . '/app/views/landing/landing.php';
}
?>
```

### Step 3: That's It! ✅
Navigate to:
- `/SINTA/public/index.php?route=homepage` - User homepage
- `/SINTA/public/index.php?route=packages` - Package browser
- `/SINTA/public/index.php?route=bookings` - My bookings
- `/SINTA/public/index.php?route=messages` - Messages with admin
- `/SINTA/public/index.php?route=admin-messages` - Admin messaging

## 🎯 Core Features Overview

### 1. **Homepage** (homepage-modern.php)
```
├─ Hero Section (CTA Buttons)
├─ Featured Packages (Grid)
├─ Statistics Cards
├─ Event Types Showcase
└─ Footer with Links
```

**Features:**
- Responsive hero with background
- Auto-fetches featured packages from DB
- Click-to-book buttons
- Professional statistics display

### 2. **Packages** (packages-modern.php)
```
├─ Search Box
├─ Filter by Event Type
├─ Sort by Price
└─ Package Grid
```

**Features:**
- Real-time search filtering
- Dropdown occasion filter
- Price sorting (ASC/DESC)
- Direct booking buttons
- 3-column responsive grid

### 3. **My Bookings** (bookings-modern.php)
```
├─ Tab Navigation (All/Confirmed/Pending)
├─ Booking Cards with:
│  ├─ Package Info
│  ├─ Pricing Breakdown
│  ├─ Payment Status
│  └─ Action Buttons
└─ Empty State Messages
```

**Features:**
- Status-based organization
- Payment tracking
- Quick actions (Complete Payment, Cancel)
- Empty state handling
- Responsive layout

### 4. **Messages** (messages-modern.php)
```
├─ Chat Interface
├─ Message History
├─ Message Input Form
├─ Auto-scroll
└─ Auto-poll (3 sec)
```

**Features:**
- Real-time message polling
- Single admin conversation
- Timestamp display
- Admin indicator
- Auto-refresh on new replies

### 5. **Admin Messages** (admin-messages-modern.php)
```
├─ Conversations List
│  ├─ User Avatar
│  ├─ User Name
│  ├─ Last Message Preview
│  ├─ Unread Count Badge
│  └─ Click to Open
├─ Chat Panel
│  ├─ User Info Header
│  ├─ Message Thread
│  ├─ Reply Input
│  └─ Auto-refresh (5 sec)
```

**Features:**
- All user conversations displayed
- Unread message count badges
- Real-time message polling
- Admin reply interface
- Auto-scroll to latest
- User contact info display

## 📡 API Reference

### Messages API
```bash
# Check for new messages
GET /SINTA/public/api/messages?action=check&user_id=1

# Get all messages in conversation
GET /SINTA/public/api/messages?action=getAll&user_id=1

# Send user message
POST /SINTA/public/api/messages?action=send
{
  "user_id": 1,
  "content": "Hello admin"
}

# Admin reply
POST /SINTA/public/api/messages?action=adminReply
{
  "user_id": 1,
  "content": "Hi user, how can I help?"
}
```

### Bookings API
```bash
# Get user bookings
GET /SINTA/public/api/bookings?action=getUserBookings&user_id=1

# Cancel booking
POST /SINTA/public/api/bookings?action=cancel
{
  "booking_id": 5
}

# Update booking (admin only)
PUT /SINTA/public/api/bookings?action=update
{
  "booking_id": 5,
  "total_amount": 5000,
  "status": "confirmed"
}
```

## 🎨 Customization Guide

### Change Primary Color
Edit `/SINTA/public/assets/css/user-modern.css`:
```css
:root {
    --primary: #6D28D9;        /* Change this */
    --secondary: #059669;      /* Or this */
}
```

### Add New Button Style
```html
<a class="btn btn--primary">Primary Button</a>
<a class="btn btn--secondary">Secondary Button</a>
<a class="btn btn--success">Success Button</a>
<a class="btn btn--danger">Danger Button</a>
<a class="btn btn--sm">Small Button</a>
<a class="btn btn--lg">Large Button</a>
<a class="btn btn--block">Full Width Button</a>
```

### Create New Component
```html
<div class="card">
    <div class="card__header">Header</div>
    <div class="card__body">Body Content</div>
    <div class="card__footer">Footer</div>
</div>
```

### Form Elements
```html
<div class="form-group">
    <label class="form-label form-label--required">Email</label>
    <input type="email" class="form-input" placeholder="Enter email">
</div>

<select class="form-select">
    <option>Choose option</option>
</select>

<textarea class="form-textarea"></textarea>
```

## 🔐 Security Setup

### Ensure Sessions Are Protected
```php
// config/database.php should include
session_start();

// Verify in modern pages
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: /SINTA/public/index.php?route=signin');
    exit;
}
```

### Input Validation
All API endpoints validate:
- User ID verification
- Admin authentication checks
- SQL injection prevention (mysqli)
- HTML escaping on output

## 🐛 Troubleshooting

### Problem: Styles not loading
**Solution**: Clear browser cache (Ctrl+Shift+Delete) and verify CSS path

### Problem: Messages not sending
**Solution**: Check browser console (F12) for errors, verify API endpoint path

### Problem: 404 on routes
**Solution**: Ensure routes-modern.php is included and all file paths are correct

### Problem: No bookings displaying
**Solution**: Verify user_id in session and database has data in checkout_tbl

## 📊 Performance Tips

1. **Reduce Polling Interval** - Change from 3000ms to 5000ms+ for less server load
2. **Paginate Messages** - Add LIMIT to database queries for large conversations
3. **Cache Package Data** - Store Package listing in memory/Redis
4. **Lazy Load Images** - Add `loading="lazy"` to all images
5. **Minify CSS** - Use production-ready minified version

## 🚨 Important Notes

### Real-Time Messaging:
- Uses client-side polling (not WebSockets)
- Checks every 3 seconds for new messages
- User-to-Admin only (not multi-user chat)
- Unread messages auto-marked as read when viewed

### Booking Status:
- **pending** - User hasn't completed payment
- **confirmed** - Admin confirmed booking
- **cancelled** - User/Admin cancelled

### Admin Features:
- Reply to user messages
- View conversation history
- Track unread message count
- Auto-update on new messages

## 📞 Need Help?

Refer to:
1. **USER_SITE_README.md** - Full documentation
2. **In-file comments** - Check PHP files for inline docs
3. **Console errors** - Browser console (F12) shows what's wrong
4. **Database** - Ensure sinta_db is accessible on localhost:3307

## ✅ Verification Checklist

- [ ] CSS file loads (check browser DevTools Network tab)
- [ ] Homepage displays without errors
- [ ] Packages load from database
- [ ] Can send message as user
- [ ] Admin can see and reply to messages
- [ ] Bookings display correctly
- [ ] All buttons are clickable and styled

## 🎉 You're All Set!

The modern SINTA user site is ready to deploy. All features are:
- ✅ Database integrated
- ✅ Fully responsive
- ✅ Real-time enabled
- ✅ Production ready
- ✅ Well documented

---

**Last Updated**: Current session
**Version**: 2.0
**Status**: ✅ Production Ready

