<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){ header("Location: ../auth/login.php"); exit; }
include("../config/db.php");

$id = intval($_GET['id'] ?? 0);
if(!$id){ header("Location: dashboard.php"); exit; }
$product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
if(!$product){ header("Location: dashboard.php"); exit; }

$error = $success = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $name     = trim($_POST['name'] ?? '');
    $price    = trim($_POST['price'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $badge    = trim($_POST['badge'] ?? '');
    $image    = $product['image'];

    if(empty($name) || empty($price) || empty($category)){
        $error = "Name, price and category are required.";
    } elseif(!is_numeric($price) || $price <= 0){
        $error = "Please enter a valid price.";
    } else {
        if(!empty($_FILES['image']['name'])){
            $allowed = ['jpg','jpeg','png','webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if(!in_array($ext,$allowed)){ $error="Only JPG, PNG or WEBP images allowed."; }
            elseif($_FILES['image']['size'] > 2*1024*1024){ $error="Image must be under 2MB."; }
            else { $image = uniqid('prod_').'.'.$ext; move_uploaded_file($_FILES['image']['tmp_name'],"../assets/images/".$image); }
        }
        if(!$error){
            $n=$conn->real_escape_string($name); $p=floatval($price);
            $c=$conn->real_escape_string($category); $b=$conn->real_escape_string($badge); $img=$conn->real_escape_string($image);
            $conn->query("UPDATE products SET name='$n',price=$p,category='$c',badge='$b',image='$img' WHERE id=$id");
            $success = "Product updated successfully!";
            $product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Product | Admin</title>
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;}body{margin:0;min-height:100vh;display:flex;flex-direction:column;font-family:'Arial',sans-serif;transition:background 0.3s,color 0.3s;}body.light-mode{background:#f0f2f5;color:#222;}body.dark-mode{background:#121212;color:#eee;}
.admin-wrap{flex:1;max-width:600px;margin:0 auto;width:100%;padding:32px 24px 60px;}
.page-title{font-size:1.5rem;font-weight:700;margin:0 0 6px;color:#1a1a1a;display:flex;align-items:center;gap:10px;}body.dark-mode .page-title{color:#f0f0f0;}.page-title i{color:#28a745;}.page-sub{color:#888;font-size:0.88rem;margin:0 0 24px;}
.form-card{background:#fff;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.08);padding:32px;}body.dark-mode .form-card{background:#1e1e1e;}
.input-group{position:relative;margin-bottom:16px;}.input-group label{display:block;font-size:0.8rem;font-weight:600;color:#555;margin-bottom:5px;}body.dark-mode .input-group label{color:#aaa;}
.input-group .field-icon{position:absolute;left:13px;top:37px;color:#aaa;font-size:0.85rem;pointer-events:none;}
.input-group input,.input-group select{width:100%;padding:11px 14px 11px 38px;border:1.5px solid #e0e0e0;border-radius:10px;font-size:0.92rem;background:#fafafa;color:#222;outline:none;transition:border-color 0.2s,box-shadow 0.2s;}
.input-group select{appearance:none;cursor:pointer;}.input-group input:focus,.input-group select:focus{border-color:#28a745;box-shadow:0 0 0 3px rgba(40,167,69,0.12);background:#fff;}
body.dark-mode .input-group input,body.dark-mode .input-group select{background:#2a2a2a;border-color:#3a3a3a;color:#eee;}body.dark-mode .input-group input:focus,body.dark-mode .input-group select:focus{border-color:#28a745;background:#2f2f2f;}
.input-group input[type="file"]{padding:8px 14px 8px 38px;}
.current-img{width:80px;height:80px;object-fit:cover;border-radius:10px;margin-bottom:8px;display:block;}
.btn-submit{width:100%;padding:13px;background:#28a745;color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;transition:background 0.3s,transform 0.15s;box-shadow:0 4px 14px rgba(40,167,69,0.35);margin-top:6px;}
.btn-submit:hover{background:#218838;transform:translateY(-1px);}
.back-link{display:inline-flex;align-items:center;gap:6px;color:#28a745;text-decoration:none;font-size:0.88rem;font-weight:600;margin-bottom:20px;}.back-link:hover{text-decoration:underline;}
.message{padding:11px 14px;border-radius:8px;margin-bottom:18px;font-size:0.9rem;display:flex;align-items:center;gap:8px;}
.message.error{background:#fdecea;color:#c0392b;border:1px solid #f5c6cb;}.message.success{background:#eafaf1;color:#1e7e34;border:1px solid #b2dfdb;}
body.dark-mode .message.error{background:#3b1f1f;border-color:#7b3535;}body.dark-mode .message.success{background:#1a3327;border-color:#2d6a4f;color:#6fcf97;}
.mode-toggle-container{position:fixed;bottom:20px;right:24px;z-index:999;}#mode-toggle{font-size:18px;width:42px;height:42px;border-radius:50%;border:2px solid #28a745;background:#fff;color:#1a1a1a;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background 0.3s,color 0.3s,border-color 0.3s;box-shadow:0 2px 8px rgba(0,0,0,0.2);}#mode-toggle:hover{background: #28a745;color:#fff;}body.dark-mode #mode-toggle{background: #1a1a1a;color: #28a745;border-color: #28a745;}body.dark-mode #mode-toggle:hover{background: #28a745;color: #1a1a1a;}
.main-footer{text-align:center;padding:12px 10px;font-size:13px;flex-shrink:0;transition:background 0.3s,color 0.3s;}body.light-mode .main-footer{background:#f0f0f0;color:#555;}body.dark-mode .main-footer{background:#1a1a1a;color:#aaa;}
</style></head><body class="light-mode">
<script>(function(){if(localStorage.getItem('mode')==='dark'){document.body.classList.remove('light-mode');document.body.classList.add('dark-mode');}})();</script>
<?php include("../includes/header.php"); ?>
<div class="admin-wrap">
    <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h2 class="page-title"><i class="fa-solid fa-pen"></i> Edit Product</h2>
    <p class="page-sub">Update product details</p>
    <div class="form-card">
        <?php if($error): ?><div class="message error"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="message success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div><?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="input-group"><label>Product Name *</label><i class="fa-solid fa-box field-icon"></i><input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required></div>
            <div class="input-group"><label>Price (LKR) *</label><i class="fa-solid fa-tag field-icon"></i><input type="number" name="price" value="<?= $product['price'] ?>" min="1" step="0.01" required></div>
            <div class="input-group"><label>Category *</label><i class="fa-solid fa-list field-icon"></i>
                <select name="category">
                    <?php foreach(['Supplements','Equipment','Accessories','Other'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= $product['category']===$cat?'selected':'' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group"><label>Badge</label><i class="fa-solid fa-certificate field-icon"></i><input type="text" name="badge" value="<?= htmlspecialchars($product['badge'] ?? '') ?>" placeholder="Best Seller / New / Popular"></div>
            <div class="input-group"><label>Product Image</label><i class="fa-solid fa-image field-icon"></i>
                <?php if(!empty($product['image'])): ?><img src="../assets/images/<?= htmlspecialchars($product['image']) ?>" class="current-img" alt="current"><?php endif; ?>
                <input type="file" name="image" accept="image/*">
            </div>
            <button type="submit" class="btn-submit"><i class="fa-solid fa-floppy-disk"></i> &nbsp;Save Changes</button>
        </form>
    </div>
</div>
<div class="mode-toggle-container"><button id="mode-toggle"><i class="fa-solid fa-moon"></i></button></div>
<?php include("../includes/footer.php"); ?>
<script>(function(){var b=document.getElementById('mode-toggle'),i=b.querySelector('i');if(document.body.classList.contains('dark-mode')){i.classList.remove('fa-moon');i.classList.add('fa-sun');}b.addEventListener('click',function(){var d=document.body.classList.contains('dark-mode');if(d){document.body.classList.remove('dark-mode');document.body.classList.add('light-mode');i.classList.remove('fa-sun');i.classList.add('fa-moon');localStorage.setItem('mode','light');}else{document.body.classList.remove('light-mode');document.body.classList.add('dark-mode');i.classList.remove('fa-moon');i.classList.add('fa-sun');localStorage.setItem('mode','dark');}});})();</script>
</body></html>