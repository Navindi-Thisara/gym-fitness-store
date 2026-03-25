<?php
if(session_status() == PHP_SESSION_NONE) session_start();
?>
<header class="main-header">
    <div class="logo">Gym & Fitness Store</div>
    <nav>
        <ul>
            <?php if(isset($_SESSION['user'])): ?>
                <?php if($_SESSION['user']['role'] == 'admin'): ?>
                    <li><a href="/admin/dashboard.php">Dashboard</a></li>
                    <li><a href="/auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="/user/home.php">Home</a></li>
                    <li><a href="/user/cart.php">Cart</a></li>
                    <li><a href="/auth/logout.php">Logout</a></li>
                <?php endif; ?>
            <?php else: ?>
                <li><a href="/auth/login.php" class="btn-header">Login</a></li>
                <li><a href="/auth/register.php" class="btn-header">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="menu-toggle">&#9776;</div>
</header>