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
</head>
<body class="light-mode">

<?php include("includes/header.php"); ?>

<div class="hero">
    <h1>Welcome to Gym & Fitness Store</h1>
    <p>Your one-stop shop for Supplements, Equipment & Accessories</p>
    <a href="auth/register.php">Get Started</a>
</div>

<?php include("includes/footer.php"); ?>

<script>
// Menu toggle
const toggle = document.querySelector('.menu-toggle');
const nav = document.querySelector('header nav ul');
toggle.addEventListener('click',()=>{ nav.classList.toggle('active'); });

// Light/Dark Mode
const body = document.body;
const modeToggleFooter = document.getElementById('mode-toggle-footer');
if(localStorage.getItem('mode') === 'dark'){ body.classList.remove('light-mode'); body.classList.add('dark-mode'); }
modeToggleFooter.addEventListener('click', ()=>{
    if(body.classList.contains('light-mode')){
        body.classList.remove('light-mode'); body.classList.add('dark-mode'); localStorage.setItem('mode','dark');
    } else{
        body.classList.remove('dark-mode'); body.classList.add('light-mode'); localStorage.setItem('mode','light');
    }
});
</script>

</body>
</html>