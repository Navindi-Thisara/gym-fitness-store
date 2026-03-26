<?php
session_start();
if(!isset($_SESSION['user'])){ header("Location: ../auth/login.php"); exit; }

// ── Remove item ──
if(isset($_GET['remove'])){
    $rid = $_GET['remove'];
    unset($_SESSION['cart'][$rid]);
    header("Location: cart.php"); exit;
}

// ── Update quantity ──
if(isset($_POST['update_cart'])){
    foreach($_POST['qty'] as $pid => $qty){
        $qty = intval($qty);
        if($qty <= 0){
            unset($_SESSION['cart'][$pid]);
        } else {
            $_SESSION['cart'][$pid]['qty'] = $qty;
        }
    }
    header("Location: cart.php"); exit;
}

// ── Clear cart ──
if(isset($_GET['clear'])){
    unset($_SESSION['cart']);
    header("Location: cart.php"); exit;
}

$cart  = $_SESSION['cart'] ?? [];
$total = 0;
foreach($cart as $item){ $total += $item['price'] * $item['qty']; }
$itemCount = array_sum(array_column($cart, 'qty'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart | Gym Store</title>
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;}
html{height:100%;}
body{
    margin:0; min-height:100vh;
    display:flex; flex-direction:column;
    font-family:'Arial',sans-serif;
    transition:background 0.3s,color 0.3s;
}
body.light-mode{background:#f0f2f5;color:#222;}
body.dark-mode {background:#121212;color:#eee;}

/* ── Main content ── */
.main-content{
    flex:1; padding:36px 24px 60px;
    max-width:960px; margin:0 auto; width:100%;
}

/* ── Page title ── */
.page-title{
    font-size:1.6rem; font-weight:700; margin:0 0 6px;
    color:#1a1a1a; display:flex; align-items:center; gap:10px;
}
body.dark-mode .page-title{color:#f0f0f0;}
.page-title i{color:#28a745;}
.page-subtitle{color:#888;font-size:0.88rem;margin:0 0 28px;}

/* ── Empty cart ── */
.empty-cart{
    text-align:center; padding:70px 20px;
    background:#fff; border-radius:16px;
    box-shadow:0 4px 20px rgba(0,0,0,0.08);
    animation:slideUp 0.4s ease both;
}
body.dark-mode .empty-cart{background:#1e1e1e;box-shadow:0 4px 20px rgba(0,0,0,0.4);}
.empty-cart i{font-size:4rem;color:#ccc;display:block;margin-bottom:16px;}
body.dark-mode .empty-cart i{color:#444;}
.empty-cart h3{margin:0 0 8px;font-size:1.3rem;color:#555;}
body.dark-mode .empty-cart h3{color:#aaa;}
.empty-cart p{margin:0 0 24px;color:#999;font-size:0.9rem;}

@keyframes slideUp{
    from{opacity:0;transform:translateY(24px);}
    to  {opacity:1;transform:translateY(0);}
}

/* ── Cart layout ── */
.cart-layout{
    display:grid;
    grid-template-columns:1fr 320px;
    gap:24px;
    align-items:start;
}
@media(max-width:768px){.cart-layout{grid-template-columns:1fr;}}

/* ── Cart Items Card ── */
.cart-card{
    background:#fff; border-radius:16px;
    box-shadow:0 4px 20px rgba(0,0,0,0.08);
    overflow:hidden; animation:slideUp 0.4s ease both;
}
body.dark-mode .cart-card{background:#1e1e1e;box-shadow:0 4px 20px rgba(0,0,0,0.4);}

.cart-card-header{
    display:flex; align-items:center; justify-content:space-between;
    padding:18px 24px;
    border-bottom:1.5px solid #f0f0f0;
}
body.dark-mode .cart-card-header{border-color:#2a2a2a;}
.cart-card-header h3{margin:0;font-size:1rem;font-weight:700;color:#1a1a1a;}
body.dark-mode .cart-card-header h3{color:#f0f0f0;}
.cart-card-header .item-count{
    font-size:0.82rem;color:#888;
    background:#f0f2f5;padding:3px 10px;border-radius:20px;
}
body.dark-mode .cart-card-header .item-count{background:#2a2a2a;}

/* ── Cart Item Row ── */
.cart-item{
    display:flex; align-items:center; gap:16px;
    padding:16px 24px;
    border-bottom:1px solid #f5f5f5;
    transition:background 0.2s;
}
body.dark-mode .cart-item{border-color:#2a2a2a;}
.cart-item:last-child{border-bottom:none;}
.cart-item:hover{background:#fafffe;}
body.dark-mode .cart-item:hover{background:#232323;}

/* Item icon */
.cart-item-icon{
    width:54px; height:54px; border-radius:10px;
    background:linear-gradient(135deg,#e8f5e9,#f1f8e9);
    display:flex; align-items:center; justify-content:center;
    font-size:1.5rem; color:#28a745; flex-shrink:0;
}
body.dark-mode .cart-item-icon{background:linear-gradient(135deg,#1a2e1a,#1e2e1e);}

.cart-item-info{flex:1;}
.cart-item-info h4{margin:0 0 3px;font-size:0.95rem;font-weight:700;color:#1a1a1a;}
body.dark-mode .cart-item-info h4{color:#f0f0f0;}
.cart-item-info .item-price{font-size:0.85rem;color:#28a745;font-weight:600;}

/* Qty control */
.qty-control{
    display:flex; align-items:center; gap:0;
    border:1.5px solid #ddd; border-radius:8px;
    overflow:hidden; flex-shrink:0;
}
body.dark-mode .qty-control{border-color:#444;}
.qty-btn{
    width:32px; height:32px; border:none;
    background:#f5f5f5; color:#333; font-size:1rem;
    cursor:pointer; display:flex; align-items:center; justify-content:center;
    transition:background 0.2s;
}
body.dark-mode .qty-btn{background:#2a2a2a;color:#eee;}
.qty-btn:hover{background:#28a745;color:#fff;}
.qty-input{
    width:40px; height:32px; border:none;
    text-align:center; font-size:0.9rem; font-weight:700;
    color:#1a1a1a; background:#fff; outline:none;
}
body.dark-mode .qty-input{background:#1e1e1e;color:#eee;}

/* Item subtotal */
.cart-item-subtotal{
    font-size:0.95rem; font-weight:700;
    color:#1a1a1a; min-width:80px; text-align:right; flex-shrink:0;
}
body.dark-mode .cart-item-subtotal{color:#f0f0f0;}

/* Remove btn */
.btn-remove{
    background:none; border:none; color:#ccc;
    font-size:1rem; cursor:pointer; padding:4px;
    transition:color 0.2s; flex-shrink:0;
}
.btn-remove:hover{color:#e74c3c;}

/* Cart footer actions */
.cart-actions{
    display:flex; justify-content:space-between;
    align-items:center; padding:16px 24px;
    border-top:1.5px solid #f0f0f0;
    gap:10px; flex-wrap:wrap;
}
body.dark-mode .cart-actions{border-color:#2a2a2a;}

.btn-clear{
    padding:9px 18px; border-radius:8px;
    border:1.5px solid #e74c3c; color:#e74c3c;
    background:transparent; font-size:0.88rem; font-weight:600;
    cursor:pointer; transition:all 0.2s;
    display:flex; align-items:center; gap:6px;
}
.btn-clear:hover{background:#e74c3c;color:#fff;}

.btn-update{
    padding:9px 18px; border-radius:8px;
    border:1.5px solid #28a745; color:#28a745;
    background:transparent; font-size:0.88rem; font-weight:600;
    cursor:pointer; transition:all 0.2s;
    display:flex; align-items:center; gap:6px;
}
.btn-update:hover{background:#28a745;color:#fff;}

/* ── Order Summary Card ── */
.summary-card{
    background:#fff; border-radius:16px;
    box-shadow:0 4px 20px rgba(0,0,0,0.08);
    overflow:hidden; animation:slideUp 0.4s ease 0.1s both;
    position:sticky; top:20px;
}
body.dark-mode .summary-card{background:#1e1e1e;box-shadow:0 4px 20px rgba(0,0,0,0.4);}

.summary-header{
    background:linear-gradient(135deg,#28a745,#218838);
    padding:18px 24px;
}
.summary-header h3{margin:0;font-size:1rem;font-weight:700;color:#fff;display:flex;align-items:center;gap:8px;}

.summary-body{padding:20px 24px;}

.summary-row{
    display:flex; justify-content:space-between;
    align-items:center; margin-bottom:12px;
    font-size:0.9rem; color:#555;
}
body.dark-mode .summary-row{color:#aaa;}
.summary-row.total{
    font-size:1.1rem; font-weight:700;
    color:#1a1a1a; padding-top:12px;
    border-top:2px solid #f0f0f0; margin-top:4px;
}
body.dark-mode .summary-row.total{color:#f0f0f0;border-color:#2a2a2a;}
.summary-row.total .amount{color:#28a745;}

.btn-checkout{
    width:100%; padding:13px; background:#28a745; color:#fff;
    border:none; border-radius:10px; font-size:1rem; font-weight:700;
    cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;
    transition:background 0.3s,transform 0.15s,box-shadow 0.3s;
    box-shadow:0 4px 14px rgba(40,167,69,0.35); letter-spacing:0.3px;
    text-decoration:none; margin-top:16px;
}
.btn-checkout:hover{background:#218838;transform:translateY(-1px);box-shadow:0 6px 18px rgba(40,167,69,0.45);}
.btn-checkout:active{transform:translateY(0);}

.btn-continue{
    width:100%; padding:10px; border-radius:10px;
    border:1.5px solid #28a745; color:#28a745;
    background:transparent; font-size:0.9rem; font-weight:600;
    cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;
    transition:all 0.2s; margin-top:10px; text-decoration:none;
}
.btn-continue:hover{background:#28a745;color:#fff;}

.summary-note{
    font-size:0.78rem; color:#aaa; text-align:center;
    margin-top:14px; display:flex; align-items:center; justify-content:center; gap:5px;
}

/* ── Mode toggle fixed ── */
.mode-toggle-container{position:fixed;bottom:20px;right:24px;z-index:999;}
#mode-toggle{
    font-size:18px;width:44px;height:44px;
    border-radius:50%;border:2px solid #28a745;
    background:#fff;color:#1a1a1a;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    transition:background 0.3s,color 0.3s,border-color 0.3s;
    box-shadow:0 3px 12px rgba(0,0,0,0.2);
}
#mode-toggle:hover{background: #28a745;color: #fff;}
body.dark-mode #mode-toggle{background: #1a1a1a;color: #28a745;border-color: #28a745;}
body.dark-mode #mode-toggle:hover{background: #28a745;color: #1a1a1a;}

/* ── Footer ── */
.main-footer{text-align:center;padding:12px 10px;font-size:13px;flex-shrink:0;transition:background 0.3s,color 0.3s;}
body.light-mode .main-footer{background:#f0f0f0;color:#555;}
body.dark-mode  .main-footer{background:#1a1a1a;color:#aaa;}
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

<div class="main-content">

    <h2 class="page-title"><i class="fa-solid fa-cart-shopping"></i> My Cart</h2>
    <p class="page-subtitle">
        <?= $itemCount > 0 ? $itemCount.' item'.($itemCount>1?'s':'').' in your cart' : 'Your cart is empty' ?>
    </p>

    <?php if(empty($cart)): ?>

        <!-- Empty state -->
        <div class="empty-cart">
            <i class="fa-solid fa-cart-shopping"></i>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added anything yet. Browse our products and start shopping!</p>
            <a href="home.php" class="btn-checkout" style="display:inline-flex;width:auto;padding:12px 28px;">
                <i class="fa-solid fa-bag-shopping"></i> Browse Products
            </a>
        </div>

    <?php else: ?>

        <form method="POST" id="cartForm">
        <div class="cart-layout">

            <!-- ── Left: Cart Items ── -->
            <div class="cart-card">
                <div class="cart-card-header">
                    <h3><i class="fa-solid fa-box" style="color:#28a745;margin-right:6px;"></i> Cart Items</h3>
                    <span class="item-count"><?= $itemCount ?> item<?= $itemCount>1?'s':'' ?></span>
                </div>

                <?php foreach($cart as $pid => $item):
                    $subtotal = $item['price'] * $item['qty'];
                ?>
                <div class="cart-item">
                    <div class="cart-item-icon">
                        <i class="fa-solid fa-dumbbell"></i>
                    </div>
                    <div class="cart-item-info">
                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                        <div class="item-price">LKR <?= number_format($item['price'], 0) ?> each</div>
                    </div>

                    <!-- Qty control -->
                    <div class="qty-control">
                        <button type="button" class="qty-btn" onclick="changeQty(this,-1)">−</button>
                        <input type="number" name="qty[<?= $pid ?>]" class="qty-input"
                               value="<?= $item['qty'] ?>" min="1" max="99"
                               onchange="updateSubtotal(this)">
                        <button type="button" class="qty-btn" onclick="changeQty(this,1)">+</button>
                    </div>

                    <!-- Subtotal -->
                    <div class="cart-item-subtotal" id="sub_<?= $pid ?>">
                        LKR <?= number_format($subtotal, 0) ?>
                    </div>

                    <!-- Remove -->
                    <a href="cart.php?remove=<?= $pid ?>" class="btn-remove" title="Remove item"
                       onclick="return confirm('Remove this item from cart?')">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>
                <?php endforeach; ?>

                <!-- Cart footer -->
                <div class="cart-actions">
                    <a href="cart.php?clear=1" class="btn-clear"
                       onclick="return confirm('Clear all items from your cart?')">
                        <i class="fa-solid fa-trash"></i> Clear Cart
                    </a>
                    <button type="submit" name="update_cart" class="btn-update">
                        <i class="fa-solid fa-rotate"></i> Update Cart
                    </button>
                </div>
            </div>

            <!-- ── Right: Order Summary ── -->
            <div class="summary-card">
                <div class="summary-header">
                    <h3><i class="fa-solid fa-receipt"></i> Order Summary</h3>
                </div>
                <div class="summary-body">
                    <div class="summary-row">
                        <span>Subtotal (<?= $itemCount ?> item<?= $itemCount>1?'s':'' ?>)</span>
                        <span>LKR <?= number_format($total, 0) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span style="color:#28a745;font-weight:600;">Free</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (0%)</span>
                        <span>LKR 0</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span class="amount" id="grandTotal">LKR <?= number_format($total, 0) ?></span>
                    </div>

                    <a href="#" class="btn-checkout"
                       onclick="alert('Checkout coming soon!'); return false;">
                        <i class="fa-solid fa-lock"></i> Proceed to Checkout
                    </a>
                    <a href="home.php" class="btn-continue">
                        <i class="fa-solid fa-arrow-left"></i> Continue Shopping
                    </a>

                    <p class="summary-note">
                        <i class="fa-solid fa-shield-halved" style="color:#28a745;"></i>
                        Secure &amp; encrypted checkout
                    </p>
                </div>
            </div>

        </div>
        </form>

    <?php endif; ?>

</div>

<!-- Mode toggle fixed bottom-right -->
<div class="mode-toggle-container">
    <button id="mode-toggle" title="Toggle Light/Dark Mode">
        <i class="fa-solid fa-moon"></i>
    </button>
</div>

<?php include("../includes/footer.php"); ?>

<script>
/* ── Qty +/- buttons ── */
function changeQty(btn, delta){
    var input = btn.parentElement.querySelector('.qty-input');
    var val   = parseInt(input.value) + delta;
    if(val < 1) val = 1;
    if(val > 99) val = 99;
    input.value = val;
}

/* ── Dark / Light Mode ── */
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