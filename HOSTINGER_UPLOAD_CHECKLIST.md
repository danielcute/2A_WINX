# ⚠️ CRITICAL: Complete Hostinger Upload Checklist
**Last Updated**: May 31, 2026  
**Status**: ❌ Issues detected - Wardrobe and Occasions not working on live server

---

## 📋 STEP 1: VERIFY FILES TO UPLOAD

### ✅ Wardrobe Feature Files (MUST UPLOAD)
**File Manager Path**: Upload to `/public_html/`

#### A. Models Layer
```
app/models/Wardrobe.php  ← CRITICAL
```

#### B. Controllers Layer  
```
app/controllers/WardrobeController.php              ← CRITICAL
app/controllers/WardrobeSelectionController.php     ← CRITICAL
```

#### C. Views Layer - Admin
```
app/views/admin/admin-wardrobe.php                  ← CRITICAL
app/views/admin/admin-wardrobe-add.php              ← CRITICAL
app/views/admin/admin-wardrobe-edit.php             ← CRITICAL
app/views/admin/admin-wardrobe-selections.php       ← CRITICAL
```

#### D. Views Layer - User
```
app/views/user/wardrobe.php                         ← CRITICAL
app/views/user/wardrobe-selection.php               ← CRITICAL
```

#### E. API Endpoints
```
public/api-wardrobe.php                             ← CRITICAL
public/api-wardrobe-image.php                       ← CRITICAL
public/api-wardrobe-update.php                      ← CRITICAL
public/api-wardrobe-selections.php                  ← CRITICAL
```

### ✅ Occasions Feature Files (MUST UPLOAD)
```
app/models/Occasion.php                             ← MUST UPDATE
app/controllers/OccasionController.php              ← MUST UPDATE
app/views/admin/admin-occasions.php                 ← MUST UPDATE
public/api-occasion.php                             ← MUST UPDATE
```

### ✅ Core Routing (MUST UPLOAD)
```
public/index.php  ← Contains ALL wardrobe routes (lines 701-761) and occasions routes
```

---

## 📤 STEP 2: HOW TO UPLOAD VIA HOSTINGER FILE MANAGER

### Option A: Upload Individual Files
1. Open **Hostinger File Manager** → Navigate to `public_html/`
2. For each file above:
   - Right-click → **Upload File**
   - Select from your local `c:\xampp\htdocs\SINTA\` folder
   - Wait for upload to complete

### Option B: Upload Folder (Recommended & Faster)
1. In Hostinger File Manager, navigate to `public_html/`
2. Click **Upload Folder**
3. Select folders from local machine:
   - `c:\xampp\htdocs\SINTA\app\` → uploads entire folder
   - `c:\xampp\htdocs\SINTA\public\` → uploads entire public folder
4. This replaces existing files with updated versions

---

## 🔐 STEP 3: FILE PERMISSIONS (AFTER UPLOAD)

After uploading, set correct permissions:

### For Folders
1. In Hostinger: Right-click folder → **Change Permissions**
2. Set to: **755** (Read/Write/Execute for owner, Read/Execute for others)
3. Apply to: `/app/`, `/public/`, `/config/`

### For Files
- Should be **644** automatically (Read/Write for owner, Read for others)
- If not, fix via: Right-click file → **Change Permissions** → Set to **644**

---

## 🗄️ STEP 4: DATABASE SETUP (CRITICAL!)

The wardrobe feature requires these tables on your live database.

### Check if Tables Exist
1. Use **Hostinger phpMyAdmin** or similar database manager
2. Connect to: `u536627044_sinta` database
3. Look for these tables:
   - `wardrobes_tbl` ← Should exist
   - `wardrobe_selections_tbl` ← Should exist
   - `occasions_tbl` ← Should exist (already have this)

### If Tables DON'T Exist, Create Them
**Database User**: `u536627044_sinta`  
**Database**: `u536627044_sinta`

Run these SQL commands in phpMyAdmin (SQL tab):

```sql
-- Create wardrobes_tbl if it doesn't exist
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

-- Create wardrobe_selections_tbl if it doesn't exist
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

---

## ✅ STEP 5: VERIFY DEPLOYMENT

After uploading and setting permissions, test:

### Test Wardrobe Feature
1. Visit: `https://sinta.bsit2a.com/index.php?route=admin-wardrobe`
2. Should show Admin Wardrobe page (not 404)
3. Should be able to:
   - Add a wardrobe ✓
   - Edit a wardrobe ✓
   - Delete a wardrobe ✓
   - Upload images ✓

### Test Occasions Edit
1. Visit: `https://sinta.bsit2a.com/index.php?route=admin-occasions`
2. Should show Occasions list
3. Click any **Edit** button → Should open edit form
4. Make changes → Should save successfully ✓

### Test User Experience
1. Visit: `https://sinta.bsit2a.com/index.php?route=occasions`
2. Click an occasion → Should proceed to customize
3. Should show wardrobe selection step before checkout ✓

---

## 🛠️ TROUBLESHOOTING

### Issue: Still getting 404 on wardrobe page
**Solution**: 
- Verify `public/index.php` uploaded correctly
- Check if route `?route=admin-wardrobe` exists (should be around line 701)
- Verify file permissions are 644 for PHP files

### Issue: Can't edit occasions
**Solution**:
- Verify `public/api-occasion.php` uploaded
- Check if it handles POST requests with `?action=update`
- Verify admin session is active (login first)

### Issue: Wardrobe images not showing
**Solution**:
- Verify `public/api-wardrobe-image.php` uploaded
- Check database has `wardrobes_tbl` with image column
- Try uploading a test image via admin

### Issue: "Table doesn't exist" error
**Solution**:
- Run SQL commands in Step 4 above
- This creates missing database tables

---

## 📞 NEXT STEPS

1. ✅ Upload all files listed in STEP 1
2. ✅ Set permissions in STEP 3 (folders 755, files 644)
3. ✅ Create database tables using STEP 4 SQL commands
4. ✅ Test using STEP 5 checklist
5. ✅ Report any errors you see

**Once you upload and test, reply with:**
- ✅ Did wardrobe page load?
- ✅ Could you add/edit a wardrobe?
- ✅ Could you edit an occasion?
- ❌ Any error messages?

---

## 📋 FILE INVENTORY CHECKLIST

Print this and use as you upload:

**App Models**
- [ ] `app/models/Wardrobe.php`
- [ ] `app/models/Occasion.php`

**Controllers**
- [ ] `app/controllers/WardrobeController.php`
- [ ] `app/controllers/WardrobeSelectionController.php`
- [ ] `app/controllers/OccasionController.php`

**Admin Views**
- [ ] `app/views/admin/admin-wardrobe.php`
- [ ] `app/views/admin/admin-wardrobe-add.php`
- [ ] `app/views/admin/admin-wardrobe-edit.php`
- [ ] `app/views/admin/admin-wardrobe-selections.php`
- [ ] `app/views/admin/admin-occasions.php`

**User Views**
- [ ] `app/views/user/wardrobe.php`
- [ ] `app/views/user/wardrobe-selection.php`

**API Endpoints**
- [ ] `public/api-wardrobe.php`
- [ ] `public/api-wardrobe-image.php`
- [ ] `public/api-wardrobe-update.php`
- [ ] `public/api-wardrobe-selections.php`
- [ ] `public/api-occasion.php`

**Core**
- [ ] `public/index.php` (MUST include wardrobe routes)
- [ ] `config/database.php`

---

**Total Files to Upload**: ~20 files  
**Estimated Upload Time**: 5-10 minutes via file manager  
**Expected Result**: ✅ Wardrobe feature working + Occasions editable
