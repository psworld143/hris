# SEAIT HRIS - Human Resource Information System

A comprehensive Human Resource Information System built with Pure PHP and Tailwind CSS for South East Asian Institute of Technology, Inc.

## Features

### 🏢 Core HR Management
- **Employee Management**: Complete CRUD operations for employee records
- **Department Management**: Organize employees by departments
- **Position Management**: Define job positions and roles
- **User Management**: Multi-role user system (Admin, HR, Employee)

### ⏰ Attendance Tracking
- **Time Tracking**: Mark attendance with time in/out
- **Status Tracking**: Present, Absent, Late, Half Day
- **Weekly Reports**: Attendance summaries and trends
- **Deduction Calculation**: Automatic salary deductions based on attendance

### 📅 Leave Management
- **Leave Types**: Vacation, Sick, Maternity, Paternity, Bereavement
- **Leave Requests**: Submit and approve leave applications
- **Leave Balance**: Track remaining leave days per employee
- **Approval Workflow**: HR approval system for leave requests

### 💰 Payroll System
- **Salary Management**: Basic salary, allowances, deductions
- **Automatic Calculations**: Attendance-based deductions
- **Payroll Generation**: Monthly payroll processing
- **Payment Tracking**: Mark payments as completed

### 📊 Reports & Analytics
- **Dashboard**: Real-time statistics and overview
- **Attendance Reports**: Detailed attendance analytics
- **Leave Reports**: Leave balance and usage reports
- **Payroll Reports**: Salary and payment summaries
- **Department Analytics**: Employee distribution and trends

### ⚙️ System Features
- **Modern UI**: Responsive design with Tailwind CSS
- **Security**: Password hashing and session management
- **Data Validation**: Input sanitization and validation
- **User Roles**: Role-based access control
- **Settings Management**: System configuration options

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- XAMPP/WAMP/MAMP (for local development)

### Setup Instructions

1. **Clone or Download the Project**
   ```bash
   # If using git
   git clone <repository-url>
   # Or download and extract the ZIP file
   ```

2. **Database Setup**
   - Create a new MySQL database named `seait_hris`
   - The system will automatically create all required tables on first run

3. **Configuration**
   - Open `config/database.php`
   - Update database connection settings:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'seait_hris');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

4. **Web Server Configuration**
   - Place the project in your web server's document root
   - For XAMPP: Place in `htdocs/hris/`
   - For WAMP: Place in `www/hris/`

5. **Access the System**
   - Open your web browser
   - Navigate to: `http://localhost/hris/`
   - Default login credentials:
     - Username: `admin`
     - Password: `admin123`

## System Structure

```
hris/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   └── functions.php         # Helper functions
├── pages/
│   ├── dashboard.php         # Main dashboard
│   ├── employees.php         # Employee management
│   ├── departments.php       # Department management
│   ├── positions.php         # Position management
│   ├── attendance.php        # Attendance tracking
│   ├── leave.php            # Leave management
│   ├── payroll.php          # Payroll system
│   ├── reports.php          # Reports and analytics
│   └── settings.php         # System settings
├── index.php                # Main entry point
├── login.php                # Authentication
├── logout.php               # Logout handler
└── README.md               # This file
```

## Database Schema

### Core Tables
- **users**: System users and authentication
- **employees**: Employee information and records
- **departments**: Organizational departments
- **positions**: Job positions and roles

### Functional Tables
- **attendance**: Daily attendance records
- **leave_types**: Types of leave available
- **leave_requests**: Leave applications and approvals
- **payroll**: Monthly payroll records

## User Roles

### Admin
- Full system access
- User management
- System configuration
- All HR functions

### HR
- Employee management
- Attendance tracking
- Leave approval
- Payroll processing
- Reports access

### Employee
- View own information
- Submit leave requests
- View attendance records
- Limited access to personal data

## Features in Detail

### Employee Management
- Add, edit, and delete employee records
- Generate unique employee IDs
- Track employment history
- Manage salary information
- Status tracking (Active, Inactive, Terminated)

### Attendance System
- Daily attendance marking
- Time in/out tracking
- Status classification (Present, Absent, Late, Half Day)
- Weekly and monthly summaries
- Automatic deduction calculations

### Leave Management
- Multiple leave types with different allowances
- Leave request submission and approval workflow
- Leave balance tracking
- Approval history and status tracking

### Payroll Processing
- Automatic salary calculations
- Attendance-based deductions
- Allowance management
- Payment status tracking
- Monthly payroll generation

### Reporting System
- Real-time dashboard statistics
- Department-wise analytics
- Attendance trend analysis
- Leave balance reports
- Payroll summaries

## Security Features

- **Password Hashing**: Secure password storage using PHP's password_hash()
- **Session Management**: Secure session handling
- **Input Sanitization**: Protection against XSS attacks
- **SQL Injection Prevention**: Prepared statements for all database queries
- **Role-based Access**: Different permissions for different user roles

## Customization

### Adding New Features
1. Create new PHP files in the `pages/` directory
2. Add navigation links in `index.php`
3. Update the `$allowed_pages` array
4. Create corresponding database tables if needed

### Styling
- The system uses Tailwind CSS for styling
- Modify classes in PHP files to change appearance
- Add custom CSS in the head section if needed

### Database Modifications
- Add new tables in `config/database.php`
- Update helper functions in `includes/functions.php`
- Modify queries in respective page files

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Verify database name exists

2. **Page Not Found**
   - Check web server configuration
   - Verify file permissions
   - Ensure .htaccess is properly configured

3. **Login Issues**
   - Default credentials: admin/admin123
   - Check if users table exists
   - Verify password hashing is working

4. **Permission Errors**
   - Check file and folder permissions
   - Ensure web server has read/write access
   - Verify PHP has required extensions

## Support

For technical support or feature requests:
- Check the documentation in this README
- Review the code comments for implementation details
- Ensure all prerequisites are met
- Verify database and web server configurations

## License

This project is developed for South East Asian Institute of Technology, Inc.
All rights reserved.

## Version History

- **v1.0.0**: Initial release with core HR features
  - Employee management
  - Attendance tracking
  - Leave management
  - Payroll system
  - Reports and analytics
  - User management
  - Settings and configuration

---

**SEAIT HRIS** - Streamlining Human Resource Management for Educational Excellence 