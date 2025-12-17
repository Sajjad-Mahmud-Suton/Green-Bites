# Complaint Table Setup Instructions

## Overview
This update modifies the complaints system to use MySQL instead of Firebase, with the following structure:
- `id` - Primary key, auto-increment
- `name` - User's name (VARCHAR 100, NOT NULL)
- `email` - User's email (VARCHAR 150, NOT NULL)
- `message` - Complaint text (TEXT, NOT NULL)
- `image_path` - Relative path to uploaded image (VARCHAR 255, NULL)
- `created_at` - Timestamp (TIMESTAMP, NOT NULL, DEFAULT CURRENT_TIMESTAMP)

## Step 1: Alter the Database Table

You have two options to alter the complaints table:

### Option A: Use the PHP Script (Recommended)
1. Open your browser and navigate to: `http://localhost/Green-Bites/database/alter_complaints_safe.php`
2. The script will automatically check and alter the table structure
3. You'll see a confirmation message with the final table structure

### Option B: Use SQL Script Manually
1. Open phpMyAdmin
2. Select the `green_bites` database
3. Go to the SQL tab
4. Open and run `database/alter_complaints_table.sql`
5. Run statements one at a time, skipping any that give errors (if columns already exist)

## Step 2: Verify Table Structure

After running the alteration script, verify the structure by running:
```sql
DESCRIBE complaints;
```

You should see:
- id (INT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR(100), NOT NULL)
- email (VARCHAR(150), NOT NULL)
- message (TEXT, NOT NULL)
- image_path (VARCHAR(255), NULL)
- created_at (TIMESTAMP, NOT NULL, DEFAULT CURRENT_TIMESTAMP)

## Step 3: Create Uploads Directory

The `submit_complaint.php` script will automatically create the `uploads/complaints/` directory when needed. However, you can create it manually:

1. Create folder: `uploads/complaints/` in your project root
2. Set permissions to 755 (or 777 if needed for your server)

## Step 4: Test the Complaint Form

1. Navigate to the Home page (`index.php`)
2. Scroll to the Complaint section
3. Fill in the form:
   - Name (required)
   - Email (required)
   - Order ID (optional)
   - Complaint Message (required)
   - Image (optional)
4. Submit the form
5. You should see a success message

## Files Modified/Created

### Modified Files:
- `index.php` - Updated complaint form to include name and email fields
- `js/user.js` - Updated to submit via AJAX to PHP handler instead of Firebase

### New Files:
- `submit_complaint.php` - PHP handler with prepared statements for complaint submission
- `database/alter_complaints_safe.php` - Safe PHP script to alter table structure
- `database/alter_complaints_table.sql` - SQL script for manual table alteration

## Security Features

- ✅ Prepared statements to prevent SQL injection
- ✅ Input validation and sanitization
- ✅ File type validation for images
- ✅ File size limit (5MB)
- ✅ Email format validation
- ✅ Secure file upload handling

## Notes

- The form will pre-fill name and email if the user is logged in (from session)
- Images are stored in `uploads/complaints/` with unique filenames
- The form uses AJAX submission, so the page won't reload
- All database operations use prepared statements for security

