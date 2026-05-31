# Wardrobe Rental System - Architecture Diagram

## System Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                      SINTA WARDROBE RENTAL SYSTEM                       │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────┐                        ┌──────────────────────┐
│   USER INTERFACE    │                        │  ADMIN INTERFACE     │
├─────────────────────┤                        ├──────────────────────┤
│ • Browse Wardrobes  │                        │ • Add Wardrobe       │
│ • View by Category  │                        │ • Edit Wardrobe      │
│ • Search Wardrobes  │                        │ • Delete Wardrobe    │
│ • Select for Rental │                        │ • View All Items     │
│ • Choose Dates/Size │                        │ • Track Selections   │
│ • Calculate Cost    │                        │ • Manage Inventory   │
└──────────┬──────────┘                        └──────────┬───────────┘
           │                                              │
           │ (READ-ONLY)                                 │ (FULL CRUD)
           │                                              │
           └──────────────┬───────────────────────────────┘
                          │
                    ┌─────▼──────┐
                    │  BACKEND   │
                    │ CONTROLLERS│
                    └─────┬──────┘
                          │
        ┌─────────────────┼─────────────────┐
        │                 │                 │
   ┌────▼────┐      ┌─────▼──────┐    ┌────▼──────┐
   │Wardrobe │      │   Admin    │    │ Selection │
   │Controller       │ Wardrobe  │    │Controller │
   └────┬────┘      │Controller │    └────┬──────┘
        │           └─────┬─────┘         │
        │                 │               │
        └─────────────────┼───────────────┘
                          │
                    ┌─────▼──────────────────┐
                    │   DATABASE MODELS      │
                    │  (Wardrobe Class)      │
                    └─────┬──────────────────┘
                          │
        ┌─────────────────┼─────────────────┐
        │                 │                 │
   ┌────▼──────┐   ┌──────▼──────┐   ┌────▼──────────┐
   │wardrobes_ │   │wardrobe_    │   │  OPERATIONS   │
   │tbl        │   │selections_  │   │               │
   ├─────────── │   │tbl          │   ├───────────────┤
   │ wardrobe_ │   ├────────────  │   │ • Create      │
   │ id (PK)   │   │ selection_id │   │ • Read        │
   │ category  │   │ (PK)         │   │ • Update      │
   │ name      │   │ plan_id (FK) │   │ • Delete      │
   │ desc      │   │ user_id (FK) │   │ • Search      │
   │ price     │   │ wardrobe_id  │   │ • Filter      │
   │ available │   │ quantity     │   │ • Check Avail │
   │ duration  │   │ size         │   │ • Track Cost  │
   │ sizes     │   │ start_date   │   │               │
   │ condition │   │ end_date     │   │               │
   │ is_active │   │ subtotal     │   │               │
   │ timestamps│   │ status       │   │               │
   │           │   │ notes        │   │               │
   │           │   │ timestamps   │   │               │
   └───────────┘   └─────────────┘   └───────────────┘
```

## Data Flow

### User Selection Flow
```
User accesses wardrobe page
        ↓
getCategories() API
        ↓
Display categories
        ↓
User selects category
        ↓
getByCategory() API
        ↓
Display wardrobes in category
        ↓
User searches/filters (optional)
        ↓
search() API
        ↓
User selects wardrobe item
        ↓
checkAvailability()
        ↓
User confirms rental details
├─ Quantity
├─ Size
├─ Rental dates
└─ Special notes
        ↓
saveSelection() API
        ↓
INSERT wardrobe_selections_tbl
        ↓
✓ Selection stored in database
        ↓
Selection added to user's plan
```

### Admin Wardrobe Management Flow
```
Admin accesses wardrobe management
        ↓
listAll() → Display all wardrobes
        ↓
Admin can:
├─ Click "Add" → addForm()
│  ├─ Enter details (category, name, price, etc.)
│  └─ Submit → add() → INSERT wardrobes_tbl
│
├─ Click "Edit" → editForm(id)
│  ├─ Display form with current data
│  └─ Submit → update() → UPDATE wardrobes_tbl
│
└─ Click "Delete" → delete() 
   └─ UPDATE is_active = 0 (soft delete)
        ↓
✓ Wardrobe catalog updated
```

## Category Structure

```
WARDROBE_TBL (107 items total)
│
├─ WEDDING (25 items)
│  ├─ Bride Gowns (10)
│  │  ├─ Classic, Modern, Luxury, Romantic, Off-shoulder, Vintage...
│  │  └─ Prices: $140-200, 3-day rental
│  │
│  ├─ Groom Suits (5)
│  │  ├─ Formal, Designer, Navy, White, Burgundy
│  │  └─ Prices: $100-130, 3-day rental
│  │
│  └─ Family & Attendants (10)
│     ├─ Bridesmaid dress packs, Groomsmen suits
│     ├─ Mother/Father of bride/groom, Ring bearer, Flower girl
│     └─ Prices: $50-180, 2-3 day rental
│
├─ BIRTHDAY (20 items)
│  ├─ Dresses & Party Wear (8)
│  │  ├─ Birthday Party Dress, Kids Birthday Outfit
│  │  ├─ Adult Casual Party, Teen Party Dress
│  │  └─ Prices: $40-70, 1-day rental
│  │
│  └─ Themed Costumes (12)
│     ├─ Superhero, Princess, Pirate, Knight, Fairy
│     ├─ Dinosaur, Mermaid, Wizard/Witch, Movie Characters
│     └─ Prices: $45-70, 1-day rental
│
├─ CORPORATE GALA (22 items)
│  ├─ Evening Gowns (11)
│  │  ├─ Black Tie, Red, Navy, Gold, Silver, Black, Blush, etc.
│  │  └─ Prices: $110-140, 1-day rental
│  │
│  └─ Business Suits & Accessories (11)
│     ├─ Black, Navy, Charcoal, Two-piece blazer sets
│     ├─ Dress shirts, pants, bow ties, ties, cummerbunds
│     └─ Prices: $15-110, 1-day rental
│
├─ DEBUT (22 items)
│  ├─ Debutante Gowns (11)
│  │  ├─ Ball Gown, Modern, Vintage, Ivory, Blush, Gold, Crystal
│  │  └─ Prices: $160-200, 2-day rental
│  │
│  └─ Escorts & Attendants (11)
│     ├─ Black Tuxedo, Navy Suit, White Tuxedo, Formal Gowns
│     ├─ Court Attendant dresses, Gloves, Tiaras, Shoes
│     └─ Prices: $15-140, 2-day rental
│
└─ ANNIVERSARY (18 items)
   ├─ Formal Gowns (9)
   │  ├─ Renewal of Vows, Party Dress, Gold, Silver, Red, Black
   │  ├─ Diamond White, Blush Pink, Champagne
   │  └─ Prices: $75-130, 1-day rental
   │
   └─ Formal Suits & Accessories (9)
      ├─ Black, Navy, Gray suits, Wine-toned suit
      ├─ Dress shirts, Neckties, Boutonnieres, Cummerbunds
      └─ Prices: $15-105, 1-day rental
```

## API Endpoint Map

```
┌──────────────────────────────────────────────────────────┐
│              API ENDPOINTS                               │
├──────────────────────────────────────────────────────────┤
│                                                          │
│ PUBLIC - WARDROBE BROWSING                             │
│ ├─ GET  /api-wardrobe.php?action=getAll                │
│ ├─ GET  /api-wardrobe.php?action=getByCategory&cat=X   │
│ ├─ GET  /api-wardrobe.php?action=search&q=X            │
│ └─ GET  /api-wardrobe.php?action=getCategories         │
│                                                          │
│ USER - WARDROBE SELECTION                              │
│ ├─ POST /api-wardrobe-selections.php?action=save       │
│ ├─ GET  /api-wardrobe-selections.php?action=getByPlan  │
│ ├─ GET  /api-wardrobe-selections.php?action=getByUser  │
│ ├─ POST /api-wardrobe-selections.php?action=update     │
│ ├─ POST /api-wardrobe-selections.php?action=delete     │
│ └─ GET  /api-wardrobe-selections.php?action=checkAvail │
│                                                          │
│ ADMIN - WARDROBE MANAGEMENT                            │
│ ├─ GET  /index.php?route=admin-wardrobe                │
│ ├─ GET  /index.php?route=admin-wardrobe-add            │
│ ├─ POST /index.php?route=admin-wardrobe-add            │
│ ├─ GET  /index.php?route=admin-wardrobe-edit?id=X      │
│ ├─ POST /index.php?route=admin-wardrobe-update         │
│ ├─ POST /index.php?route=admin-wardrobe-delete         │
│ └─ GET  /index.php?route=admin-wardrobe-selections     │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

## Selection Lifecycle

```
PENDING
  ↓
User selects wardrobe for event
- Stored in database with status = 'pending'
- User can modify or delete at this stage
  ↓
CONFIRMED
  ↓
Admin confirms/approves selection
  ↓
RENTED
  ↓
Wardrobe item shipped to user
  ↓
RETURNED
  ↓
Item received back from user
- Condition checked
- Availability count updated
  ↓
CANCELLED (Optional branch)
  ↓
Selection cancelled before rental
- Either user or admin cancellation
- Availability not affected
```

## Size & Availability Matrix

```
Wardrobe Item: Classic Bride Gown
├─ Total Available: 5 units
├─ Sizes Available: XS, S, M, L, XL
│  ├─ XS: 1 unit available
│  ├─ S: 1 unit available
│  ├─ M: 1 unit available
│  ├─ L: 1 unit available
│  └─ XL: 1 unit available
│
└─ During Date Range Check (2025-06-01 to 2025-06-15)
   ├─ Confirmed Rentals: 2 units
   ├─ Rented Units: 1 unit
   └─ Available for New Rental: 2 units ✓ (sufficient)
```

---

**System Status: ✅ FULLY OPERATIONAL**
