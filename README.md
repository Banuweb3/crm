# CRM System - Customer Relationship Management

A comprehensive, web-based CRM system built with PHP and MySQL. This system helps small to medium businesses manage their leads, customers, tasks, and communications effectively.

## 🚀 Features

### Core Functionality
- **Dashboard** - Overview with key metrics and recent activity
- **Leads Management** - Track and manage sales leads through the pipeline
- **Customer Management** - Maintain customer database with detailed contact information
- **Task Management** - Create and track tasks with due dates and priorities
- **Notes System** - Keep track of communications and important information
- **User Authentication** - Secure login/logout with session management
- **Admin Panel** - User management and system administration

### Key Highlights
- **Lead Conversion** - Convert qualified leads to customers with one click
- **Responsive Design** - Works on desktop, tablet, and mobile devices
- **Data Tables** - Sortable, searchable tables with pagination
- **Activity Logging** - Track user actions for audit purposes
- **Role-based Access** - Admin and user roles with different permissions
- **Bootstrap UI** - Modern, professional interface

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5.1.3
- **Icons**: Font Awesome 6.0
- **Tables**: DataTables
- **Architecture**: MVC-inspired structure

## 📋 System Requirements

- **Web Server**: Apache 2.4+ or Nginx
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher
- **Extensions**: PDO, PDO_MySQL
- **Browser**: Modern browser with JavaScript enabled

## 🔧 Installation

### 1. Download and Setup
```bash
# Clone or download the project files to your web server directory
# For example, in XAMPP: htdocs/crm/
```

### 2. Database Setup
1. Create a new MySQL database named `crm_system`
2. Import the database schema:
   ```sql
   mysql -u root -p crm_system < database/schema.sql
   ```
   Or manually run the SQL commands from `database/schema.sql`

### 3. Configuration
1. Edit `config/database.php` and update the database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'crm_system');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

### 4. File Permissions
Ensure the web server has read access to all files and write access to session directories.

### 5. Access the System
1. Open your web browser
2. Navigate to `http://your-domain/crm/` (or your local path)
3. You'll be redirected to the login page

## 👤 Default Login

**Username**: `admin`  
**Password**: `admin123`

⚠️ **Important**: Change the default admin password immediately after first login!

## 📁 Project Structure

```
crm/
├── config/
│   └── database.php          # Database configuration
├── database/
│   └── schema.sql           # Database schema and sample data
├── includes/
│   ├── functions.php        # Common functions
│   ├── header.php          # Common header template
│   └── footer.php          # Common footer template
├── admin.php               # Admin panel
├── customers.php           # Customer management
├── dashboard.php           # Main dashboard
├── index.php              # Entry point (redirects)
├── leads.php              # Lead management
├── login.php              # Login page
├── logout.php             # Logout handler
├── notes.php              # Notes management
├── tasks.php              # Task management
└── README.md              # This file
```

## 🎯 Usage Guide

### Getting Started
1. **Login** with the default admin credentials
2. **Add Users** (Admin Panel) - Create accounts for your team
3. **Add Leads** - Start entering your sales prospects
4. **Create Tasks** - Set up follow-up activities
5. **Track Progress** - Monitor leads through the sales pipeline

### Lead Management
- Add new leads with contact information
- Track lead status (New → Contacted → Qualified → Converted/Lost)
- Convert qualified leads to customers
- Assign leads to team members

### Customer Management
- Maintain detailed customer profiles
- Track customer history and interactions
- Link customers to related tasks and notes

### Task Management
- Create tasks with due dates and priorities
- Link tasks to specific leads or customers
- Track task completion status
- Get overdue task notifications

### Notes System
- Add notes for leads, customers, or tasks
- Keep communication history
- Share important information with team

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Session-based authentication
- Role-based access control
- CSRF protection on forms

## 🎨 Customization

### Styling
- Modify CSS in the `<style>` sections of header.php
- Colors use CSS custom properties for easy theming
- Bootstrap classes can be customized

### Database
- Add custom fields by modifying the database schema
- Update corresponding PHP files to handle new fields

### Features
- Add new modules by following the existing pattern
- Extend user roles and permissions as needed

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database exists and user has proper permissions

**Login Issues**
- Verify the users table has the default admin user
- Check if sessions are working (session directory writable)
- Clear browser cache and cookies

**Permission Errors**
- Ensure web server has read access to all files
- Check PHP error logs for specific issues

### Debug Mode
Add this to the top of any PHP file for debugging:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📈 Future Enhancements

Potential features for future versions:
- Email integration and templates
- Advanced reporting and analytics
- Document management
- Calendar integration
- API endpoints for mobile apps
- Advanced workflow automation
- Multi-language support

## 📄 License

This project is open source and available under the MIT License.

## 🤝 Support

For support, feature requests, or bug reports:
1. Check the troubleshooting section above
2. Review the code comments for implementation details
3. Test with the sample data provided

## 🙏 Acknowledgments

- Bootstrap team for the excellent CSS framework
- Font Awesome for the icon library
- DataTables for enhanced table functionality
- PHP and MySQL communities for robust backend technologies

---

**Happy CRM-ing!** 🎉
"# crm" 
