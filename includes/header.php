<?php
if(session_status() == PHP_SESSION_NONE){ session_start(); }

$currentPage = basename($_SERVER['PHP_SELF']);
$isLanding   = $currentPage === 'index.php';
$isRegister  = $currentPage === 'register.php';
$isLogin     = $currentPage === 'login.php';
$isHome      = $currentPage === 'home.php';
$userLogged  = isset($_SESSION['user']);

$scriptDir = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
$scriptDir = trim($scriptDir, '/');
$depth     = ($scriptDir === '') ? 0 : substr_count($scriptDir, '/') + 1;
$root      = str_repeat('../', $depth);

// Cart count from session
$cartCount = 0;
if(isset($_SESSION['cart'])) $cartCount = array_sum(array_column($_SESSION['cart'], 'qty'));
?>
<style>
.main-header {
    display:flex; align-items:center;
    padding:0 24px; height:60px;
    background:#1a1a1a;
    z-index:1000; box-sizing:border-box;
    width:100%; flex-shrink:0;
    transition:background 0.3s,box-shadow 0.3s;
}
.main-header .logo {
    color:#fff; font-size:1.2rem; font-weight:bold;
    white-space:nowrap; flex-shrink:0;
    transition:color 0.3s; text-decoration:none;
}
body.light-mode .main-header { background:#ffffff !important; box-shadow:0 2px 12px rgba(0,0,0,0.10); }
body.light-mode .main-header .logo { color:#1a1a1a !important; }
body.light-mode .menu-toggle { color:#1a1a1a !important; }
body.dark-mode  .main-header { background:#1a1a1a !important; box-shadow:0 2px 10px rgba(0,0,0,0.4); }
body.dark-mode  .main-header .logo { color:#fff !important; }
body.dark-mode  .menu-toggle { color:#fff !important; }

.main-header nav { margin-left:auto; padding-right:16px; }
.main-header nav ul { list-style:none; margin:0; padding:0; display:flex; align-items:center; gap:8px; }

.btn-header {
    display:inline-block; padding:7px 16px;
    border-radius:6px; text-decoration:none;
    font-weight:bold; font-size:0.9rem;
    color:#fff !important; background:#28a745;
    transition:background 0.3s;
}
.btn-header:hover { background:#218838; }

/* Cart icon button */
.btn-cart-header {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 14px; border-radius:6px;
    text-decoration:none; font-weight:bold; font-size:0.9rem;
    color:#fff !important; background:#28a745;
    transition:background 0.3s; position:relative;
}
.btn-cart-header:hover { background:#218838; }
.cart-badge {
    background:#fff; color:#28a745;
    font-size:0.7rem; font-weight:800;
    border-radius:50%; width:18px; height:18px;
    display:inline-flex; align-items:center; justify-content:center;
    line-height:1; margin-left:2px;
}
body.dark-mode .cart-badge { background:#f0c040; color:#1a1a1a; }

.menu-toggle {
    display:none; font-size:24px; cursor:pointer;
    color:#fff; margin-left:auto;
    padding-right:8px; user-select:none;
}

@media(max-width:768px){
    .menu-toggle { display:block; }
    .main-header nav {
        display:none; position:absolute; top:60px;
        left:0; right:0; padding-right:0; z-index:999;
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
                <li><a href="auth/login.php"    class="btn-header">Login</a></li>
                <li><a href="auth/register.php" class="btn-header">Register</a></li>
            <?php else: ?>
                <!-- Hide Home button when already on home page -->
                <?php if(!$isHome): ?>
                    <li><a href="<?= $root ?>index.php" class="btn-header">Home</a></li>
                <?php endif; ?>
                <?php if($userLogged): ?>
                    <!-- Cart icon — only for logged-in users on non-landing pages -->
                    <li>
                        <a href="<?= $root ?>/gym-store/user/cart.php" class="btn-cart-header" title="Cart">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <?php if($cartCount > 0): ?>
                                <span class="cart-badge"><?= $cartCount ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="<?= $root ?>/gym-store/auth/logout.php" class="btn-header">Logout</a></li>
                <?php else: ?>
                    <?php if(!$isLogin): ?>
                        <li><a href="<?= $root ?>/gym-store/auth/login.php" class="btn-header">Login</a></li>
                    <?php endif; ?>
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