# StudyHub - Online Learning Platform

StudyHub is a comprehensive online learning platform built with PHP, MySQL, HTML, CSS, and JavaScript. It provides students with access to courses, instructors with tools to create and manage courses, and administrators with oversight capabilities.

## Features

### User Management
- User registration and authentication
- Role-based access (Student, Instructor, Admin)
- Password security with hashing

### Course Management
- Browse courses by category
- Detailed course information
- Course enrollment system
- Progress tracking

### Instructor Tools
- Create and manage courses
- Add lessons and quizzes
- Track student progress
- Manage course content

### Student Features
- Enroll in courses
- Track learning progress
- Take quizzes
- View certificates
- Participate in discussions

### Additional Features
- FAQ system
- Contact support
- Course ratings and reviews
- Certificate generation
- Payment integration

## Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL (via XAMPP)
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5
- **Server**: Apache
- **PDF Generation**: TCPDF

## Installation

1. **Prerequisites**
   - XAMPP (Apache, MySQL, PHP)
   - Git (optional, for version control)

2. **Setup**
   - Clone or download the repository to your local server
   - Place the files in your `htdocs` directory (e.g., `c:/xampp/htdocs/StudyHub`)
   - Start Apache and MySQL in XAMPP Control Panel

3. **Database Setup**
   - Access phpMyAdmin via `http://localhost/phpmyadmin`
   - Create a new database named `studyhub_db`
   - Import the `database_schema.sql` file from the project root
   - Run the SQL to create tables and add sample data

4. **Configuration**
   - The application will connect to MySQL using default XAMPP settings (localhost, root user, empty password)
   - If you've changed your MySQL settings, update the connection details in `includes/db_connection.php`

5. **Access the Application**
   - Navigate to `http://localhost/StudyHub` in your browser
   - Register a new account or use the default admin account if created during database setup

## Database Schema

The application includes a complete database schema with the following key tables:
- `users` - User accounts and authentication
- `courses` - Course information and details
- `categories` - Course categories
- `lessons` - Course content and lessons
- `enrollments` - User course enrollments
- `progress` - User progress tracking
- `quizzes` and `quiz_attempts` - Quiz system
- `reviews` - Course ratings and reviews
- `certificates` - Generated certificates
- `faqs` - Frequently asked questions
- `support_tickets` - Support system

## Usage

1. **As a Student**
   - Register for an account
   - Browse available courses
   - Enroll in courses
   - Track your progress
   - Complete quizzes and earn certificates

2. **As an Instructor**
   - Register for an account and request instructor access
   - Create and manage your courses
   - Add lessons and quizzes
   - Monitor student progress

3. **As an Admin**
   - Manage users and content
   - Monitor platform activity
   - Update FAQs and manage support tickets

## File Structure

```
StudyHub/
├── index.php                 # Home page
├── courses.php               # Course catalog
├── course_detail.php         # Course details
├── register.php              # User registration
├── login.php                 # User login
├── about.php                 # About page
├── contact.php               # Contact page
├── faq.php                   # FAQ page
├── database_schema.sql       # Database schema
├── includes/
│   ├── config.php           # Application configuration
│   ├── db_connection.php    # Database connection
│   └── functions.php        # Utility functions
├── assets/
│   ├── css/                 # Stylesheets
│   ├── js/                  # JavaScript files
│   └── images/              # Image assets
├── student/                 # Student dashboard and pages
├── instructor/              # Instructor dashboard and pages
└── admin/                   # Admin dashboard and pages
```

## Security Features

- Password hashing using PHP's password_hash()
- Input sanitization and validation
- SQL injection prevention with prepared statements
- Session management for authentication
- Role-based access control

## Customization

The platform can be customized by:
- Modifying CSS files in the `assets/css/` directory
- Adding new course categories in the database
- Extending functionality in the `includes/functions.php` file
- Updating content in the database

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## Support

If you encounter issues with the installation or operation of the platform, please check:

- That all XAMPP services (Apache and MySQL) are running
- That the database has been properly imported
- That file permissions allow PHP execution
- That the database connection settings are correct

## License

This project is created for educational purposes. Feel free to use and modify according to your needs.