<?php
// Get the filename of the current script to set the active class
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillBridge - Connect & Learn</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/skill_builder/Skill_Share/skill_share/Skillshare/assets/css/style.css">
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: #f5f7fa;
            color: #333;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Elegant and Modern Header Styles */
        .site-header {
            background-color: #E8F5E9; /* Light green background */
            color: #333; /* Dark text */
            padding: 20px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); /* Subtle shadow */
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid #e0e0e0; /* Light border */
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px; /* Increased height for better spacing */
        }

        .logo {
            font-size: 28px; /* Larger logo */
            font-weight: bold;
            color: #333;
            text-decoration: none;
            letter-spacing: -0.5px; /* Slightly tighter letter spacing */
        }

        .logo span {
            color: #2E7D32; /* Green accent color */
        }

        /* Modern Navigation Styles */
        .main-nav {
            display: flex;
            gap: 25px; /* Increased gap */
            height: 100%;
            align-items: center;
        }

        .main-nav a {
            color: #E9C46A; /* Yellow text color */
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            height: 40px;
            margin: 0 5px;
            background: transparent;
        }

        .main-nav a i {
            margin-right: 8px;
            color: #E9C46A; /* Yellow icon color */
        }

        .main-nav a:hover, .main-nav a.active {
            color: #F4A261; /* Slightly darker yellow on hover */
            font-weight: 600;
            transform: translateY(-2px);
        }

        /* Specific styles for the logout button */
        .main-nav .btn-outline {
            background: transparent !important;
            color: #333 !important;
            border: 2px solid #333 !important;
        }

        .main-nav .btn-outline:hover {
            background: #333 !important;
            color: #fff !important;
        }

        /* Main content container */
        .main-content {
            flex: 1 1 auto;
            padding: 30px 0; /* Increased top and bottom padding */
        }

        /* Footer styles */
        .site-footer {
            background-color:rgb(102, 106, 112); /* Darker footer */
            color: #f8f9fa; /* Light footer text */
            padding: 20px 0;
            text-align: center;
            margin-top: 40px; /* Increased margin */
            box-shadow: 0 -4px 12px rgba(0,0,0,0.05); /* Subtle shadow */
            flex-shrink: 0;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
                height: auto;
                padding: 15px 0;
            }

            .main-nav {
                margin-top: 20px;
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }

            .main-nav a {
                margin: 5px;
                padding: 8px 12px;
                height: auto;
                color: #2E7D32;
                text-decoration: none;
                font-weight: 500;
                margin-left: 30px;
                position: relative;
                transition: color 0.3s ease;
            }

            .logo {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="/skill_builder/Skill_Share/skill_share/Skillshare/pages/dashboard.php" class="logo">
                <i class="fas fa-hands-helping"></i>
                <span>Skill<span style="color: var(--secondary);">Bridge</span></span>
            </a>
            <nav class="main-nav">
                <a href="/skill_builder/Skill_Share/skill_share/Skillshare/pages/dashboard.php" class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="/skill_builder/Skill_Share/skill_share/Skillshare/pages/post_skill.php" class="<?php echo ($currentPage == 'post_skill.php') ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle"></i> Post Skill
                </a>
                <a href="/skill_builder/Skill_Share/skill_share/Skillshare/pages/my_skills.php" class="<?php echo ($currentPage == 'my_skills.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tasks"></i> My Skills
                </a>
                <a href="/skill_builder/Skill_Share/skill_share/Skillshare/pages/inbox.php" class="<?php echo ($currentPage == 'inbox.php') ? 'active' : ''; ?>">
                    <i class="fas fa-inbox"></i> Inbox
                </a>
                <a href="/skill_builder/Skill_Share/skill_share/Skillshare/pages/chat.php" class="<?php echo ($currentPage == 'chat.php') ? 'active' : ''; ?>">
                    <i class="fas fa-comments"></i> Chat
                </a>
                <a href="/skill_builder/Skill_Share/skill_share/Skillshare/pages/search.php" class="<?php echo ($currentPage == 'search.php') ? 'active' : ''; ?>">
                    <i class="fas fa-search"></i> Search
                </a>
                <a href="/skill_builder/Skill_Share/skill_share/Skillshare/includes/logout.php" class="btn btn-outline" style="color: white; border-color: white;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
    </header>

    <div class="main-content">
        <div class="container">
            
    </div>

   

</body>
</html>