<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){ header("Location: ../auth/login.php"); exit; }
include("../config/db.php");
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users | Admin</title>
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;}body{margin:0;min-height:100vh;display:flex;flex-direction:column;font-family:'Arial',sans-serif;transition:background 0.3s,color 0.3s;}body.light-mode{background:#f0f2f5;color:#222;}body.dark-mode{background:#121212;color:#eee;}
.page-outer{flex:1;position:relative;display:flex;flex-direction:column;}
.admin-wrap{flex:1;max-width:1100px;margin:0 auto;width:100%;padding:32px 24px 60px;}
.page-title{font-size:1.5rem;font-weight:700;margin:0 0 6px;color:#1a1a1a;display:flex;align-items:center;gap:10px;}body.dark-mode .page-title{color:#f0f0f0;}.page-title i{color:#28a745;}.page-sub{color:#888;font-size:0.88rem;margin:0 0 24px;}
.back-link{display:inline-flex;align-items:center;gap:6px;color:#28a745;text-decoration:none;font-size:0.88rem;font-weight:600;margin-bottom:20px;}.back-link:hover{text-decoration:underline;}
.section-card{background:#fff;border-radius:14px;box-shadow:0 4px 18px rgba(0,0,0,0.07);overflow:hidden;}body.dark-mode .section-card{background:#1e1e1e;}
.section-card-header{padding:16px 24px;border-bottom:1.5px solid #f0f0f0;display:flex;align-items:center;gap:10px;}body.dark-mode .section-card-header{border-color:#2a2a2a;}
.section-card-header h3{margin:0;font-size:1rem;font-weight:700;color:#1a1a1a;}body.dark-mode .section-card-header h3{color:#f0f0f0;}.section-card-header i{color:#28a745;}
table{width:100%;border-collapse:collapse;}th{background:#f8f8f8;padding:11px 16px;font-size:0.8rem;font-weight:700;color:#555;text-align:left;text-transform:uppercase;letter-spacing:0.4px;}body.dark-mode th{background:#252525;color:#aaa;}
td{padding:12px 16px;font-size:0.88rem;color:#333;border-bottom:1px solid #f5f5f5;}body.dark-mode td{color:#ccc;border-color:#2a2a2a;}tr:last-child td{border-bottom:none;}tr:hover td{background:#fafffe;}body.dark-mode tr:hover td{background:#232323;}
.role-badge{padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:700;text-transform:uppercase;}
.role-badge.admin{background:#fdecea;color:#c0392b;}.role-badge.user{background:#eafaf1;color:#1e7e34;}
body.dark-mode .role-badge.admin{background:#3b1f1f;color:#f5a5a5;}body.dark-mode .role-badge.user{background:#1a3327;color:#6fcf97;}
.action-btns{display:flex;gap:8px;}
.btn-edit{padding:6px 14px;background:#3498db;color:#fff;border-radius:7px;text-decoration:none;font-size:0.8rem;font-weight:600;transition:background 0.2s;}
.btn-edit:hover{background:#2980b9;}
.btn-delete{padding:6px 14px;background:#e74c3c;color:#fff;border-radius:7px;text-decoration:none;font-size:0.8rem;font-weight:600;transition:background 0.2s;}
.btn-delete:hover{background:#c0392b;}
.mode-toggle-container{position:fixed;bottom:80px;right:24px;z-index:999;}#mode-toggle{font-size:18px;width:42px;height:42px;border-radius:50%;border:2px solid #28a745;background:#fff;color:#1a1a1a;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background 0.3s,color 0.3s,border-color 0.3s;box-shadow:0 2px 8px rgba(0,0,0,0.2);}#mode-toggle:hover{background:#28a745;color:#fff;}body.dark-mode #mode-toggle{background:#1a1a1a;color:#28a745;border-color:#28a745;}body.dark-mode #mode-toggle:hover{background:#28a745;color:#1a1a1a;}
.main-footer{text-align:center;padding:12px 10px;font-size:13px;flex-shrink:0;transition:background 0.3s,color 0.3s;}body.light-mode .main-footer{background:#f0f0f0;color:#555;}body.dark-mode .main-footer{background:#1a1a1a;color:#aaa;}
</style></head><body class="light-mode">
<script>(function(){if(localStorage.getItem('mode')==='dark'){document.body.classList.remove('light-mode');document.body.classList.add('dark-mode');}})();</script>
<?php include("../includes/header.php"); ?>
<div class="page-outer">
<div class="admin-wrap">
    <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h2 class="page-title"><i class="fa-solid fa-users"></i> Manage Users</h2>
    <p class="page-sub">View, edit or delete registered users</p>
    <div class="section-card">
        <div class="section-card-header"><i class="fa-solid fa-users"></i><h3>All Users (<?= $users->num_rows ?>)</h3></div>
        <table>
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
            <tbody>
            <?php while($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="role-badge <?= $u['role'] ?>"><?= $u['role'] ?></span></td>
                <td><div class="action-btns">
                    <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn-edit"><i class="fa-solid fa-pen"></i> Edit</a>
                    <a href="delete_user.php?id=<?= $u['id'] ?>" class="btn-delete" onclick="return confirm('Delete this user?')"><i class="fa-solid fa-trash"></i> Delete</a>
                </div></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mode-toggle-container"><button id="mode-toggle"><i class="fa-solid fa-moon"></i></button></div>
</div><!-- end page-outer -->
<?php include("../includes/footer.php"); ?>
<script>(function(){var b=document.getElementById('mode-toggle'),i=b.querySelector('i');if(document.body.classList.contains('dark-mode')){i.classList.remove('fa-moon');i.classList.add('fa-sun');}b.addEventListener('click',function(){var d=document.body.classList.contains('dark-mode');if(d){document.body.classList.remove('dark-mode');document.body.classList.add('light-mode');i.classList.remove('fa-sun');i.classList.add('fa-moon');localStorage.setItem('mode','light');}else{document.body.classList.remove('light-mode');document.body.classList.add('dark-mode');i.classList.remove('fa-moon');i.classList.add('fa-sun');localStorage.setItem('mode','dark');}});})();</script>
</body></html>