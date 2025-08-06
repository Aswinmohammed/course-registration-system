# 🎓 Course Registration Management System

A comprehensive full-stack web application for ICST University to manage course registrations, built with **PHP**, **MySQL**, **HTML**, **CSS**, and **JavaScript**.

---

## 🚀 Features

### Student Module
- View available courses with real-time seat availability
- Register for courses (with duplicate and seat limit validation)
- Drop registered courses
- View current course enrollments

### Faculty/Department Module
- View enrollment statistics for each course
- View list of students enrolled in specific courses
- Monitor course demand and capacity

### Admin Module
- System-wide statistics dashboard
- Course demand analysis
- Registration activity logs
- Department-wise statistics
- CRUD for students, courses, and departments

---

## 🛠️ Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Backend:** PHP 7.4+
- **Database:** MySQL 8.0+
- **Server:** Apache/Nginx (XAMPP, WAMP, or Laragon recommended for local development)

---

## 🗄️ Database Features

- **Stored Procedures:** `register_student_course()`, `drop_student_course()`
- **Functions:** `get_available_seats()`, `is_student_registered()`
- **Triggers:** Automatic logging of registration/drop actions
- **Constraints:** Unique registrations, seat limits, department associations

---

## 📦 Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- phpMyAdmin (optional, for database management)

### 1. Clone/Download the Project
```bash
git clone <repository-url>
cd course-registration-system
```

### 2. Database Setup
1. Start your MySQL server.
2. Open phpMyAdmin or your preferred MySQL client.
3. Import the SQL file:
   ```sql
   -- Import the complete_query.sql file:
   sql/complete_query.sql
   ```

### 3. Configure Database Connection
1. Open `backend/config/db.php`
2. Update the database credentials:
   ```php
   private $host = 'localhost';
   private $db_name = 'course_registration_system';
   private $username = 'your_mysql_username';
   private $password = 'your_mysql_password';
   ```

### 4. Deploy to Web Server
Copy the entire project folder to your web server directory:
- **XAMPP:** `htdocs/course-registration-system/`
- **WAMP/Laragon:** `www/course-registration-system/`

### 5. Access the Application
Open your web browser and navigate to:
```
http://localhost/course-registration-system/frontend/pages/login.html
```

---

## 📁 Project Structure

```
course-registration-system/
├── backend/
│   ├── config/
│   │   └── db.php
│   └── controllers/
│       ├── fetch_courses.php
│       ├── fetch_students.php
│       ├── fetch_enrollments.php
│       ├── register.php
│       ├── drop.php
│       └── admin_crud.php
├── frontend/
│   ├── css/
│   │   └── styles.css
│   ├── js/
│   │   ├── app.js
│   │   └── auth.js
│   └── pages/
│       ├── index.html
│       ├── register.html
│       ├── drop.html
│       ├── view_enrollments.html
│       └── admin.html
├── sql/
│   └── complete_query.sql
└── README.md
```

---

## 👨‍💻 Usage Guide

### For Students
- **View Courses:** Home page
- **Register for Course:** "Register for Course" page
- **Drop Course:** "Drop Course" page
- **View Enrollments:** "View Enrollments" page

### For Faculty
- **Course Statistics:** Admin panel
- **Student Lists:** Filter enrollments by course

### For Administrators
- **Dashboard:** Admin panel
- **Course Demand:** Analyze demand in admin panel
- **Activity Logs:** Monitor registration/drop logs
- **Department Stats:** View by department

---

## 🔗 API Endpoints

### GET
- `/backend/controllers/fetch_courses.php` — All courses with availability
- `/backend/controllers/fetch_students.php` — All students
- `/backend/controllers/fetch_enrollments.php` — All enrollments
- `/backend/controllers/fetch_enrollments.php?student_id=X` — Enrollments for a student
- `/backend/controllers/fetch_enrollments.php?course_id=X` — Enrollments for a course

### POST
- `/backend/controllers/register.php` — Register student for course
- `/backend/controllers/drop.php` — Drop student from course

---

## 🧪 Testing

### Sample Data Included
- 5 Departments (CS, IT, SE, DS, Cybersecurity)
- 10+ Students
- 15 Courses
- Sample registrations

### Test Scenarios
- Register a student for a course
- Attempt duplicate registration
- Register for a full course
- Drop a course
- View admin statistics

---

## 🚨 Troubleshooting

1. **Database Connection Error**
   - Check MySQL server is running
   - Verify credentials in `backend/config/db.php`
   - Ensure database and tables exist

2. **CORS Errors**
   - Access via `http://localhost`, not `file://`
   - CORS headers are set in PHP files

3. **404 Errors on API Calls**
   - Verify file paths in JS
   - Check PHP files exist in correct directories

4. **Stored Procedures Not Working**
   - Ensure MySQL user has EXECUTE privileges
   - Check that procedures were created successfully

---

## 🔒 Security Considerations

- Input validation on both client and server
- Prepared statements to prevent SQL injection
- CORS headers configured
- Error handling to prevent information disclosure

---

## 🚀 Future Enhancements

- User authentication and session management
- Email notifications
- Course prerequisites
- Waiting list functionality
- Mobile-responsive improvements
- Real-time updates

---

## 📝 License

This project is created for educational purposes as part of ICST University projectwork.

---

## 👥 Contributors

- [Mohammed Aswin] — Full-stack development
- [Fathima Nifra ] — Database design
- [Fathima Aafrin] — Frontend development
- [Mohammed HAzeem] — Testing and documentation
- [Fathima Hilma] — UI/UX designing


---

## 📞 Support

For technical support or questions:
- Email: aswinmohammed2021@gmail.com
- Course Instructor: [Mis.Vinothini ]
- Office Hours: [Database Management System]

---

**Note:** This system is for educational use and create by click 2 Entroll team Members.
