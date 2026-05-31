# 🚀 FIX GUIDE: Wardrobe & Occasions Not Working

**The Problem**: You deployed files but wardrobe feature doesn't work and can't edit occasions on the live site.

**The Root Cause**: Files weren't uploaded completely OR database tables are missing OR wrong files uploaded.

---

## ✅ QUICK FIX (5 Steps)

### STEP 1️⃣: Upload All Files

Go to **Hostinger File Manager** → Navigate to **public_html** folder

#### Option A - Upload Folder (Faster - Recommended)
1. Click **"Upload Folder"** button
2. Select from your computer: `c:\xampp\htdocs\SINTA\app\`
3. Wait for upload to finish
4. Repeat for `c:\xampp\htdocs\SINTA\public\`
5. Repeat for `c:\xampp\htdocs\SINTA\config\`

#### Option B - Upload Individual Files (Slower)
If folders don't work, upload these files one by one:

**Models** (in `/app/models/`):
- `Wardrobe.php`
- `Occasion.php`

**Controllers** (in `/app/controllers/`):
- `WardrobeController.php`
- `WardrobeSelectionController.php`
- `OccasionController.php`

**Admin Views** (in `/app/views/admin/`):
- `admin-wardrobe.php`
- `admin-wardrobe-add.php`
- `admin-wardrobe-edit.php`
- `admin-wardrobe-selections.php`
- `admin-occasions.php`

**User Views** (in `/app/views/user/`):
- `wardrobe.php`
- `wardrobe-selection.php`

**API Files** (in `/public/`):
- `api-wardrobe.php`
- `api-wardrobe-image.php`
- `api-wardrobe-update.php`
- `api-wardrobe-selections.php`
- `api-occasion.php`

**Core** (in `/public/`):
- `index.php` ← IMPORTANT!

---

### STEP 2️⃣: Set File Permissions

In **Hostinger File Manager**:

1. Find `/app/` folder → Right-click → **Change Permissions** → Set to **755**
2. Find `/public/` folder → Right-click → **Change Permissions** → Set to **755**
3. Find `/config/` folder → Right-click → **Change Permissions** → Set to **755**

✅ Do this for folders!  
✅ Files should automatically be 644

---

### STEP 3️⃣: Create Database Tables

1. Login to **Hostinger** → Go to **phpMyAdmin**
2. Click on database: `u536627044_sinta`
3. Click **SQL** tab at the top
4. Paste this code and click **Go**:

```sql
-- Create wardrobes table
CREATE TABLE IF NOT EXISTS `wardrobes_tbl` (
  `wardrobe_id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text,
  `rental_price` decimal(10,2) NOT NULL,
  `availability_count` int(11) NOT NULL,
  `rental_duration_days` int(11) NOT NULL,
  `sizes_available` varchar(255),
  `condition_status` enum('excellent','good','fair','needs_cleaning') DEFAULT 'excellent',
  `image` longblob,
  `image_type` varchar(50),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`wardrobe_id`),
  KEY `idx_category` (`category`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create wardrobe selections table
CREATE TABLE IF NOT EXISTS `wardrobe_selections_tbl` (
  `selection_id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11),
  `user_id` int(11),
  `wardrobe_id` int(11) NOT NULL,
  `quantity_selected` int(11) DEFAULT 1,
  `size_selected` varchar(50),
  `rental_start_date` date,
  `rental_end_date` date,
  `subtotal_price` decimal(10,2),
  `status` enum('pending','confirmed','rented','returned','cancelled') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`selection_id`),
  FOREIGN KEY (`wardrobe_id`) REFERENCES `wardrobes_tbl`(`wardrobe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

✅ This creates the tables needed for wardrobe feature to work

---

### STEP 4️⃣: Test the Fix

Open these URLs in your browser:

**Test 1 - Wardrobe Admin**
```
https://sinta.bsit2a.com/index.php?route=admin-wardrobe
```
❓ Does it load? Can you see a page?
❓ Can you click **Add Wardrobe** button?

**Test 2 - Occasions Admin**
```
https://sinta.bsit2a.com/index.php?route=admin-occasions
```
❓ Does it load? Can you see occasions list?
❓ Can you click **Edit** on an occasion?

**Test 3 - User Experience**
```
https://sinta.bsit2a.com/index.php?route=occasions
```
❓ Does it load? Can you click an occasion?
❓ Does it show wardrobe selection page?

---

### STEP 5️⃣: Run Automated Diagnostic

1. Upload this file to Hostinger: `public/setup-wardrobe-db.php`
2. Visit: `https://sinta.bsit2a.com/setup-wardrobe-db.php`
3. This will:
   - ✅ Check database connection
   - ✅ Verify all tables exist
   - ✅ Check if all files uploaded
   - ✅ Create missing tables automatically
4. After running, **delete this file** for security

---

## 🆘 TROUBLESHOOTING

### Problem: Still getting 404 on `/index.php?route=admin-wardrobe`

**Cause**: `public/index.php` not uploaded correctly

**Fix**:
1. Make sure you uploaded `/public/index.php`
2. Make sure it's not just the admin-wardrobe file
3. Verify the file is exactly the same as local version

**Check**: Visit `/index.php?route=admin-occasions` - if this works, it means routing is OK

### Problem: "Table wardrobes_tbl doesn't exist" error

**Cause**: Database tables not created

**Fix**:
1. Go to phpMyAdmin in Hostinger
2. Run the SQL code from STEP 3️⃣ above
3. This creates the missing tables

### Problem: Can't edit occasions (no form appears)

**Cause**: `public/api-occasion.php` not uploaded OR not updated

**Fix**:
1. Make sure you uploaded `/public/api-occasion.php`
2. Open the file in File Manager and check it has "action=update" in it
3. If not, re-upload with the latest version from your local folder

### Problem: Images not showing when viewing wardrobes

**Cause**: `public/api-wardrobe-image.php` missing

**Fix**:
1. Verify you uploaded: `/public/api-wardrobe-image.php`
2. Check file permissions are 644
3. Try re-uploading this file specifically

---

## ✅ VERIFICATION CHECKLIST

After completing all steps, verify:

- [ ] All files uploaded to `/public_html/`
- [ ] Permissions set (folders 755, files 644)
- [ ] Database tables created in phpMyAdmin
- [ ] Can visit `/index.php?route=admin-wardrobe` ✓
- [ ] Can visit `/index.php?route=admin-occasions` ✓
- [ ] Can click Edit on an occasion ✓
- [ ] Can add a new wardrobe ✓
- [ ] Can upload wardrobe image ✓

---

## 📞 Still Not Working?

If after these steps it's still not working, please provide:

1. **Screenshot** of the error you see
2. **URL** you're visiting when it fails
3. **Error message** (if any) from the page
4. **Confirmation** that:
   - [ ] You uploaded all files
   - [ ] You set permissions to 755
   - [ ] You created database tables
   - [ ] You deleted the setup-wardrobe-db.php file

---

## ⏱️ Expected Time

- Upload files: 5-10 minutes
- Set permissions: 2-3 minutes  
- Create database tables: 1 minute
- Testing: 5 minutes
- **Total**: ~20 minutes

---

## 🔐 Security Note

After you're done:
1. **Delete**: `public/setup-wardrobe-db.php` (if you uploaded it)
2. This prevents unauthorized access to setup page

---

## 📝 Files Summary

**Total files to handle**: ~20 files

**Critical files** (if ANY of these missing, features won't work):
- `/public/index.php` ← Has all routes
- `/public/api-wardrobe.php` ← API for wardrobe data
- `/public/api-occasion.php` ← API for occasions (with edit/update)
- `/app/models/Wardrobe.php` ← Database model
- `/app/models/Occasion.php` ← Occasion model
- `/app/views/admin/admin-wardrobe.php` ← Wardrobe management page
- `/app/views/admin/admin-occasions.php` ← Occasions edit page

---

**Questions?** Check the detailed `HOSTINGER_UPLOAD_CHECKLIST.md` file for more information.
