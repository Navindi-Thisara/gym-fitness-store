<?php
session_start();

if(!isset($_SESSION['user'])){ header("Location: ../auth/login.php"); exit; }
include("../config/db.php");

// ── Add to Cart ──
if(isset($_POST['add_to_cart'])){
    $pid    = intval($_POST['product_id']);
    $pname  = trim($_POST['product_name']);
    $pprice = floatval($_POST['product_price']);

    if($pid > 0){
        if(!isset($_SESSION['cart'][$pid])){
            $_SESSION['cart'][$pid] = ['name'=>$pname,'price'=>$pprice,'qty'=>1];
        } else {
            $_SESSION['cart'][$pid]['qty']++;
        }
    }
    header("Location: ".$_SERVER['PHP_SELF']); exit;
}

// ── Contact form ──
$c_error = $c_success = "";
if(isset($_POST['contact_submit'])){
    $c_name    = trim($_POST['c_name']    ?? '');
    $c_email   = trim($_POST['c_email']   ?? '');
    $c_subject = trim($_POST['c_subject'] ?? '');
    $c_message = trim($_POST['c_message'] ?? '');

    if($c_name === '' || $c_email === '' || $c_message === ''){
        $c_error = "All fields are required. Please fill in every field.";
    } elseif(!preg_match('/^[a-zA-Z\s]+$/', $c_name)){
        $c_error = "Name can only contain letters and spaces.";
    } elseif(strlen($c_name) < 2){
        $c_error = "Name must be at least 2 characters.";
    } elseif(strlen($c_name) > 80){
        $c_error = "Name must not exceed 80 characters.";
    } elseif(strpos($c_email, '@') === false){
        $c_error = "Email address must contain an @ symbol.";
    } elseif(!filter_var($c_email, FILTER_VALIDATE_EMAIL)){
        $c_error = "Please enter a valid email address (e.g. you@example.com).";
    } elseif(strlen($c_message) < 10){
        $c_error = "Message is too short — please write at least 10 characters.";
    } elseif(strlen($c_message) > 2000){
        $c_error = "Message is too long — maximum 2000 characters allowed.";
    } else {
        $cn = $conn->real_escape_string($c_name);
        $ce = $conn->real_escape_string($c_email);
        $cs = $conn->real_escape_string($c_subject);
        $cm = $conn->real_escape_string($c_message);
        $conn->query("INSERT INTO contact_messages (name,email,subject,message,created_at)
                      VALUES ('$cn','$ce','$cs','$cm',NOW())");
        $c_success = "Thank you, <strong>" . htmlspecialchars($c_name) . "</strong>! We'll get back to you soon.";
        $_POST = [];
    }
}

// ── Load products from DB only ──
$all_products = [];
$result = $conn->query("SELECT * FROM products ORDER BY category, name");
if($result && $result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $all_products[] = $row;
    }
}
$categories = array_unique(array_column($all_products, 'category'));
sort($categories);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home | Gym Store</title>
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;}
html{height:100%;}
body{margin:0;min-height:100vh;display:flex;flex-direction:column;font-family:'Arial',sans-serif;transition:background 0.3s,color 0.3s;}
body.light-mode{background:#f0f2f5;color:#222;}
body.dark-mode{background:#121212;color:#eee;}

.main-content{flex:1;padding:30px 24px 70px;max-width:1300px;margin:0 auto;width:100%;}

.section-heading{font-size:1.5rem;font-weight:700;margin:0 0 4px;color:#1a1a1a;display:flex;align-items:center;gap:10px;}
body.dark-mode .section-heading{color:#f0f0f0;}
.section-heading i{color:#28a745;}
.section-sub{color:#888;font-size:0.88rem;margin:0 0 20px;}

/* ── Filter Bar ── */
.filter-bar{
    display:flex;flex-wrap:wrap;gap:12px;
    align-items:center;margin-bottom:28px;
    background:#fff;border-radius:12px;
    padding:14px 18px;
    box-shadow:0 2px 12px rgba(0,0,0,0.07);
}
body.dark-mode .filter-bar{background:#1e1e1e;box-shadow:0 2px 12px rgba(0,0,0,0.3);}

.search-wrap{position:relative;flex:1;min-width:180px;}
.search-wrap i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#aaa;font-size:0.9rem;}
#searchInput{
    width:100%;padding:10px 14px 10px 36px;
    border:1.5px solid #ddd;border-radius:8px;
    font-size:0.93rem;background:#fafafa;color:#222;outline:none;
    transition:border-color 0.2s,box-shadow 0.2s;
}
#searchInput:focus{border-color:#28a745;box-shadow:0 0 0 3px rgba(40,167,69,0.12);background:#fff;}
body.dark-mode #searchInput{background:#2a2a2a;border-color:#444;color:#eee;}
body.dark-mode #searchInput:focus{background:#2f2f2f;border-color:#28a745;}

.category-tabs{display:flex;flex-wrap:wrap;gap:8px;align-items:center;}
.tab-btn{
    padding:7px 16px;border-radius:20px;border:1.5px solid #ddd;
    background:transparent;font-size:0.85rem;font-weight:600;
    cursor:pointer;color:#555;transition:all 0.2s;
}
body.dark-mode .tab-btn{border-color:#444;color:#aaa;}
.tab-btn:hover{border-color:#28a745;color:#28a745;}
.tab-btn.active{background:#28a745;border-color:#28a745;color:#fff;}

.results-count{margin-left:auto;font-size:0.82rem;color:#999;white-space:nowrap;}
body.dark-mode .results-count{color:#666;}

/* ── Products Grid ── */
.products-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
    gap:20px;
}
.product-card{
    background:#fff;border-radius:14px;
    box-shadow:0 4px 18px rgba(0,0,0,0.07);
    overflow:hidden;display:flex;flex-direction:column;
    transition:transform 0.25s,box-shadow 0.25s;
    animation:fadeCard 0.4s ease both;
    position:relative;
}
.product-card:hover{transform:translateY(-5px);box-shadow:0 10px 28px rgba(0,0,0,0.13);}
body.dark-mode .product-card{background:#1e1e1e;box-shadow:0 4px 18px rgba(0,0,0,0.4);}
.product-card.hidden{display:none;}

@keyframes fadeCard{from{opacity:0;transform:translateY(18px);}to{opacity:1;transform:translateY(0);}}

.product-badge{
    position:absolute;top:10px;left:10px;
    background:#28a745;color:#fff;
    font-size:0.68rem;font-weight:700;
    padding:3px 9px;border-radius:20px;
    letter-spacing:0.5px;text-transform:uppercase;z-index:1;
}
.product-img-wrap{
    background:linear-gradient(135deg,#e8f5e9,#f1f8e9);
    height:150px;display:flex;align-items:center;justify-content:center;
    font-size:3.2rem;color:#28a745;flex-shrink:0;
}
body.dark-mode .product-img-wrap{background:linear-gradient(135deg,#1a2e1a,#1e2e1e);}
.product-img-wrap img{width:100%;height:100%;object-fit:cover;display:block;}

.product-info{padding:12px 14px 14px;flex:1;display:flex;flex-direction:column;}
.product-category-tag{font-size:0.72rem;color:#28a745;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;}
.product-info h3{margin:0 0 6px;font-size:0.95rem;font-weight:700;color:#1a1a1a;line-height:1.3;}
body.dark-mode .product-info h3{color:#f0f0f0;}
.product-info .price{font-size:1.1rem;font-weight:700;color:#28a745;margin:0 0 12px;}
.price-currency{font-size:0.78rem;font-weight:600;margin-right:1px;}

.btn-cart{
    margin-top:auto;
    padding:9px 14px;background:#28a745;color:#fff;
    border:none;border-radius:8px;font-size:0.88rem;font-weight:600;
    cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;
    transition:background 0.25s,transform 0.15s,box-shadow 0.25s;
    box-shadow:0 3px 10px rgba(40,167,69,0.28);width:100%;
}
.btn-cart:hover{background:#218838;transform:translateY(-1px);box-shadow:0 5px 14px rgba(40,167,69,0.4);}
.btn-cart:active{transform:translateY(0);}

.no-results{
    grid-column:1/-1;text-align:center;
    padding:60px 20px;color:#aaa;
}
.no-results i{font-size:3rem;display:block;margin-bottom:12px;color:#ccc;}
body.dark-mode .no-results i{color:#444;}

.section-divider{border:none;border-top:2px solid #e8e8e8;margin:50px 0 38px;}
body.dark-mode .section-divider{border-color:#2a2a2a;}

/* ── Contact Card ── */
.contact-card{
    background:#fff;border-radius:16px;
    box-shadow:0 6px 28px rgba(0,0,0,0.09);
    padding:0;max-width:720px;margin:0 auto;
    overflow:hidden;
}
body.dark-mode .contact-card{background:#1e1e1e;box-shadow:0 6px 28px rgba(0,0,0,0.4);}

.contact-card-header{
    background:linear-gradient(135deg,#28a745,#218838);
    padding:28px 36px 24px;color:#fff;
}
.contact-card-header h2{margin:0 0 4px;font-size:1.4rem;font-weight:700;display:flex;align-items:center;gap:10px;}
.contact-card-header p{margin:0;font-size:0.88rem;opacity:0.88;}

.contact-info-row{
    display:flex;gap:24px;padding:20px 36px;
    border-bottom:1.5px solid #f0f0f0;
    flex-wrap:wrap;
}
body.dark-mode .contact-info-row{border-color:#2a2a2a;}
.contact-info-item{display:flex;align-items:center;gap:8px;font-size:0.85rem;color:#555;}
body.dark-mode .contact-info-item{color:#aaa;}
.contact-info-item i{color:#28a745;font-size:0.9rem;width:16px;}

.contact-form-body{padding:28px 36px 32px;}

.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:0;}
@media(max-width:560px){.form-row{grid-template-columns:1fr;}}

.input-group{position:relative;margin-bottom:14px;}
.input-group label{display:block;font-size:0.8rem;font-weight:600;color:#555;margin-bottom:5px;letter-spacing:0.3px;}
body.dark-mode .input-group label{color:#aaa;}
.input-group .field-icon{position:absolute;left:13px;top:38px;color:#aaa;font-size:0.85rem;pointer-events:none;}
.input-group.textarea-group .field-icon{top:14px;transform:none;}

.input-group input,
.input-group select,
.input-group textarea{
    width:100%;padding:11px 14px 11px 38px;
    border:1.5px solid #e0e0e0;border-radius:10px;
    font-size:0.92rem;background:#fafafa;color:#222;
    outline:none;font-family:inherit;
    transition:border-color 0.2s,box-shadow 0.2s,background 0.2s;
}
.input-group textarea{height:120px;resize:vertical;padding-top:11px;}
.input-group select{appearance:none;-webkit-appearance:none;cursor:pointer;}
.input-group input:focus,
.input-group select:focus,
.input-group textarea:focus{
    border-color:#28a745;
    box-shadow:0 0 0 3px rgba(40,167,69,0.12);
    background:#fff;
}
body.dark-mode .input-group input,
body.dark-mode .input-group select,
body.dark-mode .input-group textarea{background:#2a2a2a;border-color:#3a3a3a;color:#eee;}
body.dark-mode .input-group input:focus,
body.dark-mode .input-group select:focus,
body.dark-mode .input-group textarea:focus{border-color:#28a745;background:#2f2f2f;box-shadow:0 0 0 3px rgba(40,167,69,0.18);}

.btn-send{
    width:100%;padding:13px;background:#28a745;color:#fff;
    border:none;border-radius:10px;font-size:1rem;font-weight:700;
    cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;
    transition:background 0.3s,transform 0.15s,box-shadow 0.3s;
    box-shadow:0 4px 14px rgba(40,167,69,0.35);letter-spacing:0.3px;margin-top:6px;
}
.btn-send:hover{background:#218838;transform:translateY(-1px);box-shadow:0 6px 18px rgba(40,167,69,0.45);}
.btn-send:active{transform:translateY(0);}

.message{padding:11px 14px;border-radius:8px;margin-bottom:18px;font-size:0.9rem;display:flex;align-items:center;gap:8px;}
.message.error{background:#fdecea;color:#c0392b;border:1px solid #f5c6cb;}
.message.success{background:#eafaf1;color:#1e7e34;border:1px solid #b2dfdb;}
body.dark-mode .message.error{background:#3b1f1f;border-color:#7b3535;color:#f5a5a5;}
body.dark-mode .message.success{background:#1a3327;border-color:#2d6a4f;color:#6fcf97;}

/* ── Mode Toggle ── */
.page-outer{flex:1;display:flex;flex-direction:column;}
.mode-toggle-container{position:fixed;bottom:80px;right:24px;z-index:999;}
#mode-toggle{
    font-size:18px;width:42px;height:42px;
    border-radius:50%;border:2px solid #28a745;
    background:#fff;color:#1a1a1a;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    transition:background 0.3s,color 0.3s,border-color 0.3s;
    box-shadow:0 2px 8px rgba(0,0,0,0.2);
}
#mode-toggle:hover{background: #28a745;color:#fff;}
body.dark-mode #mode-toggle{background: #1a1a1a;color: #28a745;border-color: #28a745;}
body.dark-mode #mode-toggle:hover{background: #28a745;color: #1a1a1a;}

/* ── Footer ── */
.main-footer{text-align:center;padding:12px 10px;font-size:13px;flex-shrink:0;transition:background 0.3s,color 0.3s;}
body.light-mode .main-footer{background:#f0f0f0;color:#555;}
body.dark-mode  .main-footer{background:#1a1a1a;color:#aaa;}

@media(max-width:600px){
    .main-content{padding:20px 12px 60px;}
    .filter-bar{padding:12px 14px;}
    .contact-card-header,.contact-form-body,.contact-info-row{padding-left:18px;padding-right:18px;}
}
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

<div class="page-outer">
<div class="main-content">

    <!-- ── Products ── -->
    <h2 class="section-heading"><i class="fa-solid fa-bag-shopping"></i> Our Products</h2>
    <p class="section-sub">Premium supplements, equipment &amp; accessories — prices in LKR</p>

    <div class="filter-bar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Search products…" oninput="filterProducts()">
        </div>
        <div class="category-tabs">
            <button class="tab-btn active" data-cat="all" onclick="setCategory(this)">All</button>
            <?php foreach($categories as $cat): ?>
                <button class="tab-btn" data-cat="<?= htmlspecialchars($cat) ?>" onclick="setCategory(this)">
                    <?= htmlspecialchars($cat) ?>
                </button>
            <?php endforeach; ?>
        </div>
        <span class="results-count" id="resultsCount"></span>
    </div>

    <div class="products-grid" id="productsGrid">
    <?php foreach($all_products as $i => $row):
        $icon     = $row['icon']     ?? 'fa-box';
        $badge    = $row['badge']    ?? '';
        $category = $row['category'] ?? 'Other';
        $pid      = intval($row['id']);
        $delay    = ($i % 8) * 0.06;
        $hasImg   = isset($row['image']) && !empty($row['image']) && file_exists("../assets/images/".$row['image']);
        $price    = number_format((float)$row['price'], 0);
    ?>
        <div class="product-card"
             data-name="<?= strtolower(htmlspecialchars($row['name'])) ?>"
             data-cat="<?= htmlspecialchars($category) ?>"
             style="animation-delay:<?= $delay ?>s">

            <?php if($badge): ?>
                <span class="product-badge"><?= htmlspecialchars($badge) ?></span>
            <?php endif; ?>

            <div class="product-img-wrap">
                <?php if($hasImg): ?>
                    <img src="../assets/images/<?= htmlspecialchars($row['image']) ?>"
                         alt="<?= htmlspecialchars($row['name']) ?>">
                <?php else: ?>
                    <i class="fa-solid <?= htmlspecialchars($icon) ?>"></i>
                <?php endif; ?>
            </div>

            <div class="product-info">
                <div class="product-category-tag"><?= htmlspecialchars($category) ?></div>
                <h3><?= htmlspecialchars($row['name']) ?></h3>
                <p class="price"><span class="price-currency">LKR</span> <?= $price ?></p>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="product_id"    value="<?= $pid ?>">
                    <input type="hidden" name="product_name"  value="<?= htmlspecialchars($row['name']) ?>">
                    <input type="hidden" name="product_price" value="<?= floatval($row['price']) ?>">
                    <button type="submit" name="add_to_cart" class="btn-cart">
                        <i class="fa-solid fa-cart-plus"></i> Add to Cart
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

        <div class="no-results" id="noResults" style="display:none;">
            <i class="fa-solid fa-box-open"></i>
            No products found. Try a different search or category.
        </div>
    </div>

    <hr class="section-divider">

    <!-- ── Contact ── -->
    <h2 class="section-heading"><i class="fa-solid fa-headset"></i> Get In Touch</h2>
    <p class="section-sub">Questions, feedback or bulk orders? We're here to help.</p>

    <div class="contact-card">
        <div class="contact-card-header">
            <h2><i class="fa-solid fa-envelope-open-text"></i> Contact Us</h2>
            <p>Fill in the form and our team will respond within 24 hours.</p>
        </div>
        <div class="contact-info-row">
            <span class="contact-info-item"><i class="fa-solid fa-phone"></i> +94 77 123 4567</span>
            <span class="contact-info-item"><i class="fa-solid fa-envelope"></i> support@gymstore.lk</span>
            <span class="contact-info-item"><i class="fa-solid fa-location-dot"></i> Colombo, Sri Lanka</span>
        </div>
        <div class="contact-form-body">
            <?php if($c_error): ?>
                <div class="message error"><i class="fa-solid fa-circle-exclamation"></i> <?= $c_error ?></div>
            <?php endif; ?>
            <?php if($c_success): ?>
                <div class="message success"><i class="fa-solid fa-circle-check"></i> <?= $c_success ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="form-row">
                    <div class="input-group">
                        <label>Full Name</label>
                        <i class="fa-solid fa-user field-icon"></i>
                        <input type="text" name="c_name" placeholder="John Silva"
                               value="<?= htmlspecialchars($_POST['c_name'] ?? '') ?>" required>
                    </div>
                    <div class="input-group">
                        <label>Email Address</label>
                        <i class="fa-solid fa-envelope field-icon"></i>
                        <input type="email" name="c_email" placeholder="you@example.com"
                               value="<?= htmlspecialchars($_POST['c_email'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="input-group">
                    <label>Subject</label>
                    <i class="fa-solid fa-tag field-icon"></i>
                    <select name="c_subject">
                        <option value="General Enquiry" <?= ($_POST['c_subject']??'')==='General Enquiry'?'selected':'' ?>>General Enquiry</option>
                        <option value="Order Support"   <?= ($_POST['c_subject']??'')==='Order Support'?'selected':'' ?>>Order Support</option>
                        <option value="Bulk Order"      <?= ($_POST['c_subject']??'')==='Bulk Order'?'selected':'' ?>>Bulk Order</option>
                        <option value="Product Info"    <?= ($_POST['c_subject']??'')==='Product Info'?'selected':'' ?>>Product Information</option>
                        <option value="Feedback"        <?= ($_POST['c_subject']??'')==='Feedback'?'selected':'' ?>>Feedback</option>
                    </select>
                </div>
                <div class="input-group textarea-group">
                    <label>Message</label>
                    <i class="fa-solid fa-message field-icon"></i>
                    <textarea name="c_message" placeholder="Write your message here…" required><?= htmlspecialchars($_POST['c_message'] ?? '') ?></textarea>
                </div>
                <button type="submit" name="contact_submit" class="btn-send">
                    <i class="fa-solid fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
    </div>

</div><!-- end main-content -->

<div class="mode-toggle-container">
    <button id="mode-toggle" title="Toggle Light/Dark Mode">
        <i class="fa-solid fa-moon"></i>
    </button>
</div>
</div><!-- end page-outer -->

<?php include("../includes/footer.php"); ?>

<script>
var activeCategory = 'all';

function setCategory(btn){
    document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    activeCategory = btn.getAttribute('data-cat');
    filterProducts();
}

function filterProducts(){
    var query = document.getElementById('searchInput').value.toLowerCase().trim();
    var cards = document.querySelectorAll('.product-card');
    var visible = 0;
    cards.forEach(function(card){
        var name = card.getAttribute('data-name') || '';
        var cat  = card.getAttribute('data-cat')  || '';
        var matchSearch   = name.indexOf(query) !== -1;
        var matchCategory = activeCategory === 'all' || cat === activeCategory;
        if(matchSearch && matchCategory){
            card.classList.remove('hidden');
            visible++;
        } else {
            card.classList.add('hidden');
        }
    });
    document.getElementById('noResults').style.display = visible === 0 ? 'grid' : 'none';
    document.getElementById('resultsCount').textContent = visible + ' item' + (visible !== 1 ? 's' : '');
}

filterProducts();

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