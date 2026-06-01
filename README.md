# SINTA - Event Planning & Wardrobe Rental System

A comprehensive Event Planning and Wardrobe Rental System developed using PHP and MySQL.  
This system allows customers to book events with custom themes and wardrobes, while providing an administrative dashboard for managing inventory, bookings, and system health.

---

# Table of Contents
- [Installation](#installation)
- [Usage](#usage)
- [Features](#features)
- [Screenshots](#screenshots)
- [Folder Structure](#folder-structure)
- [Developers](#developers)
- [License](#license)

---

# Installation

## Requirements
- XAMPP
- PHP 7.4+ (with GD Library for image processing)
- MySQL
- Web Browser

## Steps to Run

1. Download or clone the repository.
2. Copy the project folder into your XAMPP `htdocs` folder.
3. Start Apache and MySQL in the XAMPP Control Panel.
4. Open phpMyAdmin.
5. Create a database named:

```plaintext
u536627044_sinta
```

6. Import the provided SQL files (check the `database/` or `migrations/` folder).
7. Open your browser and type:

```plaintext
http://localhost/SINTA/public/index.php
```

---

# Usage

## Admin Account

**Username:** admin  
**Password:** admin123  
*(Note: Some modules may require 2FA verification)*

## How to Use

1. **Login:** Use the admin credentials to access the dashboard or create a user account via Signup.
2. **Browse Occasions:** Select from Weddings, Birthdays, Corporate events, etc.
3. **Customize:** Use the Custom Color Picker and Sweets Station selection.
4. **Wardrobe Rental:** Choose event-specific attire from the wardrobe catalog.
5. **Agreement:** Review and accept the Booking Agreement and Cancellation Policy before confirming.
6. **Manage (Admin):** Admins can update wardrobe stock, monitor database health, and manage event schedules.

---

# Features
- **User Authentication:** Secure Login/Signup with optional 2FA verification.
- **Wardrobe Management:** Categorized inventory system for event rentals.
- **Booking Agreement Modal:** Legally compliant terms and cancellation fee tiers.
- **Custom Color Picker:** Interactive tool to select personalized event palettes.
- **Mobile Responsive:** Fully optimized for desktop, tablet, and hybrid mobile apps.
- **Database Health Monitor:** Real-time tracking of system table status.
- **Automated Receipts:** Generation of partial and full payment receipts.

---

# Folder Structure

```plaintext
SINTA/
├── app/               # MVC Core (Models, Views, Controllers)
├── assets/            # CSS, JS, and Static Images
├── config/            # Database and System Configuration
├── public/            # Entry point (index.php) and API endpoints
├── README.md          # Project Documentation
└── vendor/            # Third-party libraries (Google2FA, BaconQrCode)
```

---

# Developers
- **BSIT 2A Students**
- Group Project

---

# License

This project is for educational purposes only.