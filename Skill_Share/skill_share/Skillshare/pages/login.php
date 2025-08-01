<?php
session_start();
require_once('../includes/db.php'); // Your DB connection file

$error = '';
$loginFailed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if ($row = mysqli_fetch_assoc($query)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            header("Location: dashboard.php"); // ✅ Redirect to dashboard on success
            exit;
        } else {
            $error = "❌ Invalid email or password.";
            $loginFailed = true;
        }
    } else {
        $error = "❌ Invalid email or password.";
        $loginFailed = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - SkillBridge</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <style>
        body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    overflow: hidden;
    background: none;
}

body::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    background: url('https://images.pexels.com/photos/10526880/pexels-photo-10526880.jpeg') no-repeat center center/cover;
    opacity: 0.9; /* Adjust the background image opacity here */
    z-index: -1;
}

.login-container {
    background-color: #fff;
    padding: 40px;
    width: 100%;
    max-width: 400px;
    border-radius: 10px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    text-align: center;
    z-index: 1;
}

/* Rest of your styles unchanged... */
.login-container h2 {
    margin-bottom: 10px;
    color: #333;
}

.login-container p {
    color: #666;
    font-size: 14px;
    margin-bottom: 25px;
}

form {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 15px;
    text-align: left;
}

label {
    font-size: 14px;
    color: #333;
    margin-bottom: 5px;
    font-weight: 500;
}

input[type="text"],
input[type="email"],
input[type="password"] {
    padding: 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    width: 100%;
    box-sizing: border-box;
}

.login-container button {
    width: 100%;
    padding: 12px;
    background-color: #2E7D32;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #3E8E41;
}

.register-link {
    margin-top: 20px;
    text-align: center;
    color: #2E7D32;
}

.register-link a {
    color: #2E7D32;
    text-decoration: none;
}

.register-link a:hover {
    text-decoration: underline;
}
.input-error {
    border: 1px solid red;
    background-color: #ffe6e6;
}

/* Error message styling */
.error-message {
    color: red;
    font-size: 14px;
    margin-top: 10px;
}
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Welcome Back!</h2>
        <p>Please login to your SkillShare account</p>

        <form method="POST" action="">
            <label for="email">Email</label>
            <input 
                type="email" 
                name="email" 
                required 
                placeholder="Enter your email" 
                class="<?php echo ($loginFailed ? 'input-error' : ''); ?>"
            >

            <label for="password">Password</label>
            <input 
                type="password" 
                name="password" 
                id="password" 
                required 
                placeholder="Enter your password" 
                class="<?php echo ($loginFailed ? 'input-error' : ''); ?>"
            >

            <!-- Show Password toggle -->
            <div style="margin-top: 5px;">
                <input type="checkbox" id="togglePassword">
                <label for="togglePassword" style="font-size: 14px;">Show Password</label>
            </div>

            <!-- Error message -->
            <?php if (!empty($error)): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>

            <button type="submit" name="login">Login</button>
        </form>


        <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
    </div>

    <script>
    const toggle = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    toggle.addEventListener('change', function () {
        password.type = this.checked ? 'text' : 'password';
    });
</script>

</body>
</html>
