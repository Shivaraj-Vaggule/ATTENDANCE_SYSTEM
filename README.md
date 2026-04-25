# Student Attendance Management System

A web-based Student Attendance Management System built using PHP, MySQL, HTML, CSS, and JavaScript. This application streamlines attendance tracking, student management, and academic administration for educational institutions. It provides dedicated dashboards for administrators, teachers, and students.

---

## 📁 Project Structure

```text
ATTENDANCE_SYSTEM/
├── admin/                          # Administrator module
│   ├── assign_teacher.php
│   ├── change_password.php
│   ├── dashboard.php
│   ├── departments.php
│   ├── get_semesters.php
│   ├── get_students.php
│   ├── get_subjects.php
│   ├── semesters.php
│   ├── students.php
│   ├── subjects.php
│   ├── teachers.php
│   └── db.txt
│
├── student/                        # Student module
│   ├── change_password.php
│   ├── dashboard.php
│   ├── export_pdf.php
│   ├── profile.php
│   └── view_attendance.php
│
├── teacher/                        # Teacher module
│   ├── attendance_calendar.php
│   ├── attendance_report.php
│   ├── dashboard.php
│   ├── export_excel.php
│   ├── export_report.php
│   ├── mark_attendance.php
│   ├── monthly_report.php
│   ├── save_attendance.php
│   └── student_percentage.php
│
├── assets/                         # CSS stylesheets
│   ├── admin.css
│   ├── login.css
│   ├── student_dashboard.css
│   ├── students.css
│   ├── style.css
│   └── teacher_dashboard.css
│
├── config/                         # Database configuration
│   └── db.php
│
├── js/                             # JavaScript files
│   └── script.js
│
├── uploads/                        # Uploaded student images
│   └── students/
│
├── index.php                       # redirects to main page
├── login.php                       # Main login page
├── signup.php                      # User registration page
└── logout.php                      # Logout handler
```

---

## ✨ Features

### 👨‍💼 Admin Module

* Secure administrator login
* Manage departments, semesters, and subjects
* Add, edit, and delete student records
* Add, edit, and delete teacher records
* Assign teachers to subjects and classes
* View overall attendance statistics
* Change admin password

### 👨‍🏫 Teacher Module

* Secure teacher login
* View assigned subjects and classes
* Mark daily student attendance
* Update attendance records
* View class-wise attendance reports
* Track student attendance history

### 👨‍🎓 Student Module

* Secure student login
* View personal attendance records
* Check subject-wise attendance percentage
* Access attendance history
* View profile details

---

## 🛠️ Technologies Used

* **Frontend:** HTML5, CSS3, JavaScript
* **Backend:** PHP
* **Database:** MySQL
* **Server:** Apache (XAMPP/WAMP/LAMP)

---

## 🚀 Installation Guide

### Prerequisites

* PHP 7.4 or higher
* MySQL 5.7 or higher
* Apache Server
* XAMPP / WAMP / LAMP

### Steps to Install

1. Clone or download this repository.
2. Copy the project folder to your server directory:

   * XAMPP: `htdocs/`
   * WAMP: `www/`
3. Create a new MySQL database (e.g., `attendance_system`).
4. Import the SQL database file into MySQL.
5. Update database credentials in:

   ```php
   config/db.php
   ```
6. Start Apache and MySQL services.
7. Open your browser and visit:

   ```
   http://localhost/ATTENDANCE_SYSTEM/
   ```

---

## 🔐 Default User Roles

* **Admin** – Full system access
* **Teacher** – Attendance management access
* **Student** – Personal attendance viewing access

---

## 📊 Core Modules

* User Authentication
* Student Management
* Teacher Management
* Department Management
* Semester Management
* Subject Management
* Attendance Tracking
* Attendance Reports
* Profile Management
* Password Management

---

## 🔒 Security Features

* Password hashing using PHP `password_hash()`
* Session-based authentication
* Role-based access control
* SQL injection prevention using prepared statements
* Secure file upload handling

---

## 📷 Screens Included

* Login Page
* Admin Dashboard
* Teacher Dashboard
* Student Dashboard
* Attendance Marking Interface
* Attendance Reports

---

## 🌟 Future Enhancements

* Email notifications for low attendance
* SMS alerts to parents
* Export attendance reports to PDF/Excel
* Advanced analytics dashboard
* Biometric integration
* Mobile application support

---

## 👨‍💻 Author

Developed as a complete academic attendance management solution.

---

## 📄 License

This project is open-source and available for educational and personal use.

---

## 🤝 Contribution

Contributions, suggestions, and improvements are welcome. Feel free to fork the repository and submit a pull request.
