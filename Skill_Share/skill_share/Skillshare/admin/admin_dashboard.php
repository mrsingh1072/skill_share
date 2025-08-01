<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Welcome, Admin!</h1>
    <ul>
        <li><a href="manage_skills.php">Manage Skills</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</body>
</html>
