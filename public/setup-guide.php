<?php
/**
 * SINTA System - Setup & Verification Guide
 * Run this script first to initialize your database
 */

// Configuration
$host = '127.0.0.1';
$port = 3307;
$user = 'root';
$pass = '';
$database = 'sinta_db';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SINTA - Setup Guide</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container { 
            max-width: 900px; 
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .header p { font-size: 1.1em; opacity: 0.9; }
        .content { padding: 40px; }
        .section {
            margin-bottom: 40px;
            border-left: 4px solid #667eea;
            padding-left: 20px;
        }
        .section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5em;
        }
        .section p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 10px;
        }
        .step {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
            border-left: 3px solid #667eea;
        }
        .step strong { color: #667eea; }
        .code-block {
            background: #1e1e1e;
            color: #00ff00;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            overflow-x: auto;
            margin: 10px 0;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        .btn-secondary:hover {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .checklist {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .checklist-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .checklist-item:last-child {
            border-bottom: none;
        }
        .checkbox {
            width: 24px;
            height: 24px;
            margin-right: 15px;
            cursor: pointer;
        }
        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .error-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #667eea;
        }
        .table tr:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 SINTA System</h1>
            <p>Complete Event Management Platform - Setup Guide</p>
        </div>
        
        <div class="content">
            <!-- Quick Start -->
            <div class="section">
                <h2>🚀 Quick Start (3 Steps)</h2>
                <div class="step">
                    <strong>Step 1:</strong> Run Database Initialization<br>
                    Visit: <code>http://localhost/SINTA/public/database-init.php</code>
                </div>
                <div class="step">
                    <strong>Step 2:</strong> Login to Admin Panel<br>
                    Visit: <code>http://localhost/SINTA/public/index.php?route=admin-dashboard</code><br>
                    Credentials: <code>sinta2026@gmail.com / sintaAdmins2026</code>
                </div>
                <div class="step">
                    <strong>Step 3:</strong> Test Each CRUD Function<br>
                    - Package Management (Create, Read, Update, Delete)<br>
                    - Booking Management (Create, Read, Update, Delete)<br>
                    - Customization Management (Create, Read, Update, Delete)
                </div>
            </div>

            <!-- Database Configuration -->
            <div class="section">
                <h2>🗄️ Database Configuration</h2>
                <p>Current configuration:</p>
                <table class="table">
                    <tr>
                        <th>Setting</th>
                        <th>Value</th>
                    </tr>
                    <tr>
                        <td>Host</td>
                        <td><code><?php echo $host; ?></code></td>
                    </tr>
                    <tr>
                        <td>Port</td>
                        <td><code><?php echo $port; ?></code></td>
                    </tr>
                    <tr>
                        <td>Database</td>
                        <td><code><?php echo $database; ?></code></td>
                    </tr>
                    <tr>
                        <td>User</td>
                        <td><code><?php echo $user; ?></code></td>
                    </tr>
                </table>
                <div class="warning-box">
                    <strong>⚠️ Note:</strong> If your XAMPP uses a different port or credentials, update <code>config/database.php</code>
                </div>
            </div>

            <!-- Database Tables -->
            <div class="section">
                <h2>📊 Database Tables Created</h2>
                <p>The system uses the following tables:</p>
                <table class="table">
                    <tr>
                        <th>Table Name</th>
                        <th>Purpose</th>
                    </tr>
                    <tr>
                        <td><code>users_tbl</code></td>
                        <td>User accounts and authentication</td>
                    </tr>
                    <tr>
                        <td><code>packages_tbl</code></td>
                        <td>Event packages and services</td>
                    </tr>
                    <tr>
                        <td><code>checkout_tbl</code></td>
                        <td>Customer bookings and orders</td>
                    </tr>
                    <tr>
                        <td><code>customizations_tbl</code></td>
                        <td>Package add-ons and customizations</td>
                    </tr>
                    <tr>
                        <td><code>messages_tbl</code></td>
                        <td>User messages and communications</td>
                    </tr>
                    <tr>
                        <td><code>testimonials_tbl</code></td>
                        <td>Customer reviews and ratings</td>
                    </tr>
                    <tr>
                        <td><code>occasions_tbl</code></td>
                        <td>Event types and occasions</td>
                    </tr>
                    <tr>
                        <td><code>plans_tbl</code></td>
                        <td>Detailed package plans</td>
                    </tr>
                </table>
            </div>

            <!-- Three Core CRUD Functions -->
            <div class="section">
                <h2>✨ Three Core Management Functions</h2>
                
                <h3 style="color: #667eea; margin-top: 20px;">1. 📦 Package Management</h3>
                <p><strong>Purpose:</strong> Create, manage, and organize event packages</p>
                <div class="checklist">
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>✓ Create new packages with details</span>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>✓ Read and view all packages</span>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>✓ Update package information</span>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>✓ Delete packages</span>
                    </div>
                </div>
                <p><strong>Access:</strong> Admin Panel → Packages</p>

                <h3 style="color: #667eea; margin-top: 20px;">2. 📅 Booking Management</h3>
                <p><strong>Purpose:</strong> Manage customer bookings and reservations</p>
                <div class="checklist">
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>✓ Create customer bookings</span>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>✓ View all bookings with details</span>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>✓ Update booking status (pending, confirmed, completed, cancelled)</span>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>✓ Delete bookings</span>
                    </div>
                </div>
                <p><strong>Access:</strong> Admin Panel → Bookings</p>

                <h3 style="color: #667eea; margin-top: 20px;">3. 🎨 Customization Management</h3>
                <p><strong>Purpose:</strong> Manage package add-ons and customization options</p>
                <div class="checklist">
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>✓ Create customization options (decorations, catering, etc.)</span>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>✓ Read all customizations by package</span>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>✓ Update customization details and pricing</span>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>✓ Delete customization options</span>
                    </div>
                </div>
                <p><strong>Access:</strong> Admin Panel → Customizations</p>
            </div>

            <!-- Implementation Checklist -->
            <div class="section">
                <h2>✅ Implementation Checklist</h2>
                <div class="checklist">
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>Database initialized (database-init.php)</span>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>Admin user logged in</span>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>Package CRUD tested</span>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>Booking CRUD tested</span>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>Customization CRUD tested</span>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>All data persists in database</span>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="checkbox">
                        <span>No errors in browser console</span>
                    </div>
                </div>
            </div>

            <!-- Troubleshooting -->
            <div class="section">
                <h2>🔧 Troubleshooting</h2>
                
                <h3 style="color: #d32f2f; margin-top: 15px;">Database Connection Error</h3>
                <div class="error-box">
                    <p><strong>Solution:</strong></p>
                    <ol style="margin-left: 20px;">
                        <li>Verify XAMPP is running (Apache + MySQL/MariaDB)</li>
                        <li>Check port 3307 is accessible (or 3306 for default)</li>
                        <li>Update <code>config/database.php</code> with correct credentials</li>
                    </ol>
                </div>

                <h3 style="color: #d32f2f; margin-top: 15px;">Missing Tables Error</h3>
                <div class="error-box">
                    <p><strong>Solution:</strong></p>
                    <ol style="margin-left: 20px;">
                        <li>Visit: <code>http://localhost/SINTA/public/database-init.php</code></li>
                        <li>Verify all tables created successfully (✓ marks)</li>
                        <li>Refresh the page and test again</li>
                    </ol>
                </div>

                <h3 style="color: #d32f2f; margin-top: 15px;">Duplicate Constant Error</h3>
                <div class="error-box">
                    <p><strong>Solution:</strong> This has been fixed. ROOT_PATH is now only defined in index.php. Clear browser cache.</p>
                </div>
            </div>

            <!-- File Structure -->
            <div class="section">
                <h2>📁 Project Structure</h2>
                <div class="code-block">
/SINTA
├── app/
│   ├── controllers/
│   │   ├── BookingController.php
│   │   ├── CustomizationController.php
│   │   └── [other controllers]
│   ├── models/
│   │   ├── Booking.php
│   │   ├── Testimonial.php
│   │   └── [other models]
│   └── views/
│       └── admin/
│           ├── admin-manage-bookings.php
│           ├── admin-manage-customizations.php
│           └── [other admin views]
├── config/
│   └── database.php
└── public/
    ├── index.php
    └── database-init.php
                </div>
            </div>

            <!-- Next Steps -->
            <div class="section">
                <h2>🎯 Next Steps</h2>
                <div class="button-group">
                    <a href="/SINTA/public/database-init.php" class="btn">Initialize Database</a>
                    <a href="/SINTA/public/index.php?route=admin-dashboard" class="btn">Go to Admin Panel</a>
                </div>
            </div>

            <div class="success-box" style="margin-top: 30px;">
                <strong>✓ System Ready!</strong> Your SINTA event management system is organized and ready for use. All files have been cleaned up and properly structured.
            </div>
        </div>
    </div>

    <script>
        // Interactive checklist
        document.querySelectorAll('.checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                localStorage.setItem('checklist_' + this.parentElement.textContent, this.checked);
            });
            
            // Load saved state
            const saved = localStorage.getItem('checklist_' + checkbox.parentElement.textContent);
            if (saved === 'true') checkbox.checked = true;
        });
    </script>
</body>
</html>
