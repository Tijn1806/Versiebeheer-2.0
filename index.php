<?php
session_start();
require 'connect.php'; // Update this line to use connect.php

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in username
$username = $_SESSION['username'];

// 1. Basic select - Get user information by username
$stmt1 = $pdo->prepare("SELECT id, username FROM users WHERE username = ?");
$stmt1->execute([$username]);
$user_info = $stmt1->fetch();

// 2. Count select - Count total number of users
$stmt2 = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$count_info = $stmt2->fetch();

// 3. Position select - Find user's position/rank by ID
$stmt3 = $pdo->prepare("
    SELECT position 
    FROM (
        SELECT id, username, (@row_number:=@row_number+1) AS position
        FROM users, (SELECT @row_number:=0) AS r
        ORDER BY id
    ) AS ranked
    WHERE username = ?
");
$stmt3->execute([$username]);
$position_info = $stmt3->fetch();

// 4. Advanced select - Get user info with LIKE pattern matching
$search_pattern = substr($username, 0, 2) . '%'; // First 2 characters + %
$stmt4 = $pdo->prepare("SELECT id, username FROM users WHERE username LIKE ? AND username != ?");
$stmt4->execute([$search_pattern, $username]);
$similar_users = $stmt4->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuizPro Dashboard</title>
    <style>
        .user-data {
            margin: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .notification {
            display: none;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .notification.show {
            display: block;
            animation: fadeOut 3s forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; }
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="right-section">
            <?php if (isset($_SESSION['username'])) { ?>
                <a href="logout.php" class="join-button">Logout</a>
            <?php } else { ?>
                <a href="login.php" class="login">Log In</a>
                <a href="register.php" class="join-button">Sign Up</a>
            <?php } ?>
        </div>
    </div>

    <!-- Notification -->
    <div id="loginNotification" class="notification">Welcome back, <?php echo $_SESSION['username']; ?>!</div>

    <!-- User Data Display -->
    <div class="user-data">
        <h2>User Information</h2>
        
        <!-- Method 1: Basic user info -->
        <div class="data-section">
            <h3>1. Your Account Details</h3>
            <p>User ID: <?php echo $user_info['id']; ?></p>
            <p>Username: <?php echo $user_info['username']; ?></p>
        </div>
        
        <!-- Method 2: Count info -->
        <div class="data-section">
            <h3>2. System Statistics</h3>
            <p>Total users in system: <?php echo $count_info['total_users']; ?></p>
        </div>
        
        <!-- Method 3: Position info -->
        <div class="data-section">
            <h3>3. Your Account Ranking</h3>
            <p>Your position among all users: <?php echo $position_info['position'] ?? 'Not available'; ?></p>
        </div>
        
        <!-- Method 4: Similar usernames -->
        <div class="data-section">
            <h3>4. Users With Similar Usernames</h3>
            <?php if (count($similar_users) > 0): ?>
                <table>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                    </tr>
                    <?php foreach ($similar_users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No users with similar usernames found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Show notification if logged in
        <?php if (isset($_SESSION['username'])) { ?>
            const notification = document.getElementById('loginNotification');
            notification.classList.add('show');
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        <?php } ?>
    </script>
</body>
</html>

