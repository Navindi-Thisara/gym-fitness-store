<?php
include("../config/db.php");
$success = $error = '';
if($_SERVER["REQUEST_METHOD"]=="POST"){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if(!preg_match("/^[a-zA-Z ]+$/",$name)) $error="Name can only contain letters and spaces!";
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)) $error="Invalid email format!";
    elseif(!preg_match("/^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W]).{8,}$/",$password)) $error="Password must be at least 8 characters including uppercase, number & special character!";
    else{
        $check = $conn->query("SELECT * FROM users WHERE email='$email'");
        if($check->num_rows>0) $error="Email already registered!";
        else{
            $hashed = password_hash($password,PASSWORD_DEFAULT);
            $conn->query("INSERT INTO users (name,email,password,role) VALUES ('$name','$email','$hashed','user')");
            $success="Registration successful! You can now <a href='login.php'>login</a>.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Gym Store</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="light-mode">

<?php include("../includes/header.php"); ?>

<div class="register-container">
    <h2>Create Account</h2>
    <?php if($error) echo "<div class='message error'>$error</div>"; ?>
    <?php if($success) echo "<div class='message success'>$success</div>"; ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" value="<?=isset($name)?htmlspecialchars($name):''?>" required>
        <input type="email" name="email" placeholder="Email Address" value="<?=isset($email)?htmlspecialchars($email):''?>" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>
    <p style="text-align:center;margin-top:10px;">Already have an account? <a href="login.php">Login here</a></p>
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