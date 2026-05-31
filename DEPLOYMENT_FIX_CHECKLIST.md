# Deployment Fix Checklist for Hostinger

## Step 1: Clean Up Local Files
✅ Already done - deleted `default.php` and `fix-paths.php`

## Step 2: Files That MUST Be Uploaded to Hostinger
After any local edit, ensure these folders are synced:

### Critical Files to Upload:
- `public/index.php` - Main routing file
- `public/assets/css/*.css` - All CSS files
- `public/assets/js/*.js` - All JavaScript files  
- `app/views/*/` - All PHP view files
- `app/models/` - All model files
- `app/controllers/` - All controller files
- `config/database.php` - Database configuration

### Verify on Server:
- [ ] CSS files are in `/public/assets/css/`
- [ ] JS files are in `/public/assets/js/`
- [ ] Images are in `/public/assets/img/`
- [ ] No `default.php` exists anywhere

## Step 3: Clear Browser Cache
- Press `Ctrl+Shift+Delete` and clear:
  - [ ] Cached files and images
  - [ ] Cookies

## Step 4: Test Each Route
- [ ] `/public/index.php?route=landing` - Check landing page CSS
- [ ] `/public/index.php?route=signin` - Check signin page
- [ ] `/public/index.php?route=signup` - Check signup page
- [ ] Dashboard (if logged in) - Check all styling

## Step 5: Check Browser Console
After uploading, visit the site and press `F12`:
- Check the "Console" tab for JavaScript errors
- Check the "Network" tab to see if CSS files load (should be 200 OK status)
- Check the "Elements" tab to verify CSS is linked in `<head>`

## Step 6: Hostinger Specific
If using File Manager:
- Right-click files → Select all → Upload
- Or use Hostinger's bulk upload feature

If using FTP/SFTP:
- Use FileZilla or similar FTP client
- Ensure all files are transferred correctly

## Notes
- Do NOT keep multiple PHP files with similar names (check-*, test-*, debug-*)
- Ensure `ROOT_PATH` in index.php correctly points to parent directory
- Database connection uses port 3307 (configured in config/database.php)

