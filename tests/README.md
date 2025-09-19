# Tests Directory

This directory contains test files, demo files, and development utilities for FarmScout Online.

## Contents

### Test Files
- `test_*.php` - PHP test scripts for various functionalities
- `test_*.html` - HTML test pages for UI components
- `test-animations.html` - Animation testing page
- `test-search-styling.html` - Search functionality testing

### Demo Files
- `demo_special_event_email.php` - Email template demo
- `discord_style_demo.html` - Discord-style UI demo
- `email_template_demo.html` - Email template preview
- `shopping-list-demo.html` - Shopping list component demo

### Development Utilities
- `check_table_structure.php` - Database schema verification
- `cleanup_test_data.php` - Clean up test data from database
- `setup_*.php` - Setup and installation scripts
- `install-email.php` - Email system installation
- `preview_*.php` - Template preview utilities

### Documentation & Guides
- `user_guide_price_alerts.html` - Price alerts user guide
- `explain_price_alerts.php` - Price alerts explanation

### Database Utilities
- `remove_user_emails.php` - Remove user email alerts
- `remove_user_emails.sql` - SQL script for email cleanup
- `fix_alerts_table.php` - Database table repair utility

## Usage

These files are for development and testing purposes only. They are not part of the main website functionality and can be safely ignored during normal website operation.

### Test Runners Available:

1. **Simple Test Runner** (`simple_test_runner.php`)
   - **Recommended for most users**
   - Works standalone without database dependencies
   - Tests core functions and security features
   - Access: `http://localhost/farmscout_online/tests/simple_test_runner.php`

2. **Full Test Runner** (`run_all_tests.php`)
   - Comprehensive testing with database integration
   - Requires full application setup
   - May need configuration adjustments
   - Access: `http://localhost/farmscout_online/tests/run_all_tests.php`

**Note:** If you encounter errors with the full test runner, use the simple test runner instead. It demonstrates the same testing concepts for academic purposes.
