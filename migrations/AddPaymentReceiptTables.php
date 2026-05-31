<?php

/**
 * Migration: Add Payment Receipt Tables
 * Purpose: Create tables for tracking partial/full payments and receipts
 * Run: php migrate.php AddPaymentReceiptTables
 */

class AddPaymentReceiptTables {
    
    public static function up($db) {
        try {
            // 1. payments_tbl - Detailed payment transactions
            $sql1 = "CREATE TABLE IF NOT EXISTS payments_tbl (
                    payment_id INT PRIMARY KEY AUTO_INCREMENT,
                    plan_id INT NOT NULL,
                    user_id INT NOT NULL,
                    payment_type ENUM('deposit', 'full', 'final') NOT NULL COMMENT 'deposit=50%, final=remaining 50%, full=100%',
                    amount DECIMAL(10,2) NOT NULL,
                    payment_method ENUM('gcash', 'paymaya', 'bank_transfer', 'atm_card', 'credit_card') NOT NULL,
                    payment_status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
                    transaction_id VARCHAR(255) UNIQUE,
                    reference_number VARCHAR(255),
                    payment_details JSON,
                    gateway_response JSON,
                    paid_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (plan_id) REFERENCES plans_tbl(plan_id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users_tbl(user_id) ON DELETE CASCADE,
                    INDEX idx_plan_id (plan_id),
                    INDEX idx_user_id (user_id),
                    INDEX idx_payment_status (payment_status),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if (!$db->query($sql1)) {
                throw new Exception("Error creating payments_tbl: " . $db->error);
            }
            echo "✓ Created payments_tbl\n";

            // 2. payment_receipts_tbl - Receipt records
            $sql2 = "CREATE TABLE IF NOT EXISTS payment_receipts_tbl (
                    receipt_id INT PRIMARY KEY AUTO_INCREMENT,
                    plan_id INT NOT NULL,
                    user_id INT NOT NULL,
                    payment_id INT,
                    receipt_number VARCHAR(50) UNIQUE NOT NULL,
                    receipt_type ENUM('partial', 'full') NOT NULL,
                    subtotal DECIMAL(10,2) NOT NULL,
                    service_fee DECIMAL(10,2) NOT NULL,
                    total_amount DECIMAL(10,2) NOT NULL,
                    amount_paid DECIMAL(10,2) NOT NULL,
                    balance_remaining DECIMAL(10,2) NOT NULL DEFAULT 0,
                    payment_method VARCHAR(100),
                    paid_by VARCHAR(255),
                    paid_at TIMESTAMP NULL,
                    reference_number VARCHAR(255),
                    notes TEXT,
                    items_purchased JSON,
                    recipient_email VARCHAR(255),
                    recipient_phone VARCHAR(20),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (plan_id) REFERENCES plans_tbl(plan_id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users_tbl(user_id) ON DELETE CASCADE,
                    FOREIGN KEY (payment_id) REFERENCES payments_tbl(payment_id) ON DELETE SET NULL,
                    INDEX idx_plan_id (plan_id),
                    INDEX idx_user_id (user_id),
                    INDEX idx_receipt_number (receipt_number),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if (!$db->query($sql2)) {
                throw new Exception("Error creating payment_receipts_tbl: " . $db->error);
            }
            echo "✓ Created payment_receipts_tbl\n";

            // 3. Alter plans_tbl to add new payment tracking fields
            $check = $db->query("SHOW COLUMNS FROM plans_tbl WHERE Field IN ('total_paid', 'balance_remaining', 'payment_count')");
            
            if ($check && $check->num_rows === 0) {
                $alters = [
                    "ALTER TABLE plans_tbl ADD COLUMN total_paid DECIMAL(10,2) DEFAULT 0",
                    "ALTER TABLE plans_tbl ADD COLUMN balance_remaining DECIMAL(10,2) DEFAULT 0",
                    "ALTER TABLE plans_tbl ADD COLUMN payment_count INT DEFAULT 0"
                ];
                
                foreach ($alters as $alter) {
                    if (!$db->query($alter)) {
                        throw new Exception("Error altering plans_tbl: " . $db->error);
                    }
                }
                echo "✓ Added payment tracking fields to plans_tbl\n";
            }

            echo "\n✓ All payment receipt tables created successfully!\n";
            return true;

        } catch (Exception $e) {
            echo "✗ Migration failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    public static function down($db) {
        try {
            $db->query("DROP TABLE IF EXISTS payment_receipts_tbl");
            $db->query("DROP TABLE IF EXISTS payments_tbl");
            echo "✓ Payment tables rolled back\n";
            return true;
        } catch (Exception $e) {
            echo "✗ Rollback failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
}
?>
