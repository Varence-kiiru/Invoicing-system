# Olivian Group Invoicing System

A comprehensive PHP-based invoicing and quotation management system with tax handling, client management, and professional invoice generation.

## Features

- **Invoice Management**: Create, edit, view, and delete invoices with tax calculations
- **Quotation Management**: Generate quotations that can be converted to invoices
- **Tax Handling**: 
  - Configurable tax rates per document
  - Tax-exempt item support for individual line items
  - Proper tax calculation on taxable items only
- **Client Management**: Maintain client information and contact details
- **Professional Printing**: Generate print-ready invoices and quotations
- **Dashboard Overview**: Quick access to key metrics and recent documents
- **Settings Management**: Configure company information and default tax rates

## Tech Stack

- **Backend**: PHP 8.2.12
- **Database**: MySQL/MariaDB
- **Frontend**: Bootstrap 5.1.3, jQuery 3.6.0, DataTables 1.11.5
- **Server**: Apache 2.4.58
- **Version Control**: Git

## Prerequisites

- PHP 8.0+
- MySQL 5.7+
- Apache with .htaccess support
- Composer 2.0+

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/Varence-kiiru/Invoicing-system.git
cd Invoicing-system
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

Copy the example environment file and update with your database credentials:

```bash
cp .env.example .env
```

Edit `.env` with your configuration:

```env
APP_ENV=development
DB_HOST=localhost
DB_NAME=olivian_invoice
DB_USER=root
DB_PASS=your_password
```

### 4. Create Database

```bash
mysql -u root -p < database/olivian_invoice.sql
```

Replace `root` with your MySQL username and enter your password when prompted.

### 5. Set Up Web Server

For Apache/XAMPP, ensure the project is in `htdocs`:

```bash
C:\xampp\htdocs\Invoicing-system
```

Or configure your virtual host to point to the project directory.

### 6. Access the Application

Open your browser and navigate to:

```
http://localhost/Invoicing-system
```

## Database Schema

### Tables

- **settings** - Company configuration (name, address, tax rate, logo, etc.)
- **clients** - Client information (name, email, phone, address)
- **invoices** - Invoice records with totals and payment status
- **invoice_items** - Line items for invoices with tax-exempt flags
- **quotations** - Quotation records with validity dates
- **quotation_items** - Line items for quotations with tax-exempt flags

### Key Features

- Foreign key relationships with CASCADE delete
- Tax rate stored per invoice/quotation (preserves historical accuracy)
- Tax-exempt flag per item (allows mixed taxable/non-taxable items)
- Timestamp tracking on client creation

## Usage

### Creating an Invoice

1. Navigate to **Invoices** menu
2. Click **New Invoice** button
3. Select client and add items
4. Mark items as tax-exempt if needed
5. Adjust tax rate if different from default
6. Click **Save Invoice**

### Creating a Quotation

1. Navigate to **Quotations** menu
2. Click **New Quotation** button
3. Add quotation items
4. Review calculated totals
5. Save and share with client

### Converting Quotation to Invoice

1. View the quotation
2. Click **Convert to Invoice**
3. Quotation becomes an invoice with same details

### Tax Calculations

- **Taxable Subtotal**: Sum of all non-exempt items
- **Non-Taxable Subtotal**: Sum of items marked as tax-exempt
- **Tax Amount**: Taxable subtotal × (tax rate / 100)
- **Final Total**: Subtotal + Tax Amount

## Project Structure

```
├── ajax/                    # AJAX endpoints for dynamic operations
│   ├── save_invoice.php
│   ├── get_invoice.php
│   ├── delete_invoice.php
│   ├── save_quotation.php
│   ├── get_quotation.php
│   ├── delete_quotation.php
│   └── ... (other endpoints)
├── assets/
│   └── js/                  # JavaScript functionality
│       ├── invoices.js
│       └── quotation.js
├── config/
│   └── config.php          # Database configuration (uses .env)
├── database/
│   └── olivian_invoice.sql # Database schema
├── includes/               # Shared PHP templates
│   ├── navbar.php
│   └── footer.php
├── templates/              # Modal and form templates
│   ├── invoice_modal.php
│   └── quotation_modal.php
├── vendor/                 # Composer dependencies (not committed)
├── .env                    # Environment variables (not committed)
├── .env.example           # Environment template
├── .gitignore             # Git ignore rules
├── clients.php            # Client management page
├── invoices.php           # Invoice list page
├── quotations.php         # Quotation list page
├── settings.php           # Application settings
├── index.php              # Dashboard
└── README.md              # This file
```

## API Endpoints

All endpoints return JSON responses.

### Invoice Endpoints

- `GET ajax/get_invoice.php?id=<id>` - Fetch invoice with items
- `POST ajax/save_invoice.php` - Create/update invoice
- `POST ajax/delete_invoice.php` - Delete invoice
- `POST ajax/mark_paid.php` - Mark invoice as paid
- `POST ajax/get_next_invoice_number.php` - Generate next invoice number

### Quotation Endpoints

- `GET ajax/get_quotation.php?id=<id>` - Fetch quotation with items
- `POST ajax/save_quotation.php` - Create/update quotation
- `POST ajax/delete_quotation.php` - Delete quotation
- `POST ajax/get_next_quotation_number.php` - Generate next quotation number

## Security Considerations

- Database credentials are stored in `.env` and excluded from version control
- All database queries use prepared statements to prevent SQL injection
- `.env` file contains sensitive data and must never be committed
- Example `.env.example` is provided for configuration reference

## Development

### Running Locally

1. Ensure XAMPP or similar is running
2. Start MySQL service
3. Place project in `htdocs` directory
4. Access via `http://localhost/Invoicing-system`

### File Modifications Log

Key files modified in this session:
- `config/config.php` - Updated to use environment variables
- `.env` - Created with database configuration
- `.gitignore` - Added to exclude sensitive files
- `composer.json` - Added vlucas/phpdotenv dependency
- Database schema - Added `tax_rate` column to invoices and quotations
- JavaScript files - Enhanced to handle tax calculations and storage

## Contributing

1. Create a feature branch
2. Make changes
3. Commit with clear messages
4. Push to GitHub
5. Create pull request

## Future Enhancements

- [ ] User authentication and authorization
- [ ] Email invoice delivery
- [ ] Payment gateway integration
- [ ] Recurring invoices
- [ ] Multi-currency support
- [ ] Advanced reporting
- [ ] Mobile app

## Support

For issues and questions, please open an issue on GitHub:
https://github.com/Varence-kiiru/Invoicing-system/issues

## License

All rights reserved - Olivian Group

## Author

**Varence Kiiru** - Initial development and maintenance

---

**Last Updated**: March 2, 2026

For the latest version, visit: https://github.com/Varence-kiiru/Invoicing-system
