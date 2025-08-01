<?php
require_once('../includes/auth.php');
require_once('../includes/db.php');
require_once('../includes/header.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verify session and user_id
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user name from database if not in session
if (!isset($_SESSION['user_name'])) {
    $user_sql = "SELECT name FROM users WHERE id = ?";
    $user_stmt = mysqli_prepare($conn, $user_sql);
    mysqli_stmt_bind_param($user_stmt, "i", $user_id);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    
    if ($user = mysqli_fetch_assoc($user_result)) {
        $_SESSION['user_name'] = $user['name'];
    } else {
        $_SESSION['user_name'] = 'User'; // Default if not found
    }
}

// Fetch skills shared by the user count
$skills_shared_count = 0;
$skills_sql = "SELECT COUNT(*) as count FROM skills WHERE user_id = ?";
$skills_stmt = mysqli_prepare($conn, $skills_sql);
if ($skills_stmt) {
    mysqli_stmt_bind_param($skills_stmt, "i", $user_id);
    mysqli_stmt_execute($skills_stmt);
    $skills_result = mysqli_stmt_get_result($skills_stmt);
    if ($row = mysqli_fetch_assoc($skills_result)) {
        $skills_shared_count = $row['count'];
    }
    mysqli_stmt_close($skills_stmt);
} else {
    error_log("Failed to prepare skills count query: " . mysqli_error($conn));
}

$new_messages_count = 0;
$messages_sql = "SELECT COUNT(*) as count FROM messages WHERE to_id = ? AND status = 'unread'";
$messages_stmt = mysqli_prepare($conn, $messages_sql);
if ($messages_stmt) {
    mysqli_stmt_bind_param($messages_stmt, "i", $user_id);
    mysqli_stmt_execute($messages_stmt);
    $messages_result = mysqli_stmt_get_result($messages_stmt);
    if ($row = mysqli_fetch_assoc($messages_result)) {
        $new_messages_count = $row['count'];
    }
    mysqli_stmt_close($messages_stmt);
} else {
     error_log("Failed to prepare new messages count query: " . mysqli_error($conn));
}

// Fetch accepted connections
$connections_sql = "
    SELECT 
        u.id,
        u.name,
        m.timestamp AS connected_since
    FROM 
        messages m
    JOIN 
        users u ON (m.from_id = u.id OR m.to_id = u.id)
    WHERE 
        ((m.from_id = ? AND m.to_id = u.id) OR (m.to_id = ? AND m.from_id = u.id))
        AND m.status = 'accepted'
    GROUP BY
        u.id
    ORDER BY 
        connected_since DESC
";

$connections_stmt = mysqli_prepare($conn, $connections_sql);
mysqli_stmt_bind_param($connections_stmt, "ii", $user_id, $user_id);
mysqli_stmt_execute($connections_stmt);
$connections_result = mysqli_stmt_get_result($connections_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SkillBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary: #2A9D8F;
            --primary-light: #76C7BA;
            --primary-dark: #1D7874;
            --secondary: #E9C46A;
            --accent: #F4A261;
            --danger: #E76F51;
            --dark: #264653;
            --light: #F8F9FA;
            --gray: #6C757D;
            --light-gray: #E9ECEF;
            --white: #FFFFFF;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
            --text-dark: #2b2d42;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .welcome-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }

        .welcome-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            transform: rotate(30deg);
        }

        .welcome-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.2rem;
            font-weight: 700;
            position: relative;
        }

        .welcome-header p {
            margin: 0;
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            transition: var(--transition);
            box-shadow: var(--box-shadow);
            border-left: 4px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            background: var(--primary);
            flex-shrink: 0;
        }

        .stat-icon.tasks { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); }
        .stat-icon.messages { background: linear-gradient(135deg, var(--accent), #F4A261); }
        .stat-icon.connections { background: linear-gradient(135deg, var(--secondary), #E9C46A); }

        .stat-content h3 {
            margin: 0 0 0.25rem 0;
            font-size: 0.9rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .stat-number {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.2;
        }

        .action-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            min-width: 200px;
            justify-content: center;
        }

        .action-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .action-btn i {
            font-size: 1.2rem;
        }

        .dashboard-section {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .dashboard-section h2 {
            font-size: 1.8em;
            color: #212529;
            margin-bottom: 25px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .dashboard-section h2 i {
            margin-right: 12px;
            color: var(--primary-color);
        }

        /* Connections Grid */
        .connections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .connection-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            text-align: center;
        }

        .connection-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .avatar {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }

        .connection-card h4 {
            margin: 0.5rem 0;
            color: var(--text-dark);
        }

        .connection-date {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .chat-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.3rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .chat-btn:hover {
            background: var(--secondary-color);
        }

        /* Skills Grid */
        .skills-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .skill-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .skill-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .skill-card img {
            display: block;
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }

        .skill-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .skill-content h4 {
            font-size: 1.25em;
            color: #212529;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .skill-content p {
            font-size: 0.95em;
            color: #555;
            line-height: 1.5;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .skill-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 10px;
            border-top: 1px solid #f0f0f0;
        }

        .skill-meta span {
            font-size: 0.9em;
            color: #7f8c8d;
            display: flex;
            align-items: center;
        }

        .skill-meta span i {
            margin-right: 6px;
            color: #3498db;
        }

        .interest-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.85em;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .interest-btn:hover {
            background-color: #2980b9;
        }

        .interest-btn.active {
            background-color: #27ae60;
        }

        /* Activity Section */
        .activity-list {
            margin: 1rem 0;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            margin: 0.5rem 0;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            background: #e9ecef;
        }

        .activity-item i {
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .activity-item p {
            margin: 0;
            flex-grow: 1;
        }

        .activity-item small {
            color: #666;
            font-size: 0.8rem;
        }

        .activity-form {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .activity-form input {
            flex-grow: 1;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
        }

        .activity-form button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0 1.5rem;
            border-radius: 0.3rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .activity-form button:hover {
            background: var(--secondary-color);
        }

        /* Resources Grid */
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .resource-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .resource-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .resource-card h4 {
            color: var(--text-dark);
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .resource-card h4 i {
            color: var(--primary-color);
        }

        .resource-card p {
            color: #666;
            margin-bottom: 1.5rem;
        }

        .resource-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.3rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .resource-btn:hover {
            background: var(--secondary-color);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
            grid-column: 1 / -1;
        }

        .empty-state button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 0.3rem;
            margin-top: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .empty-state button:hover {
            background: var(--secondary-color);
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 1rem;
            margin: 2rem 0;
            flex-wrap: wrap;
            justify-content: center;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .action-btn i {
            font-size: 1rem;
        }

        .action-btn.primary {
            background: #E9C46A; /* Yellow from SkillBridge logo */
            color: #000; /* Black text for better contrast */
            box-shadow: 0 4px 6px rgba(233, 196, 106, 0.3);
            font-weight: 600;
        }

        .action-btn.primary:hover {
            background: #F4A261; /* Slightly darker/orange on hover */
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(233, 196, 106, 0.4);
        }

        .action-btn.secondary {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .action-btn.secondary:hover {
            background: rgba(42, 157, 143, 0.1);
            transform: translateY(-2px);
        }

        /* Section Header */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-header h2 i {
            color: var(--primary);
        }

        .view-all {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            transition: all 0.2s ease;
        }

        .view-all:hover {
            color: var(--primary-dark);
            gap: 0.5rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            background: #f8f9fa;
            border-radius: 12px;
            margin: 1rem 0;
        }

        .empty-icon {
            font-size: 2.5rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            margin: 0.5rem 0;
            color: var(--dark);
            font-size: 1.25rem;
        }

        .empty-state p {
            color: var(--gray);
            margin-bottom: 1.5rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border-radius: 50px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            border: 2px solid transparent;
            cursor: pointer;
        }

        .btn-primary {
            background: #E9C46A; /* Yellow from SkillBridge logo */
            color: #000; /* Black text for better contrast */
            box-shadow: 0 4px 6px rgba(233, 196, 106, 0.3);
            font-weight: 600;
            border: 2px solid #E9C46A;
        }

        .btn-primary:hover {
            background: #F4A261; /* Slightly darker/orange on hover */
            border-color: #F4A261;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(233, 196, 106, 0.4);
        }

        .btn-outline-primary {
            background: transparent;
            color: #E9C46A; /* Yellow from SkillBridge logo */
            border-color: #E9C46A;
            font-weight: 500;
        }

        .btn-outline-primary:hover {
            background: rgba(233, 196, 106, 0.1);
            transform: translateY(-2px);
            color: #F4A261;
            border-color: #F4A261;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .action-btn, .btn {
                width: 100%;
                justify-content: center;
                padding: 0.8rem 1rem;
            }
            
            .welcome-header h1 {
                font-size: 1.8rem;
            }
            
            .welcome-header p {
                font-size: 1rem;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .view-all {
                align-self: flex-end;
            }
        }
        
        @media (max-width: 480px) {
            .connections-grid {
                grid-template-columns: 1fr;
            }
            
            .stat-card {
                flex-direction: column;
                text-align: center;
                padding: 1.25rem;
            }
            
            .stat-icon {
                margin-bottom: 0.75rem;
            }
        }
    </style>
</head>
<body>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <!-- Welcome Header -->
        <header class="welcome-header">
            <h1>Welcome back, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?>! 👋</h1>
            <p>Here's what's happening with your SkillBridge account today</p>
        </header>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon tasks">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-content">
                    <h3>Skills Shared</h3>
                    <p class="stat-number"><?php echo $skills_shared_count; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon messages">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-content">
                    <h3>New Messages</h3>
                    <p class="stat-number"><?php echo $new_messages_count; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon connections">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3>Connections</h3>
                    <p class="stat-number"><?php echo mysqli_num_rows($connections_result); ?></p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="post_skill.php" class="action-btn primary">
                <i class="fas fa-plus"></i> Share a Skill
            </a>
            <a href="search.php" class="action-btn secondary">
                <i class="fas fa-search"></i> Find Skills
            </a>
        </div>

        <!-- Recent Connections -->
        <section class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-link"></i> Your Connections</h2>
                <a href="connections.php" class="view-all">View All</a>
            </div>
            
            <?php if (mysqli_num_rows($connections_result) > 0): ?>
                <div class="connections-grid">
                    <?php 
                    $count = 0;
                    while ($connection = mysqli_fetch_assoc($connections_result)): 
                        if ($count++ >= 4) break; // Show only first 4 connections
                    ?>
                        <div class="connection-card">
                            <div class="avatar" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark));">
                                <?php echo strtoupper(substr($connection['name'], 0, 1)); ?>
                            </div>
                            <h4><?php echo htmlspecialchars($connection['name']); ?></h4>
                            <p class="connection-date">Connected <?php echo date('M j, Y', strtotime($connection['connected_since'])); ?></p>
                            <a href="chat.php?user_id=<?php echo $connection['id']; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-comment"></i> Message
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-users-slash"></i>
                    </div>
                    <h3>No connections yet</h3>
                    <p>Connect with other users to share skills and collaborate.</p>
                    <a href="search.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Find People
                    </a>
                </div>
            <?php endif; ?>
        </section>

        <!-- Recent Activity -->
        <section class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-history"></i> Recent Activity</h2>
                <a href="activity.php" class="view-all">View All</a>
            </div>
            
            <div class="activity-list">
                <!-- Sample activity items - In a real app, these would come from the database -->
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-bell"></i>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Featured Skills Logic with Local Storage
    const skillsContainer = document.getElementById('featured-skills');
    const defaultSkills = [
        {
            id: 'web-dev-1',
            img: "../uploads/web_dev.jpg",
            alt: "Web Development Concept Image",
            title: "Modern Web Development",
            description: "Master HTML5, CSS3, Flexbox, Grid, and modern JavaScript frameworks.",
            learners: 112,
            interested: false
        },
        {
            id: 'guitar-2',
            img: "../uploads/guitar.webp",
            alt: "Acoustic Guitar",
            title: "Acoustic Guitar Basics",
            description: "Learn essential chords, strumming patterns, and your first few songs.",
            learners: 85,
            interested: false
        },
        {
            id: 'data-analysis-3',
            img: "../uploads/da.png",
            alt: "Data Analysis Charts",
            title: "Introduction to Data Analysis",
            description: "Understand data concepts, basic statistics, and tools like Excel or Python Pandas.",
            learners: 98,
            interested: false
        },
        {
            id: 'graphic-design-4',
            img: "../uploads/graphic_des.jpg",
            alt: "Graphic Design Tools",
            title: "Graphic Design Fundamentals",
            description: "Explore principles of design, color theory, typography, and layout techniques.",
            learners: 76,
            interested: false
        },
        {
            id: 'cooking-5',
            img: "../uploads/cooking.jpeg",
            alt: "Cooking Ingredients",
            title: "Basic Culinary Skills",
            description: "Learn essential knife skills, cooking methods, and how to follow recipes.",
            learners: 55,
            interested: false
        }
    ];

    let storedSkills = localStorage.getItem('featuredSkills');
    let skills = storedSkills ? JSON.parse(storedSkills) : defaultSkills;

    function renderSkills() {
        skillsContainer.innerHTML = skills.map(skill => `
            <div class="skill-card ${skill.interested ? 'interested' : ''}">
                <img src="${skill.img}" alt="${skill.alt}">
                <div class="skill-content">
                    <h4>${skill.title}</h4>
                    <p>${skill.description}</p>
                    <div class="skill-meta">
                        <span><i class="fas fa-users"></i> ${skill.learners + (skill.interested ? 1 : 0)} learners</span>
                        <button class="interest-btn ${skill.interested ? 'active' : ''}" data-skill-id="${skill.id}">
                            ${skill.interested ? 'Interested' : 'Express Interest'}
                        </button>
                    </div>
                </div>
            </div>
        `).join('');

        // Add event listeners to the "Express Interest" buttons
        const interestButtons = document.querySelectorAll('.interest-btn');
        interestButtons.forEach(button => {
            button.addEventListener('click', function() {
                const skillId = this.dataset.skillId;
                const skillIndex = skills.findIndex(skill => skill.id === skillId);
                if (skillIndex !== -1) {
                    skills[skillIndex].interested = !skills[skillIndex].interested;
                    localStorage.setItem('featuredSkills', JSON.stringify(skills));
                    renderSkills(); // Re-render to update the UI
                }
            });
        });
    }

    renderSkills();

    // LocalStorage for Recent Activity
    const activityList = document.getElementById('recent-activity');
    let activities = JSON.parse(localStorage.getItem('dashboardActivities') || '[]');

    function renderActivities() {
        activityList.innerHTML = activities.map(activity => `
            <div class="activity-item">
                <i class="fas fa-sticky-note"></i>
                <div>
                    <p>${activity.text}</p>
                    <small>${new Date(activity.date).toLocaleString()}</small>
                </div>
            </div>
        `).join('');
    }

    renderActivities();

    // Add new activity
    document.getElementById('add-activity').addEventListener('submit', (e) => {
        e.preventDefault();
        const noteInput = e.target.querySelector('input');
        const text = noteInput.value.trim();
        if (text) {
            const newActivity = {
                text,
                date: new Date().toISOString()
            };
            activities.unshift(newActivity);
            localStorage.setItem('dashboardActivities', JSON.stringify(activities));
            renderActivities();
            noteInput.value = ''; // Clear the input field
        }
    });
});
</script>

<?php require_once('../includes/footer.php'); ?>
</body>
</html>