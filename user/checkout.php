<?php
session_start();
if(!isset($_SESSION['user'])){ header("Location: ../auth/login.php"); exit; }
include("../config/db.php");

// ── Single item Buy Now support ──
$buyPid = $_GET['buy_pid'] ?? null;
if($buyPid && isset($_SESSION['cart'][$buyPid])){
    // Checkout only this one item
    $cart = [ $buyPid => $_SESSION['cart'][$buyPid] ];
    $isSingleItem = true;
} else {
    // Checkout entire cart
    $cart = $_SESSION['cart'] ?? [];
    $isSingleItem = false;
}

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
    }

    /* ── CARD VALIDATION ── */
    if(empty($error) && $payment === 'card'){
        $card_number = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
        $card_name   = trim($_POST['card_name'] ?? '');
        $card_expiry = trim($_POST['card_expiry'] ?? '');
        $card_cvv    = trim($_POST['card_cvv'] ?? '');

        if(strlen($card_number) < 13 || !ctype_digit($card_number)){
            $error = "Please enter a valid card number.";
        } elseif(empty($card_name)){
            $error = "Please enter the cardholder name.";
        } elseif(!preg_match('/^\d{2}\/\d{2}$/', $card_expiry)){
            $error = "Please enter expiry date as MM/YY.";
        } elseif(strlen($card_cvv) < 3 || !ctype_digit($card_cvv)){
            $error = "Please enter a valid CVV.";
        }
    }

    /* ── SAVE ORDER ── */
    if(empty($error)){
        $uid  = intval($user['id']);
        $fn   = $conn->real_escape_string($full_name);
        $ph   = $conn->real_escape_string($phone);
        $addr = $conn->real_escape_string($address);
        $cty  = $conn->real_escape_string($city);
        $pst  = $conn->real_escape_string($postal);
        $pay  = $conn->real_escape_string($payment);
        $tot  = floatval($total);

        // Optional improvement
        $status = ($payment === 'card') ? 'Paid' : 'Pending';

        // ── Stock validation ──
        foreach($cart as $pid => $item){
            $product_id = intval($pid);
            $iqty       = intval($item['qty']);

            $result = $conn->query("SELECT quantity, name FROM products WHERE id = $product_id");
            $product = $result->fetch_assoc();

            if(!$product || $product['quantity'] < $iqty){
                $pname = htmlspecialchars($product['name'] ?? 'A product');
                $error = "$pname is out of stock or has insufficient quantity.";
                break;
            }
        }

        // Insert order
        $conn->query("INSERT INTO orders (user_id, full_name, phone, address, city, postal,
                    payment_method, total_amount, status, created_at)
                    VALUES ($uid,'$fn','$ph','$addr','$cty','$pst','$pay',$tot,'$status',NOW())");

        $orderId = $conn->insert_id;

        // Insert order items
        foreach($cart as $pid => $item){
            $product_id = intval($pid);
            $iprice     = floatval($item['price']);
            $iqty       = intval($item['qty']);

            $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price)
                        VALUES ($orderId, $product_id, $iqty, $iprice)");

            // Decrement product stock
            $conn->query("UPDATE products SET quantity = quantity - $iqty WHERE id = $product_id");
        }

        // Clear cart
        foreach($cart as $pid => $item){
            unset($_SESSION['cart'][$pid]);
        }
        if(empty($_SESSION['cart'])) unset($_SESSION['cart']);

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
.mode-toggle-container{position:fixed;bottom:80px;right:24px;z-index:999;}
#mode-toggle{
    font-size:18px;width:42px;height:42px;
    border-radius:50%;border:2px solid #28a745;
    background:#fff;color:#1a1a1a;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    transition:background 0.3s,color 0.3s,border-color 0.3s;
    box-shadow:0 2px 8px rgba(0,0,0,0.2);
}
#mode-toggle:hover{background:#28a745;color:#fff;}
body.dark-mode #mode-toggle{background:#1a1a1a;color:#28a745;border-color:#28a745;}
body.dark-mode #mode-toggle:hover{background:#28a745;color:#1a1a1a;}

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
    <!-- ── Order Success + Printable Payment Slip ── -->

    <!-- Print styles -->
    <style>
    @media print {
        body * { visibility:hidden; }
        #paymentSlip, #paymentSlip * { visibility:visible; }
        #paymentSlip { position:fixed;top:0;left:0;width:100%;padding:30px; }
        .no-print { display:none !important; }
        body { background:#fff !important; }
    }
    #paymentSlip {
        background:#fff; border-radius:16px;
        box-shadow:0 4px 20px rgba(0,0,0,0.08);
        max-width:680px; margin:0 auto 24px;
        overflow:hidden; animation:slideUp 0.5s ease both;
    }
    body.dark-mode #paymentSlip { background:#1e1e1e; }
    .slip-header {
        background:linear-gradient(135deg,#28a745,#218838);
        padding:24px 32px; color:#fff; text-align:center;
    }
    .slip-header h2 { margin:0 0 4px; font-size:1.4rem; }
    .slip-header p  { margin:0; font-size:0.85rem; opacity:0.88; }
    .slip-order-badge {
        display:inline-block; background:rgba(255,255,255,0.2);
        border:1.5px solid rgba(255,255,255,0.4);
        border-radius:8px; padding:6px 18px;
        font-size:1rem; font-weight:800; margin-top:12px;
        letter-spacing:1px;
    }
    .slip-body { padding:28px 32px; }
    .slip-section-title {
        font-size:0.75rem; font-weight:700; color:#28a745;
        text-transform:uppercase; letter-spacing:0.8px;
        margin:0 0 10px; display:flex; align-items:center; gap:6px;
    }
    .slip-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px 20px; margin-bottom:20px; }
    .slip-field { font-size:0.85rem; color:#555; }
    body.dark-mode .slip-field { color:#aaa; }
    .slip-field strong { display:block; font-size:0.78rem; color:#aaa; margin-bottom:2px; text-transform:uppercase; letter-spacing:0.3px; }
    .slip-divider { border:none; border-top:1.5px dashed #e0e0e0; margin:18px 0; }
    body.dark-mode .slip-divider { border-color:#3a3a3a; }
    .slip-items table { width:100%; border-collapse:collapse; font-size:0.85rem; }
    .slip-items th { background:#f8f8f8; padding:8px 12px; text-align:left; font-size:0.75rem; color:#555; text-transform:uppercase; }
    body.dark-mode .slip-items th { background:#252525; color:#aaa; }
    .slip-items td { padding:9px 12px; border-bottom:1px solid #f5f5f5; color:#333; }
    body.dark-mode .slip-items td { color:#ccc; border-color:#2a2a2a; }
    .slip-items tr:last-child td { border-bottom:none; }
    .slip-totals { margin-top:14px; }
    .slip-total-row { display:flex; justify-content:space-between; font-size:0.88rem; color:#555; margin-bottom:8px; }
    body.dark-mode .slip-total-row { color:#aaa; }
    .slip-total-row.grand { font-size:1.05rem; font-weight:800; color:#1a1a1a; border-top:2px solid #e0e0e0; padding-top:10px; margin-top:4px; }
    body.dark-mode .slip-total-row.grand { color:#f0f0f0; border-color:#3a3a3a; }
    .slip-total-row.grand span:last-child { color:#28a745; }
    .slip-footer { background:#f8f8f8; padding:14px 32px; text-align:center; font-size:0.78rem; color:#aaa; }
    body.dark-mode .slip-footer { background:#252525; }
    .btn-print {
        padding:11px 28px; background:#28a745; color:#fff;
        border:none; border-radius:10px; font-size:0.95rem; font-weight:700;
        cursor:pointer; display:inline-flex; align-items:center; gap:8px;
        transition:background 0.2s; box-shadow:0 3px 12px rgba(40,167,69,0.3);
        text-decoration:none;
    }
    .btn-print:hover { background:#218838; }
    .btn-shop {
        padding:11px 24px; border:1.5px solid #28a745; color:#28a745;
        background:transparent; border-radius:10px; font-size:0.95rem; font-weight:700;
        cursor:pointer; display:inline-flex; align-items:center; gap:7px;
        transition:all 0.2s; text-decoration:none;
    }
    .btn-shop:hover { background:#28a745; color:#fff; }
    </style>

    <div class="success-actions no-print" style="text-align:center;margin-bottom:20px;">
        <div style="width:70px;height:70px;border-radius:50%;background:linear-gradient(135deg,#28a745,#218838);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:2rem;color:#fff;box-shadow:0 6px 20px rgba(40,167,69,0.4);">
            <i class="fa-solid fa-check"></i>
        </div>
        <h2 style="margin:0 0 6px;font-size:1.5rem;font-weight:700;">Order Placed Successfully!</h2>
        <p style="color:#888;margin:0 0 20px;">Your payment slip is ready below. Print or save it for your records.</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <button class="btn-print" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Print Payment Slip
            </button>
            <a href="home.php" class="btn-shop">
                <i class="fa-solid fa-bag-shopping"></i> Continue Shopping
            </a>
        </div>
    </div>

    <!-- ── Printable Payment Slip ── -->
    <div id="paymentSlip">
        <div class="slip-header">
            <h2><i class="fa-solid fa-dumbbell"></i> Gym &amp; Fitness Store</h2>
            <p>Payment Receipt &amp; Order Confirmation</p>
            <div class="slip-order-badge">
                ORDER #<?= str_pad($success, 5, '0', STR_PAD_LEFT) ?>
            </div>
        </div>

        <div class="slip-body">
            <!-- Customer Info -->
            <div class="slip-section-title"><i class="fa-solid fa-user"></i> Customer Details</div>
            <div class="slip-grid">
                <div class="slip-field"><strong>Name</strong><?= htmlspecialchars($user['name']) ?></div>
                <div class="slip-field"><strong>Email</strong><?= htmlspecialchars($user['email']) ?></div>
                <div class="slip-field"><strong>Phone</strong><?= htmlspecialchars($_POST['phone'] ?? '—') ?></div>
                <div class="slip-field"><strong>Date</strong><?= date('d M Y, h:i A') ?></div>
            </div>

            <hr class="slip-divider">

            <!-- Delivery Info -->
            <div class="slip-section-title"><i class="fa-solid fa-location-dot"></i> Delivery Address</div>
            <div class="slip-grid">
                <div class="slip-field"><strong>Address</strong><?= htmlspecialchars($_POST['address'] ?? '—') ?></div>
                <div class="slip-field"><strong>City</strong><?= htmlspecialchars(($_POST['city'] ?? '—').(!empty($_POST['postal']) ? ' - '.$_POST['postal'] : '')) ?></div>
            </div>

            <hr class="slip-divider">

            <!-- Payment Info -->
            <div class="slip-section-title"><i class="fa-solid fa-credit-card"></i> Payment Details</div>
            <div class="slip-grid">
                <div class="slip-field"><strong>Payment Method</strong>
                    <?php
                    $pm = $_POST['payment'] ?? 'cod';
                    echo $pm === 'cod' ? 'Cash on Delivery' : ($pm === 'bank' ? 'Bank Transfer' : 'Credit / Debit Card');
                    ?>
                </div>
                <?php if(($_POST['payment'] ?? '') === 'card' && !empty($_POST['card_number'])): ?>
                <div class="slip-field"><strong>Card</strong>
                    **** **** **** <?= substr(preg_replace('/\s+/','',$_POST['card_number']),-4) ?>
                </div>
                <?php endif; ?>
                <div class="slip-field"><strong>Status</strong><span style="color:#28a745;font-weight:700;">Confirmed</span></div>
            </div>

            <hr class="slip-divider">

            <!-- Order Items -->
            <div class="slip-section-title"><i class="fa-solid fa-box"></i> Ordered Items</div>
            <div class="slip-items">
                <table>
                    <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
                    <tbody>
                    <?php foreach($cart as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= $item['qty'] ?></td>
                            <td>LKR <?= number_format($item['price'], 0) ?></td>
                            <td>LKR <?= number_format($item['price'] * $item['qty'], 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="slip-totals">
                <div class="slip-total-row"><span>Subtotal</span><span>LKR <?= number_format($total, 0) ?></span></div>
                <div class="slip-total-row"><span>Shipping</span><span style="color:#28a745;">Free</span></div>
                <div class="slip-total-row"><span>Tax</span><span>LKR 0</span></div>
                <div class="slip-total-row grand"><span>Total Paid</span><span>LKR <?= number_format($total, 0) ?></span></div>
            </div>
        </div>

        <div class="slip-footer">
            Thank you for shopping at Gym &amp; Fitness Store! &nbsp;|&nbsp;
            support@gymstore.lk &nbsp;|&nbsp; +94 77 123 4567
        </div>
    </div>

<?php else: ?>
    <!-- ── Checkout Form ── -->
    <h2 class="page-title"><i class="fa-solid fa-lock"></i> Checkout</h2>
    <p class="page-subtitle">Complete your order — <?= $itemCount ?> item<?= $itemCount>1?'s':'' ?> · LKR <?= number_format($total, 0) ?></p>

    <?php if($isSingleItem): ?>
        <div style="background:#fff3cd;color:#856404;border:1px solid #ffc107;padding:10px 14px;border-radius:8px;font-size:0.88rem;margin-bottom:18px;display:flex;align-items:center;gap:8px;">
            <i class="fa-solid fa-circle-info"></i>
            You are checking out <strong>1 item only</strong>. Other cart items will remain in your cart.
        </div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="message error"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="checkout.php<?= $buyPid ? '?buy_pid='.urlencode($buyPid) : '' ?>">
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
                                   <?= ($_POST['payment']??'cod')==='cod'?'checked':'' ?> required
                                   onchange="toggleCardForm()">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <div class="payment-option-label">
                                <strong>Cash on Delivery</strong>
                                <span>Pay when your order arrives</span>
                            </div>
                        </label>
                        <label class="payment-option <?= ($_POST['payment']??'')==='bank'?'selected':'' ?>">
                            <input type="radio" name="payment" value="bank"
                                   <?= ($_POST['payment']??'')==='bank'?'checked':'' ?>
                                   onchange="toggleCardForm()">
                            <i class="fa-solid fa-building-columns"></i>
                            <div class="payment-option-label">
                                <strong>Bank Transfer</strong>
                                <span>Transfer before delivery</span>
                            </div>
                        </label>
                        <label class="payment-option <?= ($_POST['payment']??'')==='card'?'selected':'' ?>">
                            <input type="radio" name="payment" value="card"
                                   <?= ($_POST['payment']??'')==='card'?'checked':'' ?>
                                   onchange="toggleCardForm()">
                            <i class="fa-solid fa-credit-card"></i>
                            <div class="payment-option-label">
                                <strong>Credit / Debit Card</strong>
                                <span>Visa, Mastercard accepted</span>
                            </div>
                        </label>
                    </div>

                    <!-- ── Credit Card Form ── -->
                    <div id="cardForm" style="display:<?= ($_POST['payment']??'')==='card'?'block':'none' ?>;margin-top:18px;">
                        <div style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:14px;padding:22px 24px;margin-bottom:18px;color:#fff;position:relative;overflow:hidden;">
                            <div style="font-size:0.75rem;opacity:0.6;margin-bottom:12px;letter-spacing:1px;">CARD NUMBER</div>
                            <div style="font-size:1.2rem;font-weight:700;letter-spacing:3px;margin-bottom:18px;" id="cardPreviewNum">•••• •••• •••• ••••</div>
                            <div style="display:flex;justify-content:space-between;align-items:flex-end;">
                                <div>
                                    <div style="font-size:0.65rem;opacity:0.6;letter-spacing:1px;">CARD HOLDER</div>
                                    <div style="font-size:0.88rem;font-weight:600;" id="cardPreviewName">YOUR NAME</div>
                                </div>
                                <div>
                                    <div style="font-size:0.65rem;opacity:0.6;letter-spacing:1px;">EXPIRES</div>
                                    <div style="font-size:0.88rem;font-weight:600;" id="cardPreviewExp">MM/YY</div>
                                </div>
                                <div style="font-size:2rem;opacity:0.8;">
                                    <i class="fa-brands fa-cc-visa"></i>
                                </div>
                            </div>
                        </div>

                        <div class="input-group" style="margin-bottom:14px;">
                            <label style="font-size:0.8rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">Card Number *</label>
                            <i class="fa-solid fa-credit-card field-icon" style="position:absolute;left:13px;top:37px;color:#aaa;font-size:0.85rem;pointer-events:none;"></i>
                            <input type="text" name="card_number" id="cardNumber"
                                   placeholder="1234 5678 9012 3456" maxlength="19"
                                   oninput="formatCardNum(this)"
                                   value="<?= htmlspecialchars($_POST['card_number'] ?? '') ?>">
                        </div>
                        <div class="input-group" style="margin-bottom:14px;">
                            <label style="font-size:0.8rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">Cardholder Name *</label>
                            <i class="fa-solid fa-user field-icon" style="position:absolute;left:13px;top:37px;color:#aaa;font-size:0.85rem;pointer-events:none;"></i>
                            <input type="text" name="card_name" id="cardName"
                                   placeholder="John Silva" oninput="updateCardPreview()"
                                   value="<?= htmlspecialchars($_POST['card_name'] ?? '') ?>">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div class="input-group" style="margin-bottom:0;">
                                <label style="font-size:0.8rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">Expiry Date *</label>
                                <i class="fa-solid fa-calendar field-icon" style="position:absolute;left:13px;top:37px;color:#aaa;font-size:0.85rem;pointer-events:none;"></i>
                                <input type="text" name="card_expiry" id="cardExpiry"
                                       placeholder="MM/YY" maxlength="5"
                                       oninput="formatExpiry(this)"
                                       value="<?= htmlspecialchars($_POST['card_expiry'] ?? '') ?>">
                            </div>
                            <div class="input-group" style="margin-bottom:0;">
                                <label style="font-size:0.8rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">CVV *</label>
                                <i class="fa-solid fa-lock field-icon" style="position:absolute;left:13px;top:37px;color:#aaa;font-size:0.85rem;pointer-events:none;"></i>
                                <input type="text" name="card_cvv" id="cardCvv"
                                       placeholder="123" maxlength="4"
                                       oninput="this.value=this.value.replace(/\D/g,'')"
                                       value="<?= htmlspecialchars($_POST['card_cvv'] ?? '') ?>">
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;margin-top:12px;font-size:0.78rem;color:#aaa;">
                            <i class="fa-solid fa-shield-halved" style="color:#28a745;"></i>
                            Your card details are encrypted and secure.
                        </div>
                    </div>

                    <!-- Bank transfer info -->
                    <div id="bankInfo" style="display:<?= ($_POST['payment']??'')==='bank'?'block':'none' ?>;margin-top:16px;background:#f0faf2;border-radius:10px;padding:14px 16px;border:1px solid #b2dfdb;">
                        <div style="font-size:0.82rem;font-weight:700;color:#28a745;margin-bottom:8px;"><i class="fa-solid fa-building-columns"></i> Bank Transfer Details</div>
                        <div style="font-size:0.83rem;color:#555;line-height:1.8;">
                            Bank: <strong>Bank of Ceylon</strong><br>
                            Account Name: <strong>Gym &amp; Fitness Store</strong><br>
                            Account No: <strong>1234-5678-9012</strong><br>
                            Branch: <strong>Colombo</strong>
                        </div>
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

/* ── Card form toggle ── */
function toggleCardForm(){
    var pay  = document.querySelector('input[name="payment"]:checked');
    var form = document.getElementById('cardForm');
    var bank = document.getElementById('bankInfo');
    if(form) form.style.display = (pay && pay.value === 'card') ? 'block' : 'none';
    if(bank) bank.style.display = (pay && pay.value === 'bank') ? 'block' : 'none';
}

/* ── Card number format: groups of 4 ── */
function formatCardNum(input){
    var v = input.value.replace(/\D/g,'').substring(0,16);
    var r = v.match(/.{1,4}/g);
    input.value = r ? r.join(' ') : v;
    var prev = document.getElementById('cardPreviewNum');
    if(prev) prev.textContent = (r ? r.join(' ') : v).padEnd(19,'•').replace(/[^•]/g, function(c,i){
        return (i < v.length) ? c : '•';
    }) || '•••• •••• •••• ••••';
    updateCardPreview();
}

/* ── Expiry format MM/YY ── */
function formatExpiry(input){
    var v = input.value.replace(/\D/g,'').substring(0,4);
    if(v.length >= 2) v = v.substring(0,2)+'/'+v.substring(2);
    input.value = v;
    var prev = document.getElementById('cardPreviewExp');
    if(prev) prev.textContent = v || 'MM/YY';
}

/* ── Update card visual preview ── */
function updateCardPreview(){
    var name = document.getElementById('cardName');
    var num  = document.getElementById('cardNumber');
    var prevName = document.getElementById('cardPreviewName');
    var prevNum  = document.getElementById('cardPreviewNum');
    if(prevName && name) prevName.textContent = name.value.toUpperCase() || 'YOUR NAME';
    if(prevNum  && num){
        var v = num.value.replace(/\D/g,'');
        var r = v.match(/.{1,4}/g);
        var display = r ? r.join(' ') : v;
        // Pad with bullets
        while(display.replace(/\s/g,'').length < 16) display += '•';
        prevNum.textContent = display.substring(0,19) || '•••• •••• •••• ••••';
    }
}

// Bind live events
var cn = document.getElementById('cardName');
if(cn) cn.addEventListener('input', updateCardPreview);

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