# Custom Color Picker & Sweets Section - User Guide

## For Users

### Selecting a Custom Color Combination

1. **Navigate to Customize Page**
   - After selecting your occasion (Wedding, Debut, Birthday, etc.)
   - Click on "Customize Your Event"

2. **Color Palette Section**
   - You'll see section 2: "Choose Your Color Palette"
   - View preset color combinations or select "Other" to create custom

3. **Opening the Color Picker Modal**
   - Click on the "Other" card in the color palette section
   - A modal dialog will open with a full color palette

4. **Selecting Your Colors**
   - Browse the 20+ available colors:
     - Warm tones: Rose Gold, Blush Pink, Gold, Champagne, Burgundy, Coral, Peach
     - Cool tones: Ocean Blue, Navy Blue, Emerald, Sage Green, Lavender, Mint Green
     - Neutral tones: Ivory, White, Cream, Taupe, Silver
   - **Click a color button** to select it (button will be highlighted with a border)
   - **Click again** to deselect it
   - **Minimum requirement**: Select at least 3 colors
   - **Maximum allowed**: Select up to 5 colors
   - A counter shows your current selection: "Selected Colors: X/5"

5. **Optional: Add Description**
   - In the "Optional: Add Description" field
   - Write any notes about your preferred style
   - Example: "Rose gold accents with ivory..."
   - This is completely optional

6. **Confirming Your Selection**
   - Click "Confirm Selection" button
   - Modal closes and your custom colors are saved
   - The "Other" card now shows your selected colors

7. **Sweets Station (Optional)**
   - Section 5: "Add Sweets Station"
   - Marked as **(Optional)** - you don't have to select one
   - Choose one if you want sweets at your event
   - Options include:
     - Chocolate Fountain Station (₱12,000)
     - Candy Bar Setup (₱8,000)
     - Macarons & Petit Fours (₱10,000)
     - Donut Wall (₱9,000)

8. **Proceed to Checkout**
   - After selecting all your preferences
   - Click "Proceed to Checkout"
   - Your custom colors and optional sweets will be included in your order
   - Total price will be calculated with all selections

---

## For Admin Users

### Retrieving Custom Color Information

#### Via API
```
GET /SINTA/public/api-custom-colors.php?action=get&plan_id=123

Response:
{
  "success": true,
  "data": {
    "colors": [
      {"name": "Rose Gold", "hex": "#B76E79"},
      {"name": "Ivory", "hex": "#FFFFF0"},
      {"name": "Gold", "hex": "#FFD700"}
    ],
    "description": "Rose Gold + Ivory + Gold - Elegant and romantic"
  },
  "colors_display": [...]
}
```

#### In Booking Details
Custom colors are stored in the booking events JSON with structure:
```json
{
  "items": [
    {
      "category": "Color Combinations",
      "name": "Other",
      "customColors": "[{\"name\":\"Rose Gold\",\"hex\":\"#B76E79\"},...]",
      "customDescription": "Rose Gold + Ivory + Gold - Elegant and romantic"
    }
  ]
}
```

### Viewing Sweets Selection
In the booking/plan details, sweets selection will appear as:
```json
{
  "category": "Sweets",
  "name": "Chocolate Fountain Station",
  "price": 12000
}
```

---

## Color Options Available

| Warm Tones | Cool Tones | Neutral Tones |
|-----------|-----------|--------------|
| Rose Gold (#B76E79) | Ocean Blue (#006994) | Ivory (#FFFFF0) |
| Blush Pink (#FFC0CB) | Navy Blue (#000080) | White (#FFFFFF) |
| Gold (#FFD700) | Emerald (#50C878) | Cream (#FFFDD0) |
| Champagne (#F7E7CE) | Sage Green (#9DC183) | Taupe (#B38B6D) |
| Burgundy (#800020) | Lavender (#E6E6FA) | Silver (#C0C0C0) |
| Coral (#FF7F50) | Mint Green (#98FF98) | |
| Peach (#FFDAB9) | Rose Pink (#FF007F) | |
| Gold Accent (#DAA520) | | |

---

## Pricing

### Color Combination
- **Preset combinations**: ₱10,000 - ₱15,000 each
- **Custom combination**: ₱5,000
- **Formula**: Your custom combination = 5 colors × ₱1,000 each

### Sweets Station (Optional)
- Chocolate Fountain Station: ₱12,000
- Candy Bar Setup: ₱8,000
- Macarons & Petit Fours: ₱10,000
- Donut Wall: ₱9,000

---

## Frequently Asked Questions

**Q: Do I have to choose a custom color combination?**
A: No! You can choose one of the preset combinations or skip the color palette entirely. Custom colors are completely optional.

**Q: Can I select fewer than 3 colors?**
A: No, minimum 3 colors required for custom combinations. You'll see a message if you try to confirm with fewer.

**Q: Can I select more than 5 colors?**
A: No, maximum 5 colors allowed. The system will prevent you from selecting the 6th color.

**Q: Can I change my selection after confirming?**
A: Yes! Click on the "Other" card again to reopen the modal and make changes.

**Q: Is the Sweets Station required?**
A: No! The sweets section is marked as (Optional). You can skip it entirely.

**Q: What if I don't select a sweets option?**
A: No charge will be added if you don't select a sweets station.

**Q: What happens if I select both a preset color combination AND custom colors?**
A: You can only have one color selection active at a time. Selecting a new option will deselect the previous one.

**Q: Can I add custom descriptions to preset color combinations?**
A: No, descriptions are only available for custom color combinations.

---

## Troubleshooting

**Issue**: Color picker modal won't open
- **Solution**: Make sure you clicked on the "Other" card, not a preset color option

**Issue**: Can't select 6th color
- **Maximum is 5 colors** - this is intentional to keep combinations manageable

**Issue**: Custom colors not appearing in checkout
- **Make sure to click "Confirm Selection"** - if you close the modal without confirming, selections won't be saved

**Issue**: Prices not calculating correctly
- **Clear browser cache** - refresh the page and try again
- **Make sure all required fields are selected** (Theme, Venue, Catering)

---

## Contact Support

For issues or questions about:
- Color picker functionality: Technical Support
- Event customization options: Sales Team
- Pricing and packages: Customer Service

---

**Last Updated**: May 23, 2026
**Version**: 1.0
