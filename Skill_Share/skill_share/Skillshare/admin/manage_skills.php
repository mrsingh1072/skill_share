<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Delete skill
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM skills WHERE id=$id");
    header("Location: manage_skills.php");
}

$result = mysqli_query($conn, "SELECT skills.*, users.name FROM skills JOIN users ON skills.user_id = users.id ORDER BY skills.created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Skills</title>
</head>
<body>
    <h2>All Posted Skills</h2>
    <table border="1" cellpadding="10">
        <tr>
            <th>Skill Title</th>
            <th>Posted By</th>
            <th>Category</th>
            <th>Type</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= htmlspecialchars($row['type']) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td><a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>
