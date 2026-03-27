<?php
session_start();
if(!isset($_SESSION['user'])){ header("Location: ../auth/login.php"); exit; }
include("../config/db.php");

$cart = $_SESSION['cart'] ?? [];
if(empty($cart)){ header("Location: cart.php"); exit; }

$user      = $_SESSION['user'];
$total     = 0;
$itemCount = 0;
foreach($cart as $item){
    $total     += $item['price'] * $item['qty'];
    $itemCount += $item['qty'];
}

$error = $success = "";

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])){
    $full_name = trim($_POST['full_name'] ?? '');
    $phone     = trim($_POST['phone']     ?? '');
    $address   = trim($_POST['address']   ?? '');
    $city      = trim($_POST['city']      ?? '');
    $postal    = trim($_POST['postal']    ?? '');
    $payment   = trim($_POST['payment']   ?? '');

    // ── Validations ──
    if(empty($full_name) || empty($phone) || empty($address) || empty($city) || empty($payment)){
        $error = "Please fill in all required fields.";
    } elseif(!preg_match('/^[a-zA-Z\s]+$/', $full_name)){
        $error = "Full name can only contain letters and spaces.";
    } elseif(!preg_match('/^[0-9+\s\-]{7,15}$/', $phone)){
        $error = "Please enter a valid phone number.";
    } elseif(strlen($address) < 5){
        $error = "Please enter a valid delivery address.";
    } else {
        // Save order to DB
        $uid  = intval($user['id']);
        $fn   = $conn->real_escape_string($full_name);
        $ph   = $conn->real_escape_string($phone);
        $addr = $conn->real_escape_string($address);
        $cty  = $conn->real_escape_string($city);
        $pst  = $conn->real_escape_string($postal);
        $pay  = $conn->real_escape_string($payment);
        $tot  = floatval($total);

        // Insert order
        $conn->query("INSERT INTO orders (user_id, full_name, phone, address, city, postal,
                      payment_method, total_amount, status, created_at)
                      VALUES ($uid,'$fn','$ph','$addr','$cty','$pst','$pay',$tot,'Pending',NOW())");
        $orderId = $conn->insert_id;

        // Insert order items
        foreach($cart as $pid => $item){
            $iname  = $conn->real_escape_string($item['name']);
            $iprice = floatval($item['price']);
            $iqty   = intval($item['qty']);
            $conn->query("INSERT INTO order_items (order_id, product_name, price, quantity)
                          VALUES ($orderId,'$iname',$iprice,$iqty)");
        }

        // Clear cart
        unset($_SESSION['cart']);
        $success = $orderId;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout | Gym Store</title>
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;}
html{height:100%;}
body{
    margin:0;min-height:100vh;
    display:flex;flex-direction:column;
    font-family:'Arial',sans-serif;
    transition:background 0.3s,color 0.3s;
}
body.light-mode{background:#f0f2f5;color:#222;}
body.dark-mode {background:#121212;color:#eee;}

.page-outer{flex:1;position:relative;display:flex;flex-direction:column;}

.main-content{
    flex:1;padding:36px 24px 60px;
    max-width:1100px;margin:0 auto;width:100%;
}

/* ── Page title ── */
.page-title{font-size:1.6rem;font-weight:700;margin:0 0 6px;color:#1a1a1a;display:flex;align-items:center;gap:10px;}
body.dark-mode .page-title{color:#f0f0f0;}
.page-title i{color:#28a745;}
.page-subtitle{color:#888;font-size:0.88rem;margin:0 0 28px;}

/* ── Layout ── */
.checkout-layout{
    display:grid;
    grid-template-columns:1fr 340px;
    gap:24px;align-items:start;
}
@media(max-width:860px){.checkout-layout{grid-template-columns:1fr;}}

/* ── Cards ── */
.checkout-card{
    background:#fff;border-radius:16px;
    box-shadow:0 4px 20px rgba(0,0,0,0.08);
    overflow:hidden;margin-bottom:20px;
    animation:slideUp 0.4s ease both;
}
body.dark-mode .checkout-card{background:#1e1e1e;box-shadow:0 4px 20px rgba(0,0,0,0.4);}

@keyframes slideUp{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}

.card-header{
    padding:16px 24px;
    border-bottom:1.5px solid #f0f0f0;
    display:flex;align-items:center;gap:10px;
}
body.dark-mode .card-header{border-color:#2a2a2a;}
.card-header h3{margin:0;font-size:1rem;font-weight:700;color:#1a1a1a;}
body.dark-mode .card-header h3{color:#f0f0f0;}
.card-header i{color:#28a745;font-size:1rem;}
.card-body{padding:22px 24px;}

/* ── Form elements ── */
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
@media(max-width:560px){.form-row{grid-template-columns:1fr;}}

.input-group{position:relative;margin-bottom:16px;}
.input-group label{display:block;font-size:0.8rem;font-weight:600;color:#555;margin-bottom:5px;}
body.dark-mode .input-group label{color:#aaa;}
.input-group .field-icon{position:absolute;left:13px;top:37px;color:#aaa;font-size:0.85rem;pointer-events:none;}
.input-group input,
.input-group select,
.input-group textarea{
    width:100%;padding:11px 14px 11px 38px;
    border:1.5px solid #e0e0e0;border-radius:10px;
    font-size:0.92rem;background:#fafafa;color:#222;
    outline:none;font-family:inherit;
    transition:border-color 0.2s,box-shadow 0.2s,background 0.2s;
}
.input-group textarea{height:80px;resize:vertical;padding-top:11px;}
.input-group select{appearance:none;cursor:pointer;}
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

/* ── Payment options ── */
.payment-options{display:flex;flex-direction:column;gap:10px;margin-bottom:6px;}
.payment-option{
    display:flex;align-items:center;gap:12px;
    padding:13px 16px;border-radius:10px;
    border:1.5px solid #e0e0e0;cursor:pointer;
    transition:border-color 0.2s,background 0.2s;
}
.payment-option:hover{border-color:#28a745;}
.payment-option.selected{border-color:#28a745;background:#f0faf2;}
body.dark-mode .payment-option{border-color:#3a3a3a;}
body.dark-mode .payment-option.selected{background:#1a2e1a;border-color:#28a745;}
.payment-option input[type="radio"]{accent-color:#28a745;width:16px;height:16px;}
.payment-option-label{flex:1;}
.payment-option-label strong{display:block;font-size:0.92rem;color:#1a1a1a;}
body.dark-mode .payment-option-label strong{color:#f0f0f0;}
.payment-option-label span{font-size:0.78rem;color:#888;}
.payment-option i{font-size:1.3rem;color:#28a745;width:24px;text-align:center;}

/* ── Order summary card (right) ── */
.summary-card{
    background:#fff;border-radius:16px;
    box-shadow:0 4px 20px rgba(0,0,0,0.08);
    overflow:hidden;position:sticky;top:20px;
    animation:slideUp 0.4s ease 0.1s both;
}
body.dark-mode .summary-card{background:#1e1e1e;box-shadow:0 4px 20px rgba(0,0,0,0.4);}

.summary-header{background:linear-gradient(135deg,#28a745,#218838);padding:18px 24px;}
.summary-header h3{margin:0;font-size:1rem;font-weight:700;color:#fff;display:flex;align-items:center;gap:8px;}

.summary-body{padding:20px 24px;}

.summary-item{
    display:flex;justify-content:space-between;
    font-size:0.85rem;color:#555;margin-bottom:8px;
    padding-bottom:8px;border-bottom:1px solid #f5f5f5;
}
body.dark-mode .summary-item{color:#aaa;border-color:#2a2a2a;}
.summary-item:last-of-type{border-bottom:none;}
.summary-item .item-name{flex:1;padding-right:8px;}
.summary-item .item-qty{color:#999;font-size:0.78rem;}

.summary-divider{border:none;border-top:2px solid #f0f0f0;margin:12px 0;}
body.dark-mode .summary-divider{border-color:#2a2a2a;}

.summary-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;font-size:0.9rem;color:#555;}
body.dark-mode .summary-row{color:#aaa;}
.summary-row.total{font-size:1.1rem;font-weight:700;color:#1a1a1a;margin-top:4px;}
body.dark-mode .summary-row.total{color:#f0f0f0;}
.summary-row.total .amount{color:#28a745;}

.btn-order{
    width:100%;padding:13px;background:#28a745;color:#fff;
    border:none;border-radius:10px;font-size:1rem;font-weight:700;
    cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;
    transition:background 0.3s,transform 0.15s,box-shadow 0.3s;
    box-shadow:0 4px 14px rgba(40,167,69,0.35);letter-spacing:0.3px;margin-top:16px;
}
.btn-order:hover{background:#218838;transform:translateY(-1px);box-shadow:0 6px 18px rgba(40,167,69,0.45);}
.btn-order:active{transform:translateY(0);}

.secure-note{font-size:0.76rem;color:#aaa;text-align:center;margin-top:12px;display:flex;align-items:center;justify-content:center;gap:5px;}

/* ── Messages ── */
.message{padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:0.9rem;display:flex;align-items:center;gap:8px;}
.message.error  {background:#fdecea;color:#c0392b;border:1px solid #f5c6cb;}
body.dark-mode .message.error{background:#3b1f1f;border-color:#7b3535;color:#f5a5a5;}

/* ── Success screen ── */
.success-screen{
    text-align:center;padding:60px 30px;
    background:#fff;border-radius:16px;
    box-shadow:0 4px 20px rgba(0,0,0,0.08);
    animation:slideUp 0.5s ease both;
}
body.dark-mode .success-screen{background:#1e1e1e;}
.success-screen .check-icon{
    width:80px;height:80px;border-radius:50%;
    background:linear-gradient(135deg,#28a745,#218838);
    display:flex;align-items:center;justify-content:center;
    margin:0 auto 20px;font-size:2.2rem;color:#fff;
    box-shadow:0 6px 20px rgba(40,167,69,0.4);
}
.success-screen h2{margin:0 0 8px;font-size:1.6rem;font-weight:700;color:#1a1a1a;}
body.dark-mode .success-screen h2{color:#f0f0f0;}
.success-screen p{color:#888;margin:0 0 6px;font-size:0.95rem;}
.order-id-badge{
    display:inline-block;background:#f0faf2;color:#28a745;
    font-weight:700;font-size:1rem;padding:8px 20px;
    border-radius:8px;border:1.5px solid #b2dfdb;margin:16px 0 24px;
}
body.dark-mode .order-id-badge{background:#1a3327;border-color:#2d6a4f;}
.success-actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;}
.btn-success-primary{
    padding:11px 24px;background:#28a745;color:#fff;
    border:none;border-radius:10px;font-size:0.95rem;font-weight:700;
    cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:7px;
    box-shadow:0 3px 12px rgba(40,167,69,0.35);transition:background 0.2s;
}
.btn-success-primary:hover{background:#218838;}
.btn-success-outline{
    padding:11px 24px;border:1.5px solid #28a745;color:#28a745;
    background:transparent;border-radius:10px;font-size:0.95rem;font-weight:700;
    cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:7px;
    transition:all 0.2s;
}
.btn-success-outline:hover{background:#28a745;color:#fff;}

/* ── Mode toggle ── */
.mode-toggle-container{position:absolute;bottom:16px;right:24px;z-index:10;}
#mode-toggle{
    font-size:18px;width:42px;height:42px;
    border-radius:50%;border:2px solid #28a745;
    background:#fff;color:#1a1a1a;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    transition:background 0.3s,color 0.3s,border-color 0.3s;
    box-shadow:0 2px 8px rgba(0,0,0,0.2);
}
#mode-toggle:hover{background:#28a745;color:#fff;}
body.dark-mode #mode-toggle{background:#1a1a1a;color:#f0c040;border-color:#f0c040;}
body.dark-mode #mode-toggle:hover{background:#f0c040;color:#1a1a1a;}

/* ── Footer ── */
.main-footer{text-align:center;padding:12px 10px;font-size:13px;flex-shrink:0;transition:background 0.3s,color 0.3s;}
body.light-mode .main-footer{background:#f0f0f0;color:#555;}
body.dark-mode  .main-footer{background:#1a1a1a;color:#aaa;}

@media(max-width:600px){.main-content{padding:20px 12px 50px;}.card-body{padding:16px 16px;}}
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

<?php if($success): ?>
    <!-- ── Order Success Screen ── -->
    <div class="success-screen">
        <div class="check-icon"><i class="fa-solid fa-check"></i></div>
        <h2>Order Placed Successfully!</h2>
        <p>Thank you, <strong><?= htmlspecialchars($user['name']) ?></strong>! Your order has been received.</p>
        <p style="color:#aaa;font-size:0.85rem;">We'll contact you shortly to confirm your delivery.</p>
        <div class="order-id-badge"><i class="fa-solid fa-receipt"></i> Order #<?= str_pad($success, 5, '0', STR_PAD_LEFT) ?></div>
        <div class="success-actions">
            <a href="home.php" class="btn-success-primary">
                <i class="fa-solid fa-bag-shopping"></i> Continue Shopping
            </a>
        </div>
    </div>

<?php else: ?>
    <!-- ── Checkout Form ── -->
    <h2 class="page-title"><i class="fa-solid fa-lock"></i> Checkout</h2>
    <p class="page-subtitle">Complete your order — <?= $itemCount ?> item<?= $itemCount>1?'s':'' ?> · LKR <?= number_format($total, 0) ?></p>

    <?php if($error): ?>
        <div class="message error"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
    <div class="checkout-layout">

        <!-- ── Left column ── -->
        <div>
            <!-- Delivery Details -->
            <div class="checkout-card">
                <div class="card-header">
                    <i class="fa-solid fa-location-dot"></i>
                    <h3>Delivery Details</h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="input-group">
                            <label>Full Name *</label>
                            <i class="fa-solid fa-user field-icon"></i>
                            <input type="text" name="full_name" placeholder="John Silva"
                                   value="<?= htmlspecialchars($_POST['full_name'] ?? $user['name']) ?>" required>
                        </div>
                        <div class="input-group">
                            <label>Phone Number *</label>
                            <i class="fa-solid fa-phone field-icon"></i>
                            <input type="text" name="phone" placeholder="+94 77 123 4567"
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Delivery Address *</label>
                        <i class="fa-solid fa-house field-icon"></i>
                        <input type="text" name="address" placeholder="No. 12, Main Street"
                               value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required>
                    </div>
                    <div class="form-row">
                        <div class="input-group">
                            <label>City *</label>
                            <i class="fa-solid fa-city field-icon"></i>
                            <input type="text" name="city" placeholder="Colombo"
                                   value="<?= htmlspecialchars($_POST['city'] ?? '') ?>" required>
                        </div>
                        <div class="input-group">
                            <label>Postal Code</label>
                            <i class="fa-solid fa-hashtag field-icon"></i>
                            <input type="text" name="postal" placeholder="00100"
                                   value="<?= htmlspecialchars($_POST['postal'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="checkout-card">
                <div class="card-header">
                    <i class="fa-solid fa-credit-card"></i>
                    <h3>Payment Method</h3>
                </div>
                <div class="card-body">
                    <div class="payment-options">
                        <label class="payment-option <?= ($_POST['payment']??'cod')==='cod'?'selected':'' ?>">
                            <input type="radio" name="payment" value="cod"
                                   <?= ($_POST['payment']??'cod')==='cod'?'checked':'' ?> required>
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <div class="payment-option-label">
                                <strong>Cash on Delivery</strong>
                                <span>Pay when your order arrives</span>
                            </div>
                        </label>
                        <label class="payment-option <?= ($_POST['payment']??'')==='bank'?'selected':'' ?>">
                            <input type="radio" name="payment" value="bank"
                                   <?= ($_POST['payment']??'')==='bank'?'checked':'' ?>>
                            <i class="fa-solid fa-building-columns"></i>
                            <div class="payment-option-label">
                                <strong>Bank Transfer</strong>
                                <span>Transfer before delivery</span>
                            </div>
                        </label>
                        <label class="payment-option <?= ($_POST['payment']??'')==='card'?'selected':'' ?>">
                            <input type="radio" name="payment" value="card"
                                   <?= ($_POST['payment']??'')==='card'?'checked':'' ?>>
                            <i class="fa-solid fa-credit-card"></i>
                            <div class="payment-option-label">
                                <strong>Credit / Debit Card</strong>
                                <span>Coming soon</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Right: Order Summary ── -->
        <div class="summary-card">
            <div class="summary-header">
                <h3><i class="fa-solid fa-receipt"></i> Order Summary</h3>
            </div>
            <div class="summary-body">
                <?php foreach($cart as $item): ?>
                <div class="summary-item">
                    <span class="item-name"><?= htmlspecialchars($item['name']) ?></span>
                    <span>
                        <span class="item-qty">×<?= $item['qty'] ?></span>
                        &nbsp;LKR <?= number_format($item['price'] * $item['qty'], 0) ?>
                    </span>
                </div>
                <?php endforeach; ?>

                <hr class="summary-divider">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>LKR <?= number_format($total, 0) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span style="color:#28a745;font-weight:600;">Free</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span class="amount">LKR <?= number_format($total, 0) ?></span>
                </div>

                <button type="submit" name="place_order" class="btn-order">
                    <i class="fa-solid fa-bag-shopping"></i> Place Order
                </button>

                <p class="secure-note">
                    <i class="fa-solid fa-shield-halved" style="color:#28a745;"></i>
                    Secure &amp; safe checkout
                </p>
            </div>
        </div>

    </div>
    </form>

<?php endif; ?>

</div><!-- end main-content -->

<div class="mode-toggle-container">
    <button id="mode-toggle" title="Toggle Light/Dark Mode">
        <i class="fa-solid fa-moon"></i>
    </button>
</div>
</div><!-- end page-outer -->

<?php include("../includes/footer.php"); ?>

<script>
// Payment option highlight
document.querySelectorAll('.payment-option').forEach(function(opt){
    opt.addEventListener('click', function(){
        document.querySelectorAll('.payment-option').forEach(function(o){ o.classList.remove('selected'); });
        this.classList.add('selected');
    });
});

// Dark/Light Mode
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