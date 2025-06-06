<?php
// Safely start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

// Display messages if available
if (isset($message) && is_array($message)) {
    foreach ($message as $msg) {
        echo '
        <div class="message">
            <span>' . htmlspecialchars($msg) . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>
        ';
    }
}
?>

<header class="header">
    <div class="flex">
        <a href="admin_page.php" class="logo">Admin<span>Panel</span></a>

        <nav class="navbar">
            <a href="admin_page.php">home</a>
            <a href="admin_products.php">products</a>
            <a href="admin_orders.php">orders</a>
            <a href="admin_users.php">users</a>
            <a href="admin_contacts.php">messages</a>
            <a href="admin_caretakers.php">caretakers</a>
            <a href="admin_reports.php">reports</a>
        </nav>

        <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <div id="user-btn" class="fas fa-user"></div>
        </div>

        <div class="account-box">
            <p>username : <span><?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Not logged in' ?></span></p>
            <p>email : <span><?= isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : 'Not logged in' ?></span></p>

            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
                <div style="display: flex; gap: 10px; margin-top: 1rem;">
                    <a href="logout.php" class="delete-btn">logout</a>
                    <a href="add_admin.php" class="option-btn" id="add-admin-btn">add admin</a>
                </div>
            <?php else: ?>
                <div style="margin-top: 1rem;">
                    new <a href="login.php">login</a> | <a href="register.php">register</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>
