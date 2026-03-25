<?php
if(session_status() == PHP_SESSION_NONE){ session_start(); }

$currentPage = basename($_SERVER['PHP_SELF']);
$isLanding   = $currentPage === 'index.php';
$isRegister  = $currentPage === 'register.php';
$isLogin     = $currentPage === 'login.php';
$userLogged  = isset($_SESSION['user']);

// Build correct relative path to root — works on Windows (XAMPP) and Linux
$scriptDir = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
$scriptDir = trim($scriptDir, '/');
$depth     = ($scriptDir === '') ? 0 : substr_count($scriptDir, '/') + 1;
$root      = str_repeat('../', $depth);
?>
<style>
/* ── Header base ── */
.main-header {
    display:flex; align-items:center;
    padding:0 24px; height:60px;
    background:#1a1a1a;
    z-index:1000; box-sizing:border-box;
    width:100%; flex-shrink:0;
    transition: background 0.3s, box-shadow 0.3s;
}
.main-header .logo {
    color:#fff; font-size:1.2rem; font-weight:bold;
    white-space:nowrap; flex-shrink:0;
    transition: color 0.3s;
    text-decoration: none;
}

/* ── LIGHT MODE — white header, dark logo ── */
body.light-mode .main-header {
    background:#ffffff !important;
    box-shadow:0 2px 12px rgba(0,0,0,0.10);
}
body.light-mode .main-header .logo { color:#1a1a1a !important; }
body.light-mode .menu-toggle { color:#1a1a1a !important; }

/* ── DARK MODE — dark header, white logo ── */
body.dark-mode .main-header { background:#1a1a1a !important; box-shadow:0 2px 10px rgba(0,0,0,0.4); }
body.dark-mode .main-header .logo { color:#fff !important; }
body.dark-mode .menu-toggle { color:#fff !important; }

/* ── Nav ── */
.main-header nav { margin-left:auto; padding-right:40px; }
.main-header nav ul { list-style:none; margin:0; padding:0; display:flex; align-items:center; gap:10px; }

/* ── Nav buttons ── */
.btn-header {
    display:inline-block; padding:7px 16px;
    border-radius:6px; text-decoration:none;
    font-weight:bold; font-size:0.9rem;
    color:#fff !important; background:#28a745;
    transition:background 0.3s;
}
.btn-header:hover { background:#218838; }

/* ── Hamburger ── */
.menu-toggle {
    display:none; font-size:24px; cursor:pointer;
    color:#fff; margin-left:auto;
    padding-right:8px; user-select:none;
}

/* ── Responsive ── */
@media(max-width:768px){
    .menu-toggle { display:block; }
    .main-header nav {
        display:none; position:absolute; top:60px;
        left:0; right:0; padding-right:0;
        z-index:999;
    }
    body.light-mode .main-header nav { background:#ffffff; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
    body.dark-mode  .main-header nav { background:#1a1a1a; }
    .main-header nav.open { display:block; }
    .main-header nav ul { flex-direction:column; align-items:flex-end; padding:12px 24px; gap:8px; }
}
</style>

<header class="main-header">
    <div class="logo">Gym &amp; Fitness Store</div>
    <span class="menu-toggle" id="menuToggle">&#9776;</span>
    <nav id="mainNav">
        <ul>
            <?php if($isLanding): ?>
                <!-- Landing page: Login + Register -->
                <li><a href="auth/login.php"    class="btn-header">Login</a></li>
                <li><a href="auth/register.php" class="btn-header">Register</a></li>
            <?php else: ?>
                <!-- All other pages -->
                <li><a href="<?= $root ?>index.php" class="btn-header">Home</a></li>
                <?php if($userLogged): ?>
                    <li><a href="<?= $root ?>/gym-store/auth/logout.php" class="btn-header">Logout</a></li>
                <?php else: ?>
                    <!-- Hide Login button when already on login page -->
                    <?php if(!$isLogin): ?>
                        <li><a href="<?= $root ?>/gym-store/auth/login.php" class="btn-header">Login</a></li>
                    <?php endif; ?>
                    <!-- Hide Register button when already on register page -->
                    <?php if(!$isRegister): ?>
                        <li><a href="<?= $root ?>/gym-store/auth/register.php" class="btn-header">Register</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<script>
document.getElementById('menuToggle').addEventListener('click', function(){
    document.getElementById('mainNav').classList.toggle('open');
});
</script>