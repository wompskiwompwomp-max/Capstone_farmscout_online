# FarmScout Online - PHP MySQL Version

A dynamic PHP-based agricultural price monitoring system for Baloan Public Market, converted from the original HTML version.

## Features

- **Real-time Price Dashboard**: Live market prices with price change tracking
- **Product Management**: Complete CRUD operations for products and categories
- **Search Functionality**: Real-time product search with AJAX
- **Category Browsing**: Organized product categories with filtering
- **Admin Panel**: Easy-to-use interface for managing products and prices
- **Mobile-Responsive**: Tailwind CSS for consistent design across devices
- **Price History**: Track price changes over time

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

## Installation

### 1. Clone/Download Files
Download all files to your web server directory.

### 2. Database Setup
1. Create a MySQL database named `farmscout_online`
2. Import the database schema:
   ```sql
   mysql -u username -p farmscout_online < database/schema.sql
   ```

### 3. Database Configuration
Edit `config/database.php` and update the database credentials:
```php
private $host = "localhost";
private $db_name = "farmscout_online";
private $username = "your_username";
private $password = "your_password";
```

### 4. Web Server Configuration
Ensure your web server is configured to serve PHP files and has the following extensions enabled:
- PDO MySQL
- JSON

### 5. CSS Build (Optional)
If you need to modify the CSS:
```bash
npm install
npm run build:css
```

## File Structure

```
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── functions.php         # Core PHP functions
│   ├── header.php           # Common header template
│   └── footer.php           # Common footer template
├── database/
│   └── schema.sql           # Database schema and sample data
├── css/                     # Stylesheets (Tailwind CSS)
├── pages/                   # Original HTML files (reference)
├── index.php               # Main homepage
├── admin.php               # Admin panel for product management
├── categories.php          # Product categories page
├── quick-check.php         # Quick price search tool
└── README.md               # This file
```

## Usage

### Admin Panel
Access the admin panel at `/admin.php` to:
- Add new products
- Edit existing products
- Delete products
- Manage categories
- Update prices

### Product Management
- **Add Product**: Fill in the form with product details including Filipino and English names
- **Edit Product**: Click "Edit" button on any product in the admin panel
- **Delete Product**: Click "Delete" button (performs soft delete)
- **Featured Products**: Check the "Featured Product" checkbox to display on homepage

### Search Functionality
- **Homepage Search**: Basic search form redirects to filtered results
- **Quick Check**: Real-time AJAX search with instant results
- **Category Filtering**: Browse products by specific categories

## Database Schema

### Tables
- **categories**: Product categories (Gulay, Prutas, Karne, etc.)
- **products**: All market products with pricing information
- **vendors**: Market vendor information
- **price_history**: Historical price tracking

### Key Features
- Foreign key relationships for data integrity
- Soft deletes for products (is_active flag)
- Timestamp tracking for all changes
- Price history for trend analysis

## API Endpoints

### AJAX Search
```
GET /quick-check.php?ajax=1&search={term}
```
Returns JSON array of matching products with price information.

## Customization

### Adding New Categories
1. Insert into `categories` table via admin panel or direct SQL
2. Include appropriate icon SVG path
3. Set price range and sort order

### Modifying Styles
1. Edit `css/tailwind.css` for custom styles
2. Update `tailwind.config.js` for theme changes
3. Run `npm run build:css` to compile

### Extending Functionality
- Add user authentication system
- Implement price alerts via email/SMS
- Add shopping list functionality
- Include vendor management features

## Security Considerations

- Input sanitization on all user inputs
- PDO prepared statements prevent SQL injection
- HTML escaping prevents XSS attacks
- Proper error handling without exposing sensitive data

## Sample Data

The database schema includes sample data:
- 5 product categories
- 10 sample products
- 5 sample vendors
- Price history for trending analysis

## Migration from HTML

This PHP version maintains:
- All original design elements
- Tailwind CSS styling
- Mobile responsiveness
- User experience consistency

New additions:
- Dynamic content from database
- Admin management interface
- Search functionality
- Price tracking capabilities

## Support

For issues or questions:
1. Check database connection in `config/database.php`
2. Verify PHP extensions are installed
3. Check web server error logs
4. Ensure proper file permissions

## Testing

FarmScout Online includes a comprehensive testing framework:

### Running Tests
1. **Simple Test Runner**: `http://localhost/farmscout_online/tests/simple_test_runner.php`
   - Quick, standalone testing (8 tests)
   - Works without any setup
   - Recommended for demos

2. **Complete Test Runner**: `http://localhost/farmscout_online/tests/run_all_tests_fixed.php`
   - Comprehensive testing (50+ tests)
   - Multiple test suites
   - Professional test output
   - Recommended for professor evaluation

3. **Individual Tests**: Access specific test files in `tests/unit/` and `tests/integration/`

### Test Coverage
- **Unit Tests**: Core functions, security, validation
- **Integration Tests**: Database operations, search functionality
- **Manual Tests**: File structure, configuration validation
- **Total**: 29+ test cases covering critical functionality

### Test Documentation
See `docs/TESTING_GUIDE.md` for comprehensive testing information including:
- Test structure and organization
- Academic evaluation criteria
- Running instructions
- Troubleshooting guide

## License

MIT License - Feel free to modify and distribute as needed.
