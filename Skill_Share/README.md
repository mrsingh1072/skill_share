# SkillShare

SkillShare is a web application designed to connect individuals who want to share their skills with those who are looking to learn new ones. It facilitates a community where users can offer their expertise in various categories or request help in areas they wish to develop.

## Features

*   **User Authentication**: Register, Login, and Logout functionality.
*   **Skill Management**: Users can post skills they offer or skills they want to learn (requests).
*   **Skill Search & Discovery**: Browse and search for available skills.
*   **Direct Messaging**: Users can connect and chat with each other.
*   **Connection Requests**: A system for users to initiate connections related to skills.
*   **Skill Reviews**: Users can review skills (though the implementation may vary).
*   **Admin Panel**: Basic administration features (e.g., managing skills, users).

## Technologies Used

*   **Backend**: PHP
*   **Database**: MySQL (managed with phpMyAdmin)
*   **Web Server**: Apache (via XAMPP)
*   **Frontend**: HTML, CSS, JavaScript

## Local Setup Instructions

Follow these steps to get SkillShare up and running on your local machine using XAMPP:

### Prerequisites

*   **XAMPP**: Ensure you have XAMPP installed. XAMPP provides Apache, MySQL, and PHP in one package. You can download it from [apachefriends.org](https://www.apachefriends.org/index.html).

### 1. Project Placement

*   Place the `Skillshare` folder directly inside your XAMPP's `htdocs` directory (e.g., `C:\xampp\htdocs\skill_share\Skillshare`). Based on your current setup, it appears you already have it in `C:\xampp\htdocs\skill_share\Skillshare`.

### 2. Start XAMPP Services

*   Open your XAMPP Control Panel.
*   Start the **Apache** and **MySQL** modules.

### 3. Database Setup

1.  **Create the Database**:
    *   Open your web browser and go to `http://localhost/phpmyadmin/`.
    *   Click on the "Databases" tab.
    *   In the "Create database" section, enter `skillshare` as the database name.
    *   Click the "Create" button.

2.  **Import Data**:
    *   In phpMyAdmin, select the newly created `skillshare` database from the left sidebar.
    *   Click on the "Import" tab at the top.
    *   Click "Choose File" and select the `Skillshare Database[1].sql` file from your project directory (`C:\xampp\htdocs\skill_share\Skillshare\Skillshare Database[1].sql`).
    *   Scroll down and click the "Go" button to execute the SQL queries. This will create all necessary tables and populate them with sample data.

### 4. Configure Database Connection

The database connection details are located in `Skillshare/includes/db.php`. Verify that these settings match your local XAMPP MySQL configuration:

```php
<?php
$host = "localhost";
$user = "root";
$password = ""; // Typically empty for XAMPP root user
$database = "skillshare";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```
*No changes should be needed if you are using default XAMPP settings.*

### 5. Access the Application

*   Open your web browser and navigate to the following URL:
    `http://localhost/skill_share/Skillshare/pages/index.php`

You should now see the SkillShare application running.

## Default Admin Credentials

If you need to access the admin panel, you can use one of the default user accounts that was imported with the database:

**Admin User (if available in your `users` table initial data):**
*   **Email:** `admin@email.com`
*   **Password:** `password` (Note: This is the plaintext password that hashes to the value in the database. In a real application, you'd register to create a new hashed password.)

**Other Sample Users:**
Check the `users` table in phpMyAdmin for other sample user emails and their corresponding (hashed) passwords. You might need to register a new user to easily log in if the provided default passwords don't work due to hashing differences, or use a tool to generate the hash if you know the password.
