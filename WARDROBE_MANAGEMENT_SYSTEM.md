# SINTA Wardrobe Management System - Complete Documentation

## System Architecture

The wardrobe management system is **fully centralized in the Admin Panel** with users having view-only access for selection purposes.

---

## Admin Wardrobe Management

### Location & Access
- **URL**: `http://localhost/SINTA/public/index.php?route=admin-wardrobe`
- **Navigation**: Admin Dashboard → Sidebar → "Wardrobe Management"
- **Controller**: `app/controllers/AdminWardrobeController.php`
- **Views**: `app/views/admin/admin-wardrobe-*.php`

### Functionality

#### 1. **View All Wardrobes** (`admin-wardrobe`)
**Features:**
- Display all wardrobes grouped by category
- Show count of items per category
- Display details: Name, Description, Rental Price, Stock, Rental Duration, Sizes
- Edit and Delete buttons for each item
- Add New Wardrobe button

**Fields Displayed:**
```
- Name: Wardrobe item name
- Description: Brief description (truncated in list view)
- Rental Price: Display as badge format (₱ X.XX)
- Stock: Number of available pieces
- Rental Duration: Number of rental days
- Sizes: Available sizes (e.g., XS,S,M,L,XL)
```

#### 2. **Add New Wardrobe** (`admin-wardrobe-add`)
**Form Fields:**
- **Category** (Required, Select dropdown):
  - Wedding
  - Birthday
  - Corporate Gala
  - Debut
  - Anniversary
  - Other Events
  - Custom categories allowed

- **Wardrobe Name** (Required, Text):
  - Max 150 characters
  - Example: "Classic Bride Gown"

- **Description** (Optional, Textarea):
  - Detailed description of the item

- **Rental Price** (Required, Number):
  - Currency: Philippine Peso (₱)
  - Format: Decimal with 2 places
  - Min: 0

- **Available Stock** (Required, Number):
  - Min: 1 piece
  - Represents quantity of this item available

- **Rental Duration** (Required, Number):
  - In days
  - Default: 1 day

- **Available Sizes** (Required, Text):
  - Comma-separated values
  - Example: "XS,S,M,L,XL"
  - Default: "Standard"

**API Endpoint:** `POST /SINTA/public/index.php?route=admin-wardrobe-add`

#### 3. **Edit Wardrobe** (`admin-wardrobe-edit?id=X`)
**Features:**
- All fields pre-populated from database
- Modify any field
- Delete option available on edit form
- Validation same as Add form

**API Endpoint:** `POST /SINTA/public/index.php?route=admin-wardrobe-update`

#### 4. **Delete Wardrobe**
**Options:**
- From wardrobe list: Click "Delete" button with confirmation
- From edit form: Click "Delete" button with confirmation
- Soft delete: Sets `is_active` to 0 (data retained in DB)

**API Endpoint:** `POST /SINTA/public/index.php?route=admin-wardrobe-delete`

#### 5. **View Wardrobe Rentals** (`admin-wardrobe-selections`)
- Track which users have selected which wardrobes
- Monitor rental statuses
- Manage selections

---

## User Wardrobe Selection (View Only)

### Location & Access
- **URL**: `http://localhost/SINTA/public/index.php?route=wardrobe`
- **Access**: After selecting customize options
- **Controller**: `app/controllers/WardrobeController.php`
- **View**: `app/views/user/wardrobe.php`

### Functionality
- **Browse** wardrobes by category
- **Search** for specific items
- **Select** wardrobes for their event planning
- **No Management** - cannot add, edit, or delete

---

## Database Schema

### `wardrobes_tbl`
```sql
- wardrobe_id (INT, AUTO_INCREMENT, PRIMARY KEY)
- category (VARCHAR(100), NOT NULL)
- name (VARCHAR(150), NOT NULL)
- description (TEXT)
- rental_price (DECIMAL(10,2), DEFAULT 0)
- availability_count (INT, DEFAULT 1)
- rental_duration_days (INT, DEFAULT 1)
- sizes_available (VARCHAR(255), DEFAULT 'Standard')
- condition_status (ENUM: excellent/good/fair/needs_cleaning, DEFAULT 'excellent')
- image (LONGBLOB) - For future image storage
- image_type (VARCHAR(50))
- is_active (TINYINT, DEFAULT 1)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, AUTO_UPDATE)

INDEXES:
- idx_category (category)
- idx_active (is_active)
```

### `wardrobe_selections_tbl`
```sql
- selection_id (INT, AUTO_INCREMENT, PRIMARY KEY)
- plan_id (INT, NOT NULL)
- user_id (INT, NOT NULL)
- wardrobe_id (INT, NOT NULL, FK → wardrobes_tbl)
- quantity_selected (INT, DEFAULT 1)
- size_selected (VARCHAR(50))
- rental_start_date (DATE)
- rental_end_date (DATE)
- subtotal_price (DECIMAL(10,2), DEFAULT 0)
- selection_notes (TEXT)
- status (ENUM: pending/confirmed/rented/returned/cancelled, DEFAULT 'pending')
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, AUTO_UPDATE)

INDEXES:
- idx_plan, idx_user, idx_wardrobe, idx_status
```

---

## Controller Methods Reference

### AdminWardrobeController (`app/controllers/AdminWardrobeController.php`)

```php
listAll()              // Display all wardrobes
addForm()              // Show add wardrobe form
add()                  // Handle add submission (POST)
editForm()             // Show edit form for specific wardrobe
update()               // Handle update submission (POST)
delete()               // Handle delete request (POST)
getAll()               // Return all wardrobes (returns Wardrobe model data)
```

### WardrobeController (`app/controllers/WardrobeController.php`)

```php
index()                // Display wardrobe selection page
getJson()              // API endpoint for AJAX requests
  - action: getAll
  - action: getByCategory
  - action: search
  - action: getCategories
```

---

## Model Methods

### Wardrobe Model (`app/models/Wardrobe.php`)

**CRUD Operations:**
```php
create($data)                  // Create new wardrobe
update($id, $data)             // Update existing wardrobe
delete($id)                    // Soft delete wardrobe
getById($id)                   // Retrieve single wardrobe
getAll()                       // Get all active wardrobes
getAllByCategory()             // Group wardrobes by category
getByCategory($category)       // Get wardrobes in category
getCategories()                // Get unique categories
search($query)                 // Search by name/desc/category
```

**Selection Management:**
```php
saveSelection($data)           // Save user's wardrobe selection
getSelectionsByPlan($plan_id)  // Get selections for specific plan
getSelectionsByUser($user_id)  // Get all user's selections
updateSelection($id, $data)    // Update selection
deleteSelection($id)           // Remove selection
```

**Availability:**
```php
checkAvailability($wardrobe_id, $start, $end, $qty)  // Check if item available
getTotalCost($plan_id)         // Calculate total rental cost
```

---

## Pre-Populated Sample Data

### Categories
- Wedding (25 items)
- Birthday (20 items)
- Corporate Gala (22 items)
- Debut (22 items)
- Anniversary (18 items)

### Sample Items Per Category

**Wedding:**
- Classic Bride Gown - ₱150/rental, 5 in stock
- Groom Formal Suit - ₱100/rental, 6 in stock
- Bridesmaid Dress Pack - ₱180/rental, 5 in stock

**Birthday:**
- Birthday Party Dress - ₱60/rental, 8 in stock
- Princess Dress - ₱65/rental, 7 in stock
- Superhero Costume Set - ₱55/rental, 8 in stock

*... and more items in each category*

---

## Workflow & User Journey

### Admin: Adding a New Wardrobe Item
1. Navigate to Admin Dashboard
2. Click "Wardrobe Management" in sidebar
3. Click "Add Wardrobe" button
4. Fill in form fields:
   - Select category
   - Enter name
   - Add description
   - Set rental price
   - Set available stock quantity
   - Set rental duration
   - Specify sizes
5. Click "Add Wardrobe"
6. Success message displays and returns to list

### Admin: Editing a Wardrobe Item
1. Go to Wardrobe Management
2. Find item in category list
3. Click "Edit" button in Actions column
4. Modify any fields as needed
5. Click "Update Wardrobe"
6. Success message displays and returns to list

### Admin: Deleting a Wardrobe Item
1. Option A: From list view, click "Delete" button
2. Option B: From edit form, click "Delete" button
3. Confirm deletion in dialog
4. Item is soft-deleted (marked inactive)
5. Success message displays

### User: Selecting Wardrobes for Event
1. User logs in and creates event/plan
2. Selects customize options
3. Redirects to "Choose Your Wardrobe" page
4. Browses wardrobes by category
5. Can search for specific items
6. Selects wardrobes needed for their event
7. Proceeds to checkout

---

## Validation & Business Rules

### When Adding/Editing Wardrobes:
- ✅ Category and Name are **required**
- ✅ Rental Price must be **≥ 0**
- ✅ Availability Count must be **≥ 1**
- ✅ Rental Duration Days must be **≥ 1**
- ✅ Sizes field cannot be empty
- ✅ Name max length: 150 characters

### When Checking Availability:
- ✅ System checks against wardrobe_selections for date range
- ✅ Only counts 'confirmed' or 'rented' status selections
- ✅ Available quantity = availability_count - reserved_count

---

## Security & Access Control

- ✅ Admin routes require `$_SESSION['admin_logged_in']` verification
- ✅ User routes require `$_SESSION['user_logged_in']` verification
- ✅ Users cannot access admin wardrobe management
- ✅ Admins cannot use user wardrobe selection interface
- ✅ Soft delete preserves data integrity

---

## API Endpoints Summary

| Route | Method | Purpose | Access |
|-------|--------|---------|--------|
| `admin-wardrobe` | GET | List all wardrobes | Admin |
| `admin-wardrobe-add` | GET/POST | Add new wardrobe | Admin |
| `admin-wardrobe-edit` | GET | Edit form | Admin |
| `admin-wardrobe-update` | POST | Update wardrobe | Admin |
| `admin-wardrobe-delete` | POST | Delete wardrobe | Admin |
| `admin-wardrobe-selections` | GET | View rentals | Admin |
| `wardrobe` | GET | Browse wardrobes | User |
| `wardrobe-selection` | POST | Save selection | User |

---

## Future Enhancement Opportunities

1. **Image Support**: Wardrobe table has image fields (image, image_type) - ready for implementation
2. **Advanced Search**: Filters by price range, size, duration
3. **Bulk Operations**: Add/edit multiple items at once
4. **Seasonal Management**: Mark items as seasonal
5. **Ratings & Reviews**: Customer feedback on wardrobes
6. **Inventory Tracking**: Low stock alerts
7. **Rental History**: Analytics on most/least rented items

---

## File Reference

### Controllers
- `app/controllers/AdminWardrobeController.php` - Admin management
- `app/controllers/WardrobeController.php` - User browsing

### Views
- `app/views/admin/admin-wardrobe.php` - List view
- `app/views/admin/admin-wardrobe-add.php` - Add form
- `app/views/admin/admin-wardrobe-edit.php` - Edit form
- `app/views/admin/admin-wardrobe-selections.php` - Rentals tracking
- `app/views/user/wardrobe.php` - User browse/select

### Models
- `app/models/Wardrobe.php` - All wardrobe operations

### Routes
- `public/index.php` - Lines 670-758 - Wardrobe routing

---

## Summary

✅ **The wardrobe management system is COMPLETE and FULLY FUNCTIONAL:**
- Admin has centralized control over all wardrobes
- Admin can add, edit, and delete wardrobe items with full validation
- Users can browse and select wardrobes without management capabilities
- Database schema properly supports inventory tracking and rental management
- Security controls prevent unauthorized access
- System is ready for production use

