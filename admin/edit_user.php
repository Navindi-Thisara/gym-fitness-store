<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){ header("Location: ../auth/login.php"); exit; }
include("../config/db.php");
$id = intval($_GET['id'] ?? 0);
if(!$id){ header("Location: users.php"); exit; }
$user = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
if(!$user){ header("Location: users.php"); exit; }

$error = $success = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $role  = trim($_POST['role']  ?? 'user');

    if(empty($name) || empty($email)){
        $error = "Name and email are required.";
    } elseif(!preg_match('/^[a-zA-Z\s]+$/', $name)){
        $error = "Name can only contain letters and spaces.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Please enter a valid email address.";
    } else {
        $n=$conn->real_escape_string($name); $e=$conn->real_escape_string($email); $r=$conn->real_escape_string($role);
        // Check duplicate email
        $chk = $conn->query("SELECT id FROM users WHERE email='$e' AND id!=$id");
        if($chk->num_rows > 0){ $error = "This email is already in use by another user."; }
        else {
            $conn->query("UPDATE users SET name='$n',email='$e',role='$r' WHERE id=$id");
            // Update password if provided
            if(!empty($_POST['new_password'])){
                $pw = $_POST['new_password'];
                if(strlen($pw) < 8){ $error = "New password must be at least 8 characters."; }
                else { $h=$conn->real_escape_string(password_hash($pw,PASSWORD_DEFAULT)); $conn->query("UPDATE users SET password='$h' WHERE id=$id"); }
            }
            if(!$error){
                $success = "User updated successfully!";
                $user = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
            }
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit User | Admin</title>
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;}body{margin:0;min-height:100vh;display:flex;flex-direction:column;font-family:'Arial',sans-serif;transition:background 0.3s,color 0.3s;}body.light-mode{background:#f0f2f5;color:#222;}body.dark-mode{background:#121212;color:#eee;}
.admin-wrap{flex:1;max-width:560px;margin:0 auto;width:100%;padding:32px 24px 60px;}
.page-title{font-size:1.5rem;font-weight:700;margin:0 0 6px;color:#1a1a1a;display:flex;align-items:center;gap:10px;}body.dark-mode .page-title{color:#f0f0f0;}.page-title i{color:#28a745;}.page-sub{color:#888;font-size:0.88rem;margin:0 0 24px;}
.back-link{display:inline-flex;align-items:center;gap:6px;color:#28a745;text-decoration:none;font-size:0.88rem;font-weight:600;margin-bottom:20px;}.back-link:hover{text-decoration:underline;}
.form-card{background:#fff;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.08);padding:32px;}body.dark-mode .form-card{background:#1e1e1e;}
.input-group{position:relative;margin-bottom:16px;}.input-group label{display:block;font-size:0.8rem;font-weight:600;color:#555;margin-bottom:5px;}body.dark-mode .input-group label{color:#aaa;}
.input-group .field-icon{position:absolute;left:13px;top:37px;color:#aaa;font-size:0.85rem;pointer-events:none;}
.input-group input,.input-group select{width:100%;padding:11px 14px 11px 38px;border:1.5px solid #e0e0e0;border-radius:10px;font-size:0.92rem;background:#fafafa;color:#222;outline:none;transition:border-color 0.2s,box-shadow 0.2s;}
.input-group select{appearance:none;cursor:pointer;}.input-group input:focus,.input-group select:focus{border-color:#28a745;box-shadow:0 0 0 3px rgba(40,167,69,0.12);background:#fff;}
body.dark-mode .input-group input,body.dark-mode .input-group select{background:#2a2a2a;border-color:#3a3a3a;color:#eee;}body.dark-mode .input-group input:focus,body.dark-mode .input-group select:focus{border-color:#28a745;background:#2f2f2f;}
.section-divider{border:none;border-top:1.5px dashed #e0e0e0;margin:20px 0 16px;}body.dark-mode .section-divider{border-color:#3a3a3a;}
.optional-label{font-size:0.78rem;color:#aaa;margin-bottom:12px;}
.btn-submit{width:100%;padding:13px;background:#28a745;color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;transition:background 0.3s,transform 0.15s;box-shadow:0 4px 14px rgba(40,167,69,0.35);margin-top:6px;}
.btn-submit:hover{background:#218838;transform:translateY(-1px);}
.message{padding:11px 14px;border-radius:8px;margin-bottom:18px;font-size:0.9rem;display:flex;align-items:center;gap:8px;}
.message.error{background:#fdecea;color:#c0392b;border:1px solid #f5c6cb;}.message.success{background:#eafaf1;color:#1e7e34;border:1px solid #b2dfdb;}
body.dark-mode .message.error{background:#3b1f1f;border-color:#7b3535;}body.dark-mode .message.success{background:#1a3327;border-color:#2d6a4f;color:#6fcf97;}
.mode-toggle-container{position:fixed;bottom:20px;right:24px;z-index:999;}#mode-toggle{font-size:18px;width:42px;height:42px;border-radius:50%;border:2px solid #28a745;background:#fff;color:#1a1a1a;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background 0.3s,color 0.3s,border-color 0.3s;box-shadow:0 2px 8px rgba(0,0,0,0.2);}#mode-toggle:hover{background:#28a745;color:#fff;}body.dark-mode #mode-toggle{background:#1a1a1a;color:#f0c040;border-color:#f0c040;}body.dark-mode #mode-toggle:hover{background:#f0c040;color:#1a1a1a;}
.main-footer{text-align:center;padding:12px 10px;font-size:13px;flex-shrink:0;transition:background 0.3s,color 0.3s;}body.light-mode .main-footer{background:#f0f0f0;color:#555;}body.dark-mode .main-footer{background:#1a1a1a;color:#aaa;}
</style></head><body class="light-mode">
<script>(function(){if(localStorage.getItem('mode')==='dark'){document.body.classList.remove('light-mode');document.body.classList.add('dark-mode');}})();</script>
<?php include("../includes/header.php"); ?>
<div class="admin-wrap">
    <a href="users.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Users</a>
    <h2 class="page-title"><i class="fa-solid fa-user-pen"></i> Edit User</h2>
    <p class="page-sub">Update user information and role</p>
    <div class="form-card">
        <?php if($error): ?><div class="message error"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="message success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div><?php endif; ?>
        <form method="POST" autocomplete="off">
            <div class="input-group"><label>Full Name *</label><i class="fa-solid fa-user field-icon"></i><input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required></div>
            <div class="input-group"><label>Email Address *</label><i class="fa-solid fa-envelope field-icon"></i><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></div>
            <div class="input-group"><label>Role *</label><i class="fa-solid fa-shield field-icon"></i>
                <select name="role">
                    <option value="user"  <?= $user['role']==='user' ?'selected':'' ?>>User</option>
                    <option value="admin" <?= $user['role']==='admin'?'selected':'' ?>>Admin</option>
                </select>
            </div>
            <hr class="section-divider">
            <p class="optional-label"><i class="fa-solid fa-lock" style="color:#28a745;margin-right:4px;"></i> Leave blank to keep existing password</p>
            <div class="input-group"><label>New Password</label><i class="fa-solid fa-key field-icon"></i><input type="password" name="new_password" placeholder="Min 8 characters"></div>
            <button type="submit" class="btn-submit"><i class="fa-solid fa-floppy-disk"></i> &nbsp;Save Changes</button>
        </form>
    </div>
</div>
<div class="mode-toggle-container"><button id="mode-toggle"><i class="fa-solid fa-moon"></i></button></div>
<?php include("../includes/footer.php"); ?>
<script>(function(){var b=document.getElementById('mode-toggle'),i=b.querySelector('i');if(document.body.classList.contains('dark-mode')){i.classList.remove('fa-moon');i.classList.add('fa-sun');}b.addEventListener('click',function(){var d=document.body.classList.contains('dark-mode');if(d){document.body.classList.remove('dark-mode');document.body.classList.add('light-mode');i.classList.remove('fa-sun');i.classList.add('fa-moon');localStorage.setItem('mode','light');}else{document.body.classList.remove('light-mode');document.body.classList.add('dark-mode');i.classList.remove('fa-moon');i.classList.add('fa-sun');localStorage.setItem('mode','dark');}});})();</script>
</body></html>