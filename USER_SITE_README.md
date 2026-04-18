# SINTA USER SITE - Modern Minimalist Design

## 🎨 Overview

Complete redesign of the SINTA user-facing website with:
- Modern, clean minimalist aesthetic
- Professional purple/emerald color scheme
- Fully responsive design
- Real-time user-to-admin messaging
- Improved booking management
- Database-integrated functionality

## 📁 Files Created

### Core CSS & Layout
- **`public/assets/css/user-modern.css`** - Complete CSS system with all user components
- **`app/views/user/header-modern.php`** - Modern header with navigation
- **`app/views/user/footer.php`** - Professional footer

### User Pages
- **`app/views/user/homepage-modern.php`** - Homepage with featured packages
- **`app/views/user/packages-modern.php`** - Package browsing with filters
- **`app/views/user/bookings-modern.php`** - Booking management dashboard
- **`app/views/user/messages-modern.php`** - Real-time messaging with admin

### API Endpoints
- **`public/api/messages/index.php`** - Message API (send/receive)
- **`public/api/bookings/index.php`** - Booking API (CRUD operations)

### Routing
- **`public/routes-modern.php`** - Modern route configuration

## 🎯 Key Features

### 1. **Modern User Header**
- Clean navigation with active states
- User profile menu
- Unread message badge counter
- Sign in/up buttons for guests
- Responsive mobile menu

### 2. **Homepage**
- Hero section with CTA buttons
- Featured packages grid (6 packages)
- Statistics section (4 key metrics)
- Event types showcase
- Call-to-action section
- Professional footer

### 3. **Packages Page**
Features:
- Search functionality
- Event type filter
- Price sorting (ASC/DESC)
- Package cards with images
- Feature list with inclusions
- Quick book/view buttons
- Responsive grid layout

### 4. **Bookings Management**
Features:
- Tab-based navigation (All/Confirmed/Pending)
- Booking status tracking
- Payment information display
- Action buttons (View, Complete Payment, Cancel)
- Empty states
- Organized by status

### 5. **Real-Time Messaging** ⭐
**User-to-Admin Only System:**
- Clean 2-column chat interface
- Message history with timestamps
- Auto-polling for new messages (3-second intervals)
- Admin only appears on user side
- Message thread organization
- Real-time notifications
- Auto-scroll to latest message

### 6. **Database Integration**
All pages connect directly to database:
- Packages from `packages_tbl`
- Bookings from `checkout_tbl`
- Messages from `tbl_messages`
- Users from `users_tbl`
- Occasions from `occasions_tbl`

## 🚀 Integration Steps

### Step 1: Link Modern CSS
```html
<link rel="stylesheet" href="/SINTA/public/assets/css/user-modern.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
```

### Step 2: Update Your Router
In `public/index.php`, add:
```php
require_once ROOT_PATH . '/public/routes-modern.php';

$route = $_GET['route'] ?? 'landing';

if (modernRouteExists($route)) {
    include getModernRoute($route);
} else {
    // Fallback to old routing
    include VIEW_PATH . 'landing/landing.php';
}
```

### Step 3: Ensure Database Models are Loaded
```php
require_once ROOT_PATH . '/app/models/Package.php';
require_once ROOT_PATH . '/app/models/Booking.php';
require_once ROOT_PATH . '/app/models/Message.php';
require_once ROOT_PATH . '/app/models/User.php';
```

## 📊 Color System

```
Primary:     #6D28D9 (Purple - Elegant)
Secondary:   #059669 (Emerald Green)
Accent:      #DC2626 (Red - CTAs)

Success:     #10B981
Warning:     #F59E0B
Danger:      #EF4444
Info:        #3B82F6
```

## 🔧 API Endpoints

### Messages API
```
POST /SINTA/public/api/messages?action=send
GET  /SINTA/public/api/messages?action=getAll&user_id=1
GET  /SINTA/public/api/messages?action=check&user_id=1
```

### Bookings API
```
GET  /SINTA/public/api/bookings?action=getUserBookings&user_id=1
GET  /SINTA/public/api/bookings?action=getById&booking_id=1
POST /SINTA/public/api/bookings?action=create
POST /SINTA/public/api/bookings?action=cancel
PUT  /SINTA/public/api/bookings?action=update
```

## 📱 Responsive Breakpoints

- **Desktop**: 1200px+
- **Tablet**: 768px - 1199px
- **Mobile**: 640px - 767px
- **Small Mobile**: Below 640px

All pages are mobile-first and fully responsive.

## 🎯 Component Library

### Cards
```html
<div class="card">Content</div>
```

### Package Cards
```html
<div class="package-card">
    <div class="package-card__header">Header</div>
    <div class="package-card__body">Body</div>
    <div class="package-card__footer">Footer</div>
</div>
```

### Buttons
```html
<a class="btn btn--primary">Primary</a>
<a class="btn btn--secondary">Secondary</a>
<a class="btn btn--success">Success</a>
<a class="btn btn--sm">Small</a>
<a class="btn btn--lg">Large</a>
<a class="btn btn--block">Full Width</a>
```

### Badges
```html
<span class="badge badge--primary">Primary</span>
<span class="badge badge--success">Success</span>
<span class="badge badge--warning">Warning</span>
<span class="badge badge--danger">Danger</span>
```

### Forms
```html
<div class="form-group">
    <label class="form-label form-label--required">Label</label>
    <input class="form-input" type="text">
</div>

<select class="form-select">
    <option>Option</option>
</select>

<textarea class="form-textarea"></textarea>
```

## 🔐 Security Features

- ✅ SQL injection prevention (prepared statements)
- ✅ Session-based authentication
- ✅ Input validation & sanitization
- ✅ HTML escaping for output
- ✅ CSRF protection ready
- ✅ Role-based access control

## 📝 Database Tables Required

### tbl_messages
```sql
CREATE TABLE tbl_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    admin_id INT,
    parent_id INT,
    content TEXT NOT NULL,
    is_admin_reply BOOLEAN DEFAULT 0,
    status ENUM('read', 'unread') DEFAULT 'unread',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### checkout_tbl
```sql
CREATE TABLE checkout_tbl (
    checkout_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    package_id INT,
    plan_id INT,
    total_amount DECIMAL(10, 2),
    deposit_amount DECIMAL(10, 2),
    payment_method VARCHAR(50),
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### packages_tbl
```sql
CREATE TABLE packages_tbl (
    package_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    occasion_id INT,
    price DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🎨 Customization Guide

### Change Primary Color
Find in `user-modern.css`:
```css
:root {
    --primary: #6D28D9; /* Change this */
}
```

### Add New Page
1. Create new file in `app/views/user/`
2. Include `header-modern.php` at top
3. Include `footer.php` at bottom
4. Add to `routes-modern.php`

### Modify Messaging System
Edit `app/views/user/messages-modern.php` to:
- Change polling interval
- Add new message types
- Customize styling

## 🚨 Common Issues

### Issue: Styles not loading
**Solution**: Check CSS file path and clear browser cache

### Issue: Messages not sending
**Solution**: Verify API endpoint and check browser console for errors

### Issue: Bookings not displaying
**Solution**: Ensure database connection and user ID in session

## 📞 Support

For integration help, refer to:
- Component library in this documentation
- Inline code comments in each file
- Database queries in models

## ✅ Quality Checklist

- ✅ Fully responsive design
- ✅ Database integrated
- ✅ Real-time messaging
- ✅ User-admin only messaging
- ✅ Modern aesthetic
- ✅ Accessible design
- ✅ Well documented
- ✅ SEO friendly
- ✅ Mobile optimized
- ✅ Security best practices

---

**Last Updated**: April 18, 2026
**Version**: 2.0
**Status**: Ready for Production

