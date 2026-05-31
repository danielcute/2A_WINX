# Wardrobe Rental System - Admin Guide

## Overview
The SINTA wardrobe rental system is designed to manage formal attire rentals for various events. Users can browse and select from a comprehensive collection of rental wardrobes, while admins have full control over the inventory.

## System Architecture

### Database Structure

#### wardrobes_tbl
Stores all available rental wardrobe items.

**Key Fields:**
- `wardrobe_id` - Unique identifier
- `category` - Event type (Wedding, Birthday, Corporate Gala, Debut, Anniversary)
- `name` - Wardrobe item name
- `description` - Item details
- `rental_price` - Cost per rental period
- `availability_count` - Number of items available for rent
- `rental_duration_days` - Default rental period (days)
- `sizes_available` - Available sizes (e.g., XS,S,M,L,XL)
- `condition_status` - Condition tracking (excellent, good, fair, needs_cleaning)
- `is_active` - Soft delete flag (1 = active, 0 = deleted)

#### wardrobe_selections_tbl
Tracks all user wardrobe selections and rentals.

**Key Fields:**
- `selection_id` - Unique identifier
- `plan_id` - Associated booking/plan
- `user_id` - Customer who made the selection
- `wardrobe_id` - Item selected
- `quantity_selected` - Number of items
- `size_selected` - Chosen size
- `rental_start_date` - Rental start
- `rental_end_date` - Rental end
- `subtotal_price` - Cost for this selection
- `status` - pending, confirmed, rented, returned, cancelled
- `selection_notes` - Additional notes

## Admin Features

### 1. Add New Wardrobe Item

**Path:** Admin Dashboard → Wardrobe Management → Add Wardrobe

**Required Fields:**
- **Category** - Select from: Wedding, Birthday, Corporate Gala, Debut, Anniversary
- **Name** - Item name (e.g., "Classic Bride Gown")
- **Description** - Details about the item
- **Rental Price** - Cost per rental period (must be positive)
- **Availability Count** - How many units available (minimum 1)
- **Rental Duration Days** - Default rental period
- **Sizes Available** - Comma-separated sizes (e.g., XS,S,M,L,XL)

**Example:**
```
Category: Wedding
Name: Luxury Couture Bride
Description: Premium designer bridal gown with intricate beading
Rental Price: 200
Availability Count: 3
Rental Duration: 3 days
Sizes: S,M,L
```

### 2. Edit Wardrobe Item

**Path:** Admin Dashboard → Wardrobe Management → Select Item → Edit

You can modify:
- Category
- Name
- Description
- Rental Price
- Availability Count
- Rental Duration
- Sizes Available

**Note:** Soft delete is used - items are marked inactive rather than permanently deleted.

### 3. Delete Wardrobe Item

**Path:** Admin Dashboard → Wardrobe Management → Select Item → Delete

- Items are soft-deleted (marked as inactive)
- Existing selections remain in database for history
- Item can be reactivated if needed

### 4. View Wardrobe Inventory

**Path:** Admin Dashboard → Wardrobe Management

**Display Information:**
- All active wardrobe items organized by category
- Item details (price, availability, sizes)
- Condition status
- Quick actions: Edit, Delete, View Details

### 5. Track Rental Selections

**Path:** Admin Dashboard → Wardrobe → Selections

**View:**
- All user wardrobe selections
- Associated booking/plan information
- Rental dates
- Selection status
- Rental costs

## Current Wardrobe Inventory

### Categories & Item Count

1. **Wedding** (25 items)
   - Bride gowns (10 styles)
   - Groom suits (5 styles)
   - Bridesmaid/Groomsmen packs
   - Family attire (mother, father, ring bearer, flower girl)

2. **Birthday** (20 items)
   - Party dresses and suits
   - Themed costumes (superhero, princess, pirate, wizard, etc.)
   - Character outfits
   - Various size ranges

3. **Corporate Gala** (22 items)
   - Evening gowns (11 styles)
   - Business formal suits (5 styles)
   - Accessories (ties, shirts, cummerbunds)
   - Multiple price points

4. **Debut** (22 items)
   - Debutante ball gowns
   - Modern debut dresses
   - Escort formal wear
   - Court attendant dresses
   - Formal accessories

5. **Anniversary** (18 items)
   - Formal gowns
   - Business suits
   - Romantic dress options
   - Formal accessories

**Total: 107 wardrobe rental items**

## Best Practices

### Inventory Management
1. **Update Availability Regularly**
   - Decrease count when renting out
   - Increase when items are returned
   - Track condition for maintenance planning

2. **Price Management**
   - Review rental prices quarterly
   - Consider demand vs. condition
   - Offer bundled pricing for multiple items

3. **Size Availability**
   - Ensure diverse size ranges for inclusivity
   - XS through XXL for adults
   - Children sizes: 2T, 3T, 4T, 5T, 6T, 7T, 8T
   - "One Size" for accessories/costumes

### User Experience
1. **Category Organization**
   - Keep categories clear and distinct
   - Add new categories as needed for new event types
   - Use consistent naming

2. **Detailed Descriptions**
   - Include style details (lace, beading, etc.)
   - Mention color options if available
   - Note any special features

3. **Clear Pricing**
   - Display rental period with price
   - Show total cost calculations
   - Include any additional fees

## API Endpoints

### Admin Operations

**Add Wardrobe:**
```
POST /admin-wardrobe-add
Parameters: category, name, description, rental_price, availability_count, rental_duration_days, sizes_available
```

**Edit Wardrobe:**
```
POST /admin-wardrobe-update
Parameters: wardrobe_id, category, name, description, rental_price, availability_count, rental_duration_days, sizes_available
```

**Delete Wardrobe:**
```
POST /admin-wardrobe-delete
Parameters: wardrobe_id
```

**Get All Wardrobes:**
```
GET /api-wardrobe.php?action=getAll
```

**Get By Category:**
```
GET /api-wardrobe.php?action=getByCategory&category=Wedding
```

### User Operations

**View All Wardrobes:**
```
GET /api-wardrobe.php?action=getAll
```

**Search Wardrobes:**
```
GET /api-wardrobe.php?action=search&q=bride
```

**Get Categories:**
```
GET /api-wardrobe.php?action=getCategories
```

**Save Selection:**
```
POST /api-wardrobe-selections.php?action=save
Parameters: plan_id, wardrobe_id, quantity, size, start_date, end_date, subtotal, notes
```

**Get Plan Selections:**
```
GET /api-wardrobe-selections.php?action=getByPlan&plan_id={id}
```

**Check Availability:**
```
GET /api-wardrobe-selections.php?action=checkAvailability&wardrobe_id={id}&start_date={date}&end_date={date}&quantity=1
```

## Troubleshooting

### Issue: Items not showing in user interface
- Check `is_active` flag in wardrobes_tbl
- Verify category is correctly set
- Ensure availability_count > 0

### Issue: Rental price calculations incorrect
- Verify subtotal_price in wardrobe_selections_tbl
- Check rental_price in wardrobes_tbl
- Confirm quantity and duration calculations

### Issue: Size selections not available
- Check sizes_available field format
- Ensure comma-separated values (no spaces)
- Verify size matches what users are requesting

## Future Enhancements

Potential improvements to consider:
1. Image uploads for wardrobe items
2. Color/style variants
3. Seasonal availability management
4. Damage tracking and repair logs
5. Rental history reports
6. Customer reviews for items
7. Wishlist/favorites feature
8. Automatic availability updates based on rental dates
9. Size recommendation engine
10. Bundle pricing for multiple items

## Contact & Support

For issues or questions:
- Check the system logs in the database
- Review the Wardrobe model for technical details
- Contact development team for feature requests
