-- ╔═══════════════════════════════════════════════════════════════════════════╗
-- ║                    EVENT BOOKINGS DATABASE MIGRATION                      ║
-- ╠═══════════════════════════════════════════════════════════════════════════╣
-- ║  This migration creates:                                                  ║
-- ║    1. event_bookings table for managing event reservations                ║
-- ║    2. Supports filtering by upcoming, past, week, month                   ║
-- ╚═══════════════════════════════════════════════════════════════════════════╝

-- ═══════════════════════════════════════════════════════════════════════════
-- EVENT BOOKINGS TABLE
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `event_bookings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_name` VARCHAR(200) NOT NULL,
  `event_type` ENUM('birthday', 'wedding', 'corporate', 'anniversary', 'graduation', 'reunion', 'other') NOT NULL DEFAULT 'other',
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(20) NOT NULL,
  `customer_email` VARCHAR(100) DEFAULT NULL,
  `event_date` DATE NOT NULL,
  `event_time` TIME NOT NULL,
  `end_time` TIME DEFAULT NULL,
  `guest_count` INT(11) NOT NULL DEFAULT 1,
  `venue` VARCHAR(200) DEFAULT 'Green Bites Restaurant',
  `package_type` ENUM('basic', 'standard', 'premium', 'custom') NOT NULL DEFAULT 'standard',
  `advance_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` ENUM('pending', 'partial', 'paid', 'refunded') NOT NULL DEFAULT 'pending',
  `booking_status` ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  `special_requirements` TEXT DEFAULT NULL,
  `menu_items` TEXT DEFAULT NULL,
  `decorations` TEXT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_date` (`event_date`),
  KEY `idx_booking_status` (`booking_status`),
  KEY `idx_customer_phone` (`customer_phone`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════════════════
-- SAMPLE DATA (Optional - for testing)
-- ═══════════════════════════════════════════════════════════════════════════

-- INSERT INTO `event_bookings` (`event_name`, `event_type`, `customer_name`, `customer_phone`, `customer_email`, `event_date`, `event_time`, `guest_count`, `package_type`, `advance_amount`, `total_amount`, `payment_status`, `booking_status`, `special_requirements`) VALUES
-- ('Rahul\'s Birthday Party', 'birthday', 'Rahul Ahmed', '01712345678', 'rahul@email.com', DATE_ADD(CURDATE(), INTERVAL 7 DAY), '18:00:00', 50, 'premium', 10000.00, 50000.00, 'partial', 'confirmed', 'Need birthday cake and balloon decorations'),
-- ('Corporate Annual Dinner', 'corporate', 'ABC Company Ltd', '01812345678', 'hr@abc.com', DATE_ADD(CURDATE(), INTERVAL 14 DAY), '19:00:00', 100, 'premium', 25000.00, 100000.00, 'partial', 'confirmed', 'Projector and mic required'),
-- ('Wedding Reception', 'wedding', 'Karim & Fatima', '01912345678', 'karim@email.com', DATE_ADD(CURDATE(), INTERVAL 30 DAY), '17:00:00', 200, 'custom', 50000.00, 250000.00, 'partial', 'pending', 'Full wedding decoration package');
