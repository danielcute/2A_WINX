# SINTA Deployment Verification Checklist

## ADMIN SIDE - CRITICAL

### 1. Hamburger Navigation (All Pages)
- [ ] Dashboard: Click hamburger → sidebar opens → overlay appears → click overlay to close
- [ ] Profile: Hamburger clickable and functioning
- [ ] Packages: Test hamburger on mobile (480px viewport)
- [ ] Bookings: Verify on tablet (768px) and mobile
- [ ] Occasions: All navigation working
- [ ] Feedback: Menu closes when clicking menu items
- [ ] Customize: Sidebar functions correctly
- [ ] Wardrobe: Toggle working on all resolutions
- [ ] Messages: Hamburger responsive

### 2. Package Management (Image CRUD)
- [ ] Create: Upload image with new package → image saves and displays
- [ ] Read: Package list shows all uploaded images
- [ ] Update: Edit package image → new image replaces old
- [ ] Delete: Package deletion removes associated image
- [ ] Display: Image paths correct (check console for 404s)
- [ ] Fallback: Missing images show default wedding.jpg

### 3. Container & Size Consistency (All Pages)
- [ ] Desktop (>1024px): All containers properly sized
- [ ] Tablet (768px-1024px): Single column layout working
- [ ] Mobile (480px-768px): Full-width display, no overflow
- [ ] Small Mobile (<480px): All text readable, buttons accessible
- [ ] Navbar: No content hidden behind fixed navbar
- [ ] Padding: Consistent 1-2rem spacing all pages
- [ ] Buttons: All 44px+ min-height for touch targets

### 4. Button Functionality (All Pages)
Dashboard:
- [ ] All action buttons functional
- [ ] Quick action buttons clickable
- [ ] Add buttons working

Packages:
- [ ] Add package button
- [ ] Edit buttons on cards
- [ ] Delete buttons with confirmation
- [ ] Image upload field responsive

Bookings:
- [ ] View details functional
- [ ] Status update dropdown working
- [ ] Action buttons (view, update, cancel)

Occasions:
- [ ] Add occasion button
- [ ] Edit occasions
- [ ] Delete with confirmation

Feedback:
- [ ] Reply buttons functional
- [ ] Delete feedback buttons
- [ ] Status update dropdown

Customize:
- [ ] Add customization option
- [ ] Edit options
- [ ] Delete options
- [ ] Save changes functional

Wardrobe:
- [ ] Add wardrobe button
- [ ] Edit wardrobe items
- [ ] Delete wardrobe
- [ ] Image upload functional

Messages:
- [ ] Send reply button
- [ ] Delete message button
- [ ] Navigation between conversations

Profile:
- [ ] Save changes button
- [ ] Change avatar button
- [ ] Password update functional

### 5. CRUD Operations
- [ ] All Create operations: Form submission → database save → confirmation
- [ ] All Read operations: Data displays correctly → pagination works
- [ ] All Update operations: Edit form → database update → list refreshes
- [ ] All Delete operations: Delete button → confirmation → database remove

---

## USER SIDE - CRITICAL

### 1. Error Scanning
- [ ] Open browser console (F12) → check for JavaScript errors
- [ ] Homepage: No error messages displayed
- [ ] Packages/Plans: All images load → no 404 errors
- [ ] Booking: Form validation working → no console errors
- [ ] Checkout: No API errors in Network tab
- [ ] Messages: No WebSocket errors
- [ ] Notifications: No undefined variable errors
- [ ] Profile: Form submit → no errors

### 2. Customization Feature
- [ ] Admin customization options created
- [ ] User sees admin options in customization page
- [ ] Customization selections properly stored
- [ ] Customization affects booking/checkout display
- [ ] Color combinations work correctly
- [ ] Custom options update in real-time

### 3. Booking Functionality
- [ ] User selects package
- [ ] Date picker functional
- [ ] Event type selection working
- [ ] Quantity field accepts input
- [ ] Booking form validates required fields
- [ ] Submit button creates booking record
- [ ] Confirmation message appears

### 4. Plans/Packages Display
- [ ] All package images load
- [ ] Quick view buttons functional
- [ ] Add to cart working
- [ ] Event buttons clickable
- [ ] Package details display correctly
- [ ] Price calculations accurate
- [ ] Filter/sort working (if applicable)

### 5. Messages
- [ ] Send message button functional
- [ ] Message list displays all conversations
- [ ] Message details show correctly
- [ ] Reply functionality working
- [ ] Delete message working
- [ ] Timestamps accurate

### 6. Real-time Notifications
- [ ] Notifications dropdown appears
- [ ] Real-time updates showing
- [ ] Messages notifications appearing
- [ ] Booking notifications showing
- [ ] Receipt notifications displaying
- [ ] Confirmation notifications working
- [ ] All notification types appearing

### 7. Checkout & Receipt
- [ ] Event details displayed on checkout
- [ ] Booking summary showing correctly
- [ ] Deposit amount calculating properly
- [ ] Payment form functional
- [ ] Receipt generating after purchase
- [ ] Receipt details correct
- [ ] Download/print working

### 8. Profile & 2FA
- [ ] Profile photo upload functional
- [ ] Photo displays on profile page
- [ ] 2FA setup page accessible
- [ ] 2FA verification code working
- [ ] 2FA enable/disable toggling
- [ ] Profile info update saving
- [ ] Password change functional

---

## DEPLOYMENT READINESS

### 1. Error Logging
- [ ] No PHP errors in error_log
- [ ] No fatal errors on any page
- [ ] All 404s resolved
- [ ] CORS errors (if any) fixed

### 2. Performance
- [ ] Page load time < 3 seconds
- [ ] No memory exhaustion errors
- [ ] No timeout errors
- [ ] Images optimized

### 3. Security
- [ ] SQL injection prevention (prepared statements)
- [ ] XSS prevention (htmlspecialchars used)
- [ ] CSRF tokens checked
- [ ] Session validation working
- [ ] File upload validation strict

### 4. Cross-browser Testing
- [ ] Chrome: All pages functional
- [ ] Firefox: No rendering issues
- [ ] Edge: All features working
- [ ] Mobile browsers: Responsive design works

### 5. Database
- [ ] All tables created
- [ ] No missing columns
- [ ] Foreign keys working
- [ ] Indexes present for performance

### 6. API Endpoints
- [ ] api-notification.php: /api-notification.php working
- [ ] api-payment.php: Payment processing functional
- [ ] api-receipt.php: Receipt generation working
- [ ] api-messages.php: Messages API responding
- [ ] api-user-profile.php: Profile updates working
- [ ] All API endpoints returning proper JSON
- [ ] Error handling returns appropriate status codes

---

## TESTING SEQUENCE

1. **Clear Browser Cache**: Ctrl+Shift+Delete or Cmd+Shift+Delete
2. **Test Admin Login**: Verify session handling
3. **Test Each Admin Page**: Hamburger, buttons, CRUD operations
4. **Test User Login**: Regular user access
5. **Test User Pages**: Navigation, customization, booking
6. **Test Notifications**: Real-time updates
7. **Test Checkout**: Payment flow
8. **Check Console**: F12 → Console tab for errors
9. **Check Network**: F12 → Network tab for failed requests
10. **Test Mobile**: Responsive design at 480px, 768px, 1024px

---

## QUICK FIX REFERENCE

If issues found:

1. **Hamburger not clicking**: Clear cache, verify admin-nav.php has pointer-events fixes
2. **Images not showing**: Check /public/assets/img/packages/ folder exists with 777 permissions
3. **Buttons not working**: Verify onclick handlers in HTML, check JavaScript console
4. **Forms not submitting**: Check form action, verify CSRF tokens, check validation
5. **API errors**: Check Network tab for response status, review error_log
6. **Styling issues**: Hard refresh (Ctrl+F5), verify CSS files loading
7. **Session lost**: Check cookie settings, verify session_start() called
8. **Database errors**: Check database connection, verify tables exist

---

## CONTACT INFO

For support during deployment:
- Check error_log in project root
- Review browser console (F12)
- Check Network tab for API failures
- Verify file permissions (uploads folder should be 777)

**Status**: Ready for deployment ✅
