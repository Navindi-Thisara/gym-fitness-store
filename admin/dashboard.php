<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){
    header("Location: ../auth/login.php"); exit;
}
include("../config/db.php");

// Stats
$totalUsers    = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'];
$totalProducts = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$totalOrders   = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'] ?? 0;
$totalRevenue  = $conn->query("SELECT SUM(total_amount) as s FROM orders WHERE status='Pending' OR status='Completed'")->fetch_assoc()['s'] ?? 0;
$recentOrders  = $conn->query("SELECT o.*, u.name as uname FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | Gym Store</title>
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;}
body{margin:0;min-height:100vh;display:flex;flex-direction:column;font-family:'Arial',sans-serif;transition:background 0.3s,color 0.3s;}
body.light-mode{background:#f0f2f5;color:#222;}
body.dark-mode {background:#121212;color:#eee;}
.admin-wrap{flex:1;max-width:1200px;margin:0 auto;width:100%;padding:32px 24px 60px;}
.page-title{font-size:1.6rem;font-weight:700;margin:0 0 6px;color:#1a1a1a;display:flex;align-items:center;gap:10px;}
body.dark-mode .page-title{color:#f0f0f0;}
.page-title i{color:#28a745;}
.page-sub{color:#888;font-size:0.88rem;margin:0 0 28px;}
/* Stat cards */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:18px;margin-bottom:32px;}
.stat-card{background:#fff;border-radius:14px;padding:22px 24px;box-shadow:0 4px 18px rgba(0,0,0,0.07);display:flex;align-items:center;gap:16px;animation:fadeCard 0.4s ease both;}
body.dark-mode .stat-card{background:#1e1e1e;box-shadow:0 4px 18px rgba(0,0,0,0.4);}
@keyframes fadeCard{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
.stat-icon{width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fff;flex-shrink:0;}
.stat-icon.green{background:linear-gradient(135deg,#28a745,#218838);}
.stat-icon.blue {background:linear-gradient(135deg,#3498db,#2980b9);}
.stat-icon.orange{background:linear-gradient(135deg,#e67e22,#d35400);}
.stat-icon.purple{background:linear-gradient(135deg,#9b59b6,#8e44ad);}
.stat-info h3{margin:0 0 2px;font-size:1.5rem;font-weight:800;color:#1a1a1a;}
body.dark-mode .stat-info h3{color:#f0f0f0;}
.stat-info p{margin:0;font-size:0.82rem;color:#888;}
/* Quick links */
.quick-links{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:32px;}
.quick-card{background:#fff;border-radius:14px;padding:20px 22px;box-shadow:0 4px 18px rgba(0,0,0,0.07);text-decoration:none;display:flex;align-items:center;gap:14px;transition:transform 0.2s,box-shadow 0.2s;animation:fadeCard 0.4s ease both;}
body.dark-mode .quick-card{background:#1e1e1e;}
.quick-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,0.12);}
.quick-card i{font-size:1.6rem;color:#28a745;}
.quick-card span{font-size:0.95rem;font-weight:700;color:#1a1a1a;}
body.dark-mode .quick-card span{color:#f0f0f0;}
/* Recent orders table */
.section-card{background:#fff;border-radius:14px;box-shadow:0 4px 18px rgba(0,0,0,0.07);overflow:hidden;}
body.dark-mode .section-card{background:#1e1e1e;}
.section-card-header{padding:16px 24px;border-bottom:1.5px solid #f0f0f0;display:flex;align-items:center;gap:10px;}
body.dark-mode .section-card-header{border-color:#2a2a2a;}
.section-card-header h3{margin:0;font-size:1rem;font-weight:700;color:#1a1a1a;}
body.dark-mode .section-card-header h3{color:#f0f0f0;}
.section-card-header i{color:#28a745;}
table{width:100%;border-collapse:collapse;}
th{background:#f8f8f8;padding:11px 16px;font-size:0.8rem;font-weight:700;color:#555;text-align:left;text-transform:uppercase;letter-spacing:0.4px;}
body.dark-mode th{background:#252525;color:#aaa;}
td{padding:12px 16px;font-size:0.88rem;color:#333;border-bottom:1px solid #f5f5f5;}
body.dark-mode td{color:#ccc;border-color:#2a2a2a;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fafffe;}
body.dark-mode tr:hover td{background:#232323;}
.status-badge{padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:700;text-transform:uppercase;}
.status-badge.pending {background:#fff3cd;color:#856404;}
.status-badge.completed{background:#d4edda;color:#155724;}
.status-badge.cancelled{background:#f8d7da;color:#721c24;}
body.dark-mode .status-badge.pending {background:#3a2e00;color:#f0c040;}
body.dark-mode .status-badge.completed{background:#1a3327;color:#6fcf97;}
body.dark-mode .status-badge.cancelled{background:#3b1f1f;color:#f5a5a5;}
/* Mode toggle */
.mode-toggle-container{position:fixed;bottom:20px;right:24px;z-index:999;}
#mode-toggle{font-size:18px;width:42px;height:42px;border-radius:50%;border:2px solid #28a745;background:#fff;color:#1a1a1a;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background 0.3s,color 0.3s,border-color 0.3s;box-shadow:0 2px 8px rgba(0,0,0,0.2);}
#mode-toggle:hover{background: #28a745;color:#fff;}
body.dark-mode #mode-toggle{background:#1a1a1a;color: #28a745;border-color: #28a745;}
body.dark-mode #mode-toggle:hover{background: #28a745;color:#1a1a1a;}
.main-footer{text-align:center;padding:12px 10px;font-size:13px;flex-shrink:0;transition:background 0.3s,color 0.3s;}
body.light-mode .main-footer{background:#f0f0f0;color:#555;}
body.dark-mode  .main-footer{background:#1a1a1a;color:#aaa;}
</style>
</head>
<body class="light-mode">
<script>(function(){if(localStorage.getItem('mode')==='dark'){document.body.classList.remove('light-mode');document.body.classList.add('dark-mode');}})();</script>
<?php include("../includes/header.php"); ?>
<div class="admin-wrap">
    <h2 class="page-title"><i class="fa-solid fa-gauge"></i> Admin Dashboard</h2>
    <p class="page-sub">Welcome back, <?= htmlspecialchars($_SESSION['user']['name']) ?>!</p>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card" style="animation-delay:0s">
            <div class="stat-icon green"><i class="fa-solid fa-users"></i></div>
            <div class="stat-info"><h3><?= $totalUsers ?></h3><p>Total Users</p></div>
        </div>
        <div class="stat-card" style="animation-delay:0.07s">
            <div class="stat-icon blue"><i class="fa-solid fa-box"></i></div>
            <div class="stat-info"><h3><?= $totalProducts ?></h3><p>Products</p></div>
        </div>
        <div class="stat-card" style="animation-delay:0.14s">
            <div class="stat-icon orange"><i class="fa-solid fa-bag-shopping"></i></div>
            <div class="stat-info"><h3><?= $totalOrders ?></h3><p>Total Orders</p></div>
        </div>
        <div class="stat-card" style="animation-delay:0.21s">
            <div class="stat-icon purple"><i class="fa-solid fa-coins"></i></div>
            <div class="stat-info"><h3>LKR <?= number_format($totalRevenue,0) ?></h3><p>Revenue</p></div>
        </div>
    </div>

    <!-- Quick links -->
    <div class="quick-links">
        <a href="add_product.php" class="quick-card"><i class="fa-solid fa-plus"></i><span>Add Product</span></a>
        <a href="users.php"       class="quick-card"><i class="fa-solid fa-users"></i><span>Manage Users</span></a>
        <a href="orders.php"      class="quick-card"><i class="fa-solid fa-receipt"></i><span>View Orders</span></a>
        <a href="../auth/logout.php" class="quick-card"><i class="fa-solid fa-right-from-bracket" style="color:#e74c3c;"></i><span style="color:#e74c3c;">Logout</span></a>
    </div>

    <!-- Recent Orders -->
    <div class="section-card">
        <div class="section-card-header">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <h3>Recent Orders</h3>
        </div>
        <table>
            <thead><tr><th>#</th><th>Customer</th><th>Amount</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php if($recentOrders && $recentOrders->num_rows > 0): ?>
                <?php while($o = $recentOrders->fetch_assoc()): ?>
                <tr>
                    <td><?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($o['uname']) ?></td>
                    <td>LKR <?= number_format($o['total_amount'],0) ?></td>
                    <td><?= htmlspecialchars($o['payment_method']) ?></td>
                    <td><span class="status-badge <?= strtolower($o['status']) ?>"><?= $o['status'] ?></span></td>
                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;color:#aaa;padding:30px;">No orders yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mode-toggle-container"><button id="mode-toggle" title="Toggle"><i class="fa-solid fa-moon"></i></button></div>
<?php include("../includes/footer.php"); ?>
<script>
(function(){var b=document.getElementById('mode-toggle'),i=b.querySelector('i');
if(document.body.classList.contains('dark-mode')){i.classList.remove('fa-moon');i.classList.add('fa-sun');}
b.addEventListener('click',function(){var d=document.body.classList.contains('dark-mode');
if(d){document.body.classList.remove('dark-mode');document.body.classList.add('light-mode');i.classList.remove('fa-sun');i.classList.add('fa-moon');localStorage.setItem('mode','light');}
else{document.body.classList.remove('light-mode');document.body.classList.add('dark-mode');i.classList.remove('fa-moon');i.classList.add('fa-sun');localStorage.setItem('mode','dark');}});})();
</script>
</body>
</html>