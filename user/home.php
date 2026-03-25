<?php
session_start();
if(!isset($_SESSION['user'])){ header("Location: ../auth/login.php"); exit; }
include("../config/db.php");

// Fetch products
$products = $conn->query("SELECT * FROM products");

// Contact form handling
$contactSuccess = $contactError = '';
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_submit'])){
    $c_name = trim($_POST['c_name']);
    $c_email = trim($_POST['c_email']);
    $c_message = trim($_POST['c_message']);
    
    if(!preg_match("/^[a-zA-Z ]+$/",$c_name)) $contactError = "Name can only contain letters and spaces!";
    elseif(!filter_var($c_email,FILTER_VALIDATE_EMAIL)) $contactError = "Invalid email!";
    elseif(strlen($c_message) < 5) $contactError = "Message is too short!";
    else $contactSuccess = "Thank you! Your message has been sent.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home | Gym Store</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="light-mode">

<?php include("../includes/header.php"); ?>

<!-- Products -->
<div class="home-container">
<?php while($row = $products->fetch_assoc()): ?>
    <div class="product-card">
        <img src="../assets/images/<?= $row['image'] ?>" alt="<?= $row['name'] ?>">
        <h3><?= $row['name'] ?></h3>
        <p>$<?= $row['price'] ?></p>
        <button>Add to Cart</button>
    </div>
<?php endwhile; ?>
</div>

<!-- Contact Form -->
<div class="contact-container">
    <h2>Contact Us</h2>
    <?php if($contactError) echo "<div class='message error'>$contactError</div>"; ?>
    <?php if($contactSuccess) echo "<div class='message success'>$contactSuccess</div>"; ?>
    <form method="POST" class="contact-form">
        <input type="text" name="c_name" placeholder="Your Name" required>
        <input type="email" name="c_email" placeholder="Your Email" required>
        <textarea name="c_message" placeholder="Your Message" required></textarea>
        <button type="submit" name="contact_submit">Send Message</button>
    </form>
</div>

<?php include("../includes/footer.php"); ?>

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