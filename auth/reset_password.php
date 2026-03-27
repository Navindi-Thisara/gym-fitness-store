<?php
date_default_timezone_set('Asia/Colombo');
session_start();
include("../config/db.php");

$error = $success = "";
$token = trim($_GET['token'] ?? '');
$validToken = false;
$tokenEmail = "";

if($token){
    $t = $conn->real_escape_string($token);

    $res = $conn->query("SELECT * FROM password_resets WHERE token='$t'");

    if($res && $res->num_rows === 1){
        $row = $res->fetch_assoc();

        if($row['expires_at'] && strtotime($row['expires_at']) > time()){
            $validToken = true;
            $tokenEmail = $row['email'];
        } else {
            $error = "This reset link has expired.";
        }
    } else {
        $error = "Invalid reset link.";
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])){
    $newPass  = $_POST['new_password']     ?? '';
    $confPass = $_POST['confirm_password'] ?? '';
    $t        = $conn->real_escape_string($_POST['token'] ?? '');
    $email    = $_POST['email'] ?? '';

    if(empty($newPass) || empty($confPass)){
        $error = "Please fill in both password fields.";
    } elseif(strlen($newPass) < 8 || !preg_match("/[A-Z]/", $newPass) || !preg_match("/[a-z]/", $newPass) || !preg_match("/[0-9]/", $newPass) || !preg_match("/[\W]/", $newPass)){
        $error = "Password must be 8+ characters with uppercase, lowercase, number &amp; special character.";
    } elseif($newPass !== $confPass){
        $error = "Passwords do not match.";
    } else {
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        $ce   = $conn->real_escape_string($email);
        $conn->query("UPDATE users SET password='$hash' WHERE email='$ce'");
        $conn->query("DELETE FROM password_resets WHERE email='$ce'");
        $success = "Your password has been reset successfully!";
        $validToken = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password | Gym Store</title>
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;}
html{height:100%;}
body{margin:0;min-height:100vh;display:flex;flex-direction:column;font-family:'Arial',sans-serif;transition:background 0.3s,color 0.3s;}
body.light-mode{background:#f0f2f5;color:#222;}
body.dark-mode {background:#121212;color:#eee;}
.page-body{flex:1;position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 16px 30px;}
.mode-toggle-container{position:absolute;bottom:16px;right:24px;z-index:10;}
#mode-toggle{font-size:18px;width:42px;height:42px;border-radius:50%;border:2px solid #28a745;background:#fff;color:#1a1a1a;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background 0.3s,color 0.3s,border-color 0.3s;box-shadow:0 2px 8px rgba(0,0,0,0.2);}
#mode-toggle:hover{background: #28a745;color:#fff;}
body.dark-mode #mode-toggle{background:#1a1a1a;color: #28a745;border-color: #28a745;}
body.dark-mode #mode-toggle:hover{background: #28a745;color:#1a1a1a;}
.register-card{background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);padding:40px 40px 36px;width:100%;max-width:440px;animation:slideUp 0.5s ease both;}
body.dark-mode .register-card{background:#1e1e1e;box-shadow:0 8px 32px rgba(0,0,0,0.5);}
@keyframes slideUp{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}
.card-icon{text-align:center;margin-bottom:6px;}
.card-icon i{font-size:2.4rem;color:#28a745;}
.register-card h2{text-align:center;margin:0 0 6px;font-size:1.6rem;font-weight:700;color:#1a1a1a;}
body.dark-mode .register-card h2{color:#f0f0f0;}
.subtitle{text-align:center;font-size:0.88rem;color:#888;margin-bottom:26px;}
.input-group{position:relative;margin-bottom:18px;}
.input-group .field-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#aaa;font-size:0.95rem;pointer-events:none;}
.input-group input{width:100%;padding:13px 44px 13px 40px;border:1.5px solid #ddd;border-radius:10px;font-size:0.95rem;background:#fafafa;color:#222;outline:none;transition:border-color 0.2s,box-shadow 0.2s,background 0.2s;}
.input-group input:focus{border-color:#28a745;box-shadow:0 0 0 3px rgba(40,167,69,0.12);background:#fff;}
body.dark-mode .input-group input{background:#2a2a2a;border-color:#444;color:#eee;}
body.dark-mode .input-group input:focus{border-color:#28a745;background:#2f2f2f;}
.pwd-toggle{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#aaa;cursor:pointer;font-size:0.95rem;transition:color 0.2s;}
.pwd-toggle:hover{color:#28a745;}
/* Strength */
.strength-bar{display:flex;gap:4px;margin-top:6px;}
.strength-bar span{flex:1;height:4px;border-radius:4px;background:#e0e0e0;transition:background 0.3s;}
.strength-label{font-size:0.75rem;color:#999;margin-top:3px;min-height:16px;}
.btn-register{width:100%;padding:13px;background:#28a745;color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;transition:background 0.3s,transform 0.15s,box-shadow 0.3s;box-shadow:0 4px 14px rgba(40,167,69,0.35);margin-top:6px;letter-spacing:0.4px;}
.btn-register:hover{background:#218838;transform:translateY(-1px);}
.btn-register:active{transform:translateY(0);}
.message{padding:11px 14px;border-radius:8px;margin-bottom:18px;font-size:0.9rem;display:flex;align-items:center;gap:8px;}
.message.error  {background:#fdecea;color:#c0392b;border:1px solid #f5c6cb;}
.message.success{background:#eafaf1;color:#1e7e34;border:1px solid #b2dfdb;}
body.dark-mode .message.error  {background:#3b1f1f;border-color:#7b3535;}
body.dark-mode .message.success{background:#1a3327;border-color:#2d6a4f;color:#6fcf97;}
.login-link{text-align:center;margin-top:20px;font-size:0.88rem;color:#888;}
.login-link a{color:#28a745;font-weight:bold;text-decoration:none;}
.login-link a:hover{text-decoration:underline;}
.main-footer{text-align:center;padding:10px 5px;font-size:13px;flex-shrink:0;transition:background 0.3s,color 0.3s;}
body.light-mode .main-footer{background:#f0f0f0;color:#333;}
body.dark-mode  .main-footer{background:#1a1a1a;color:#fff;}
</style>
</head>
<body class="light-mode">
<script>
(function(){
    if(localStorage.getItem('mode')==='dark'){
        document.body.classList.remove('light-mode');
        document.body.classList.add('dark-mode');
    }
})();
</script>
<?php include("../includes/header.php"); ?>
<div class="page-body">
    <div class="register-card">
        <div class="card-icon"><i class="fa-solid fa-key"></i></div>
        <h2>Reset Password</h2>
        <p class="subtitle">Enter your new password below</p>

        <?php if($error): ?>
            <div class="message error"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="message success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
        <?php endif; ?>

        <?php if($validToken): ?>
        <form method="POST" autocomplete="off">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($tokenEmail) ?>">

            <div class="input-group">
                <i class="fa-solid fa-lock field-icon"></i>
                <input type="password" name="new_password" id="newPass" placeholder="New Password" required>
                <span class="pwd-toggle" onclick="togglePwd('newPass','eye1')">
                    <i class="fa-solid fa-eye" id="eye1"></i>
                </span>
            </div>
            <div class="strength-bar">
                <span id="s1"></span><span id="s2"></span>
                <span id="s3"></span><span id="s4"></span>
            </div>
            <div class="strength-label" id="strengthLabel"></div>

            <div class="input-group" style="margin-top:10px;">
                <i class="fa-solid fa-lock field-icon"></i>
                <input type="password" name="confirm_password" id="confPass" placeholder="Confirm New Password" required>
                <span class="pwd-toggle" onclick="togglePwd('confPass','eye2')">
                    <i class="fa-solid fa-eye" id="eye2"></i>
                </span>
            </div>

            <button type="submit" name="reset_password" class="btn-register">
                <i class="fa-solid fa-check"></i> &nbsp;Reset Password
            </button>
        </form>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="login-link"><a href="login.php"><i class="fa-solid fa-arrow-left" style="font-size:0.8rem;"></i> Back to Login</a></div>
        <?php elseif(!$validToken && !$success): ?>
            <div class="login-link"><a href="forgot_password.php">Request a new reset link</a></div>
        <?php endif; ?>
    </div>

    <div class="mode-toggle-container">
        <button id="mode-toggle" title="Toggle Light/Dark Mode"><i class="fa-solid fa-moon"></i></button>
    </div>
</div>
<?php include("../includes/footer.php"); ?>
<script>
function togglePwd(id, eyeId){
    var inp=document.getElementById(id), icon=document.getElementById(eyeId);
    if(inp.type==='password'){ inp.type='text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
    else { inp.type='password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
}
var pwdInput=document.getElementById('newPass');
var bars=[document.getElementById('s1'),document.getElementById('s2'),document.getElementById('s3'),document.getElementById('s4')];
var label=document.getElementById('strengthLabel');
var colors=['#e74c3c','#e67e22','#f1c40f','#28a745'];
var labels=['Weak','Fair','Good','Strong'];
if(pwdInput){ pwdInput.addEventListener('input',function(){
    var v=pwdInput.value,score=0;
    if(v.length>=8)score++; if(/[A-Z]/.test(v)&&/[a-z]/.test(v))score++; if(/[0-9]/.test(v))score++; if(/[\W_]/.test(v))score++;
    bars.forEach(function(b,i){b.style.background=i<score?colors[score-1]:'#e0e0e0';});
    label.textContent=v.length?(labels[score-1]||''):''; label.style.color=score>0?colors[score-1]:'#999';
});}
(function(){
    var modeBtn=document.getElementById('mode-toggle'),icon=modeBtn.querySelector('i');
    if(document.body.classList.contains('dark-mode')){icon.classList.remove('fa-moon');icon.classList.add('fa-sun');}
    modeBtn.addEventListener('click',function(){
        var isDark=document.body.classList.contains('dark-mode');
        if(isDark){document.body.classList.remove('dark-mode');document.body.classList.add('light-mode');icon.classList.remove('fa-sun');icon.classList.add('fa-moon');localStorage.setItem('mode','light');}
        else{document.body.classList.remove('light-mode');document.body.classList.add('dark-mode');icon.classList.remove('fa-moon');icon.classList.add('fa-sun');localStorage.setItem('mode','dark');}
    });
})();
</script>
</body>
</html>