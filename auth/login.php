<?php
session_start();
include("../config/db.php");

$error = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Invalid email address!";
    } else {
        $result = $conn->query("SELECT * FROM users WHERE email='$email'");
        if($result->num_rows === 1){
            $user = $result->fetch_assoc();
            if(password_verify($password, $user['password'])){
                $_SESSION['user'] = $user;
                if($user['role'] === 'admin'){
                    header("Location: ../admin/dashboard.php"); exit;
                } else {
                    header("Location: ../user/home.php"); exit;
                }
            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "No account found with that email!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Gym Store</title>
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*, *::before, *::after { box-sizing:border-box; }
html { height:100%; }
body {
    margin:0; min-height:100vh;
    display:flex; flex-direction:column;
    font-family:'Arial',sans-serif;
    transition:background 0.3s,color 0.3s;
}
body.light-mode { background:#f0f2f5; color:#222; }
body.dark-mode  { background:#121212; color:#eee; }

/* ── Page body grows to push footer down ── */
.page-body {
    flex:1; position:relative;
    display:flex; flex-direction:column;
    align-items:center; justify-content:center;
    padding:40px 16px 30px;
}

/* ── Mode toggle — bottom-right, identical to index & register ── */
.mode-toggle-container {
    position:absolute; bottom:16px; right:24px; z-index:10;
}
#mode-toggle {
    font-size:18px; width:42px; height:42px;
    border-radius:50%; border:2px solid #28a745;
    background:#fff; color:#1a1a1a; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:background 0.3s,color 0.3s,border-color 0.3s;
    box-shadow:0 2px 8px rgba(0,0,0,0.2);
}
#mode-toggle:hover { background: #28a745; color: #fff; }
body.dark-mode #mode-toggle { background: #1a1a1a; color: #28a745; border-color: #28a745; }
body.dark-mode #mode-toggle:hover { background: #28a745; color: #1a1a1a; }

/* ── Login Card ── */
.register-card {
    background:#fff; border-radius:16px;
    box-shadow:0 8px 32px rgba(0,0,0,0.12);
    padding:40px 40px 36px; width:100%; max-width:440px;
    animation:slideUp 0.5s ease both;
}
body.dark-mode .register-card { background:#1e1e1e; box-shadow:0 8px 32px rgba(0,0,0,0.5); }

@keyframes slideUp {
    from { opacity:0; transform:translateY(30px); }
    to   { opacity:1; transform:translateY(0); }
}

.register-card .card-icon { text-align:center; margin-bottom:6px; }
.register-card .card-icon i { font-size:2.4rem; color:#28a745; }
.register-card h2 {
    text-align:center; margin:0 0 6px;
    font-size:1.6rem; font-weight:700; color:#1a1a1a;
}
body.dark-mode .register-card h2 { color:#f0f0f0; }
.register-card .subtitle { text-align:center; font-size:0.88rem; color:#888; margin-bottom:26px; }

/* ── Input groups ── */
.input-group { position:relative; margin-bottom:18px; }
.input-group .field-icon {
    position:absolute; left:14px; top:50%;
    transform:translateY(-50%); color:#aaa;
    font-size:0.95rem; pointer-events:none; transition:color 0.2s;
}
.input-group input {
    width:100%; padding:13px 14px 13px 40px;
    border:1.5px solid #ddd; border-radius:10px;
    font-size:0.95rem; background:#fafafa; color:#222;
    outline:none;
    transition:border-color 0.2s,box-shadow 0.2s,background 0.2s;
}
.input-group input:focus {
    border-color:#28a745;
    box-shadow:0 0 0 3px rgba(40,167,69,0.12);
    background:#fff;
}
body.dark-mode .input-group input { background:#2a2a2a; border-color:#444; color:#eee; }
body.dark-mode .input-group input:focus { border-color:#28a745; background:#2f2f2f; box-shadow:0 0 0 3px rgba(40,167,69,0.18); }

/* ── Eye toggle ── */
.pwd-toggle {
    position:absolute; right:14px; top:50%;
    transform:translateY(-50%); color:#aaa;
    cursor:pointer; font-size:0.95rem; transition:color 0.2s;
}
.pwd-toggle:hover { color:#28a745; }

/* ── Forgot password link ── */
.forgot-link {
    text-align:right; margin-top:-10px; margin-bottom:14px;
    font-size:0.82rem;
}
.forgot-link a { color:#28a745; text-decoration:none; }
.forgot-link a:hover { text-decoration:underline; }

/* ── Submit button ── */
.btn-register {
    width:100%; padding:13px; background:#28a745; color:#fff;
    border:none; border-radius:10px; font-size:1rem; font-weight:700;
    cursor:pointer; transition:background 0.3s,transform 0.15s,box-shadow 0.3s;
    box-shadow:0 4px 14px rgba(40,167,69,0.35); margin-top:6px;
    letter-spacing:0.4px;
}
.btn-register:hover { background:#218838; transform:translateY(-1px); box-shadow:0 6px 18px rgba(40,167,69,0.45); }
.btn-register:active { transform:translateY(0); }

/* ── Messages ── */
.message {
    padding:11px 14px; border-radius:8px; margin-bottom:18px;
    font-size:0.9rem; display:flex; align-items:center; gap:8px;
}
.message.error   { background:#fdecea; color:#c0392b; border:1px solid #f5c6cb; }
.message.success { background:#eafaf1; color:#1e7e34; border:1px solid #b2dfdb; }
body.dark-mode .message.error   { background:#3b1f1f; border-color:#7b3535; }
body.dark-mode .message.success { background:#1a3327; border-color:#2d6a4f; }

/* ── Register link ── */
.login-link { text-align:center; margin-top:20px; font-size:0.88rem; color:#888; }
.login-link a { color:#28a745; font-weight:bold; text-decoration:none; }
.login-link a:hover { text-decoration:underline; }

/* ── Footer ── */
.main-footer { text-align:center; padding:10px 5px; font-size:13px; flex-shrink:0; transition:background 0.3s,color 0.3s; }
body.light-mode .main-footer { background:#f0f0f0; color:#333; }
body.dark-mode  .main-footer { background:#1a1a1a; color:#fff; }
</style>
</head>
<body class="light-mode">

<script>
(function(){
    if(localStorage.getItem('mode') === 'dark'){
        document.body.classList.remove('light-mode');
        document.body.classList.add('dark-mode');
    }
})();
</script>

<?php include("../includes/header.php"); ?>

<div class="page-body">

    <!-- Login Card -->
    <div class="register-card">
        <div class="card-icon"><i class="fa-solid fa-dumbbell"></i></div>
        <h2>Welcome Back</h2>
        <p class="subtitle">Login to your Gym &amp; Fitness account</p>

        <?php if($error): ?>
            <div class="message error"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">

            <!-- Email -->
            <div class="input-group">
                <i class="fa-solid fa-envelope field-icon"></i>
                <input type="email" name="email" placeholder="Email Address"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <!-- Password -->
            <div class="input-group">
                <i class="fa-solid fa-lock field-icon"></i>
                <input type="password" name="password" id="passwordInput" placeholder="Password" required>
                <span class="pwd-toggle" onclick="togglePwd()">
                    <i class="fa-solid fa-eye" id="eyeIcon"></i>
                </span>
            </div>

            <!-- Forgot password -->
            <div class="forgot-link">
                <a href="#">Forgot password?</a>
            </div>

            <button type="submit" class="btn-register">
                <i class="fa-solid fa-right-to-bracket"></i> &nbsp;Login
            </button>
        </form>

        <div class="login-link">
            Don't have an account? <a href="/gym-store/auth/register.php">Register here</a>
        </div>
    </div>

    <!-- Mode toggle — bottom-right, same position as index & register -->
    <div class="mode-toggle-container">
        <button id="mode-toggle" title="Toggle Light/Dark Mode">
            <i class="fa-solid fa-moon"></i>
        </button>
    </div>

</div>

<?php include("../includes/footer.php"); ?>

<script>
/* ── Password visibility toggle ── */
function togglePwd(){
    var inp  = document.getElementById('passwordInput');
    var icon = document.getElementById('eyeIcon');
    if(inp.type === 'password'){
        inp.type = 'text';
        icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash');
    } else {
        inp.type = 'password';
        icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye');
    }
}

/* ── Light / Dark Mode ── */
(function(){
    var modeBtn = document.getElementById('mode-toggle');
    var icon    = modeBtn.querySelector('i');

    if(document.body.classList.contains('dark-mode')){
        icon.classList.remove('fa-moon'); icon.classList.add('fa-sun');
    }

    modeBtn.addEventListener('click', function(){
        var isDark = document.body.classList.contains('dark-mode');
        if(isDark){
            document.body.classList.remove('dark-mode');
            document.body.classList.add('light-mode');
            icon.classList.remove('fa-sun'); icon.classList.add('fa-moon');
            localStorage.setItem('mode','light');
        } else {
            document.body.classList.remove('light-mode');
            document.body.classList.add('dark-mode');
            icon.classList.remove('fa-moon'); icon.classList.add('fa-sun');
            localStorage.setItem('mode','dark');
        }
    });
})();
</script>

</body>
</html>