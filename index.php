<?php
session_start();
if(isset($_SESSION['user'])){
    if($_SESSION['user']['role']=='admin'){ header("Location: admin/dashboard.php"); exit; }
    else{ header("Location: user/home.php"); exit; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gym & Fitness Store</title>
<link rel="stylesheet" href="assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
/* Light / Dark mode body backgrounds */
body.light-mode { background: #f5f5f5; color: #111; }
body.dark-mode  { background: #121212; color: #eee; }

/* Reset & full-page lock */
*, *::before, *::after { box-sizing: border-box; }
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    overflow: hidden; /* no scrollbar at all */
    font-family: 'Arial', sans-serif;
}

/* Page wrapper fills exactly the viewport */
.page-wrapper {
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
}

/* Header inside wrapper */
.main-header {
    flex-shrink: 0;
}

/* ── Ken Burns animation keyframes ── */
@keyframes kenburns {
    0%   { transform: scale(1)    translateX(0)    translateY(0); }
    25%  { transform: scale(1.08) translateX(-1%)  translateY(-1%); }
    50%  { transform: scale(1.12) translateX(1%)   translateY(-2%); }
    75%  { transform: scale(1.08) translateX(-1%)  translateY(1%); }
    100% { transform: scale(1)    translateX(0)    translateY(0); }
}

/* Text fade-up on load */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Hero wrapper — clips the zooming bg */
.hero {
    flex: 1;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    color: #fff;
}

/* Animated background pseudo-element */
.hero::before {
    content: '';
    position: absolute;
    inset: -10%; /* slightly oversized so zoom never shows edges */
    background: url('assets/images/hero-bg.jpg') center/cover no-repeat;
    animation: kenburns 20s ease-in-out infinite;
    z-index: 0;
}

/* Dark gradient overlay for text readability */
.hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to bottom,
        rgba(0,0,0,0.55) 0%,
        rgba(0,0,0,0.35) 50%,
        rgba(0,0,0,0.65) 100%
    );
    z-index: 1;
}

/* Hero content sits above overlay */
.hero-content {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.hero-content h1 {
    font-size: 3rem;
    margin: 0 0 12px;
    letter-spacing: 1px;
    text-shadow: 0 2px 16px rgba(0,0,0,0.8), 0 1px 4px rgba(0,0,0,0.9);
    animation: fadeUp 0.9s ease both;
}

.hero-content p {
    font-size: 1.3rem;
    margin: 0 0 24px;
    text-shadow: 0 1px 10px rgba(0,0,0,0.9);
    animation: fadeUp 0.9s ease 0.2s both;
}

.hero-content a {
    padding: 13px 30px;
    background: #28a745;
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    font-size: 1rem;
    letter-spacing: 0.5px;
    transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
    box-shadow: 0 4px 15px rgba(40,167,69,0.5);
    animation: fadeUp 0.9s ease 0.4s both;
}
.hero-content a:hover {
    background: #218838;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40,167,69,0.7);
}

/* Dark/Light toggle — absolute, bottom-right of hero, above overlay */
.mode-toggle-container {
    position: absolute;
    bottom: 16px;
    right: 24px;
    z-index: 2;
}
#mode-toggle {
    font-size: 18px;
    background: #fff;
    border: 2px solid #28a745;
    border-radius: 50%;
    width: 42px;
    height: 42px;
    cursor: pointer;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s, color 0.3s, border-color 0.3s;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}
#mode-toggle:hover {
    background: #28a745;
    color: #fff;
    border-color: #28a745;
}

/* Dark mode — dark background with golden sun icon */
body.dark-mode #mode-toggle {
    background: #1a1a1a;
    color: #28a745;
    border-color: #28a745;
}
body.dark-mode #mode-toggle:hover {
    background: #28a745;
    color: #1a1a1a;
    border-color: #28a745;
}

/* Footer — compact, never pushes content */
.main-footer {
    background: #1a1a1a;
    color: #fff;
    text-align: center;
    padding: 8px 5px;
    font-size: 13px;
    flex-shrink: 0;
}

@media(max-width:768px){
    .hero h1 { font-size:2rem; }
    .hero p   { font-size:1rem; }
}
</style>
</head>
<body class="light-mode">

<div class="page-wrapper">

<?php include("includes/header.php"); ?>

    <!-- Hero Section -->
    <div class="hero">

        <!-- Animated text content -->
        <div class="hero-content">
            <h1>Welcome to Gym &amp; Fitness Store</h1>
            <p>Your one-stop shop for Supplements, Equipment &amp; Accessories</p>
            <a href="auth/register.php">Get Started</a>
        </div>

        <!-- Dark/Light Mode Toggle — bottom-right corner of hero -->
        <div class="mode-toggle-container">
            <button id="mode-toggle" title="Toggle Light/Dark Mode">
                <i class="fa-solid fa-moon"></i>
            </button>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>
</div>

<script>
// Light/Dark Mode
const modeBtn = document.getElementById('mode-toggle');
const icon    = modeBtn.querySelector('i');

if(localStorage.getItem('mode') === 'dark'){
    document.body.classList.replace('light-mode','dark-mode');
    icon.classList.replace('fa-moon','fa-sun');
}

modeBtn.addEventListener('click', () => {
    if(document.body.classList.contains('light-mode')){
        document.body.classList.replace('light-mode','dark-mode');
        icon.classList.replace('fa-moon','fa-sun');
        localStorage.setItem('mode','dark');
    } else {
        document.body.classList.replace('dark-mode','light-mode');
        icon.classList.replace('fa-sun','fa-moon');
        localStorage.setItem('mode','light');
    }
});
</script>

</body>
</html>