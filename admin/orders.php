<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){
    header("Location: ../auth/login.php"); exit;
}
include("../config/db.php");

// ── Update order status ──
if(isset($_POST['update_status'])){
    $oid    = intval($_POST['order_id']);
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE orders SET status='$status' WHERE id=$oid");
    header("Location: orders.php?updated=1"); exit;
}

// ── Delete order ──
if(isset($_GET['delete'])){
    $oid = intval($_GET['delete']);
    $conn->query("DELETE FROM order_items WHERE order_id=$oid");
    $conn->query("DELETE FROM orders WHERE id=$oid");
    header("Location: orders.php?deleted=1"); exit;
}

// ── Filter ──
$statusFilter  = $_GET['status'] ?? 'all';
$searchQuery   = trim($_GET['search'] ?? '');
$validStatuses = ['Pending','Completed','Cancelled'];

$where = [];
if($statusFilter !== 'all' && in_array($statusFilter, $validStatuses)){
    $where[] = "o.status='".$conn->real_escape_string($statusFilter)."'";
}
if($searchQuery !== ''){
    $sq      = $conn->real_escape_string($searchQuery);
    $where[] = "(u.name LIKE '%$sq%' OR o.full_name LIKE '%$sq%' OR o.id LIKE '%$sq%')";
}
$whereSQL = $where ? "WHERE ".implode(" AND ", $where) : "";

$orders = $conn->query("SELECT o.*, u.name as uname, u.email as uemail
                         FROM orders o
                         JOIN users u ON o.user_id = u.id
                         $whereSQL
                         ORDER BY o.created_at DESC");

// Stats
$totalOrders    = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$pendingOrders  = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='Pending'")->fetch_assoc()['c'];
$completedOrders= $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='Completed'")->fetch_assoc()['c'];
$totalRevenue   = $conn->query("SELECT SUM(total_amount) as s FROM orders")->fetch_assoc()['s'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Orders | Admin</title>
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;}
html{height:100%;}
body{margin:0;min-height:100vh;display:flex;flex-direction:column;font-family:'Arial',sans-serif;transition:background 0.3s,color 0.3s;}
body.light-mode{background:#f0f2f5;color:#222;}
body.dark-mode {background:#121212;color:#eee;}

.page-outer{flex:1;position:relative;display:flex;flex-direction:column;}
.admin-wrap{flex:1;max-width:1200px;margin:0 auto;width:100%;padding:32px 24px 80px;}

/* ── Titles ── */
.page-title{font-size:1.6rem;font-weight:700;margin:0 0 4px;color:#1a1a1a;display:flex;align-items:center;gap:10px;}
body.dark-mode .page-title{color:#f0f0f0;}
.page-title i{color:#28a745;}
.page-sub{color:#888;font-size:0.88rem;margin:0 0 24px;}
.back-link{display:inline-flex;align-items:center;gap:6px;color:#28a745;text-decoration:none;font-size:0.88rem;font-weight:600;margin-bottom:20px;}
.back-link:hover{text-decoration:underline;}

/* ── Stats ── */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:16px;margin-bottom:28px;}
.stat-card{background:#fff;border-radius:14px;padding:20px 22px;box-shadow:0 4px 18px rgba(0,0,0,0.07);display:flex;align-items:center;gap:14px;animation:fadeCard 0.4s ease both;}
body.dark-mode .stat-card{background:#1e1e1e;box-shadow:0 4px 18px rgba(0,0,0,0.4);}
@keyframes fadeCard{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
.stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#fff;flex-shrink:0;}
.stat-icon.green {background:linear-gradient(135deg,#28a745,#218838);}
.stat-icon.orange{background:linear-gradient(135deg,#e67e22,#d35400);}
.stat-icon.blue  {background:linear-gradient(135deg,#3498db,#2980b9);}
.stat-icon.purple{background:linear-gradient(135deg,#9b59b6,#8e44ad);}
.stat-info h3{margin:0 0 2px;font-size:1.4rem;font-weight:800;color:#1a1a1a;}
body.dark-mode .stat-info h3{color:#f0f0f0;}
.stat-info p{margin:0;font-size:0.8rem;color:#888;}

/* ── Filter bar ── */
.filter-bar{
    display:flex;flex-wrap:wrap;gap:12px;align-items:center;
    background:#fff;border-radius:12px;
    padding:14px 18px;margin-bottom:22px;
    box-shadow:0 2px 12px rgba(0,0,0,0.07);
}
body.dark-mode .filter-bar{background:#1e1e1e;box-shadow:0 2px 12px rgba(0,0,0,0.3);}

.search-wrap{position:relative;flex:1;min-width:180px;}
.search-wrap i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#aaa;font-size:0.9rem;}
#searchInput{
    width:100%;padding:10px 14px 10px 36px;
    border:1.5px solid #ddd;border-radius:8px;
    font-size:0.92rem;background:#fafafa;color:#222;outline:none;
    transition:border-color 0.2s,box-shadow 0.2s;
}
#searchInput:focus{border-color:#28a745;box-shadow:0 0 0 3px rgba(40,167,69,0.12);background:#fff;}
body.dark-mode #searchInput{background:#2a2a2a;border-color:#444;color:#eee;}
body.dark-mode #searchInput:focus{background:#2f2f2f;border-color:#28a745;}

.status-tabs{display:flex;flex-wrap:wrap;gap:8px;}
.tab-btn{
    padding:7px 16px;border-radius:20px;border:1.5px solid #ddd;
    background:transparent;font-size:0.84rem;font-weight:600;
    cursor:pointer;color:#555;transition:all 0.2s;
}
body.dark-mode .tab-btn{border-color:#444;color:#aaa;}
.tab-btn:hover{border-color:#28a745;color:#28a745;}
.tab-btn.active{background:#28a745;border-color:#28a745;color:#fff;}
.tab-btn.pending-tab.active {background:#e67e22;border-color:#e67e22;}
.tab-btn.completed-tab.active{background:#28a745;border-color:#28a745;}
.tab-btn.cancelled-tab.active{background:#e74c3c;border-color:#e74c3c;}

.results-count{margin-left:auto;font-size:0.82rem;color:#999;white-space:nowrap;}

/* ── Section card ── */
.section-card{background:#fff;border-radius:14px;box-shadow:0 4px 18px rgba(0,0,0,0.07);overflow:hidden;margin-bottom:20px;}
body.dark-mode .section-card{background:#1e1e1e;}
.section-card-header{padding:15px 22px;border-bottom:1.5px solid #f0f0f0;display:flex;align-items:center;gap:10px;}
body.dark-mode .section-card-header{border-color:#2a2a2a;}
.section-card-header h3{margin:0;font-size:0.98rem;font-weight:700;color:#1a1a1a;}
body.dark-mode .section-card-header h3{color:#f0f0f0;}
.section-card-header i{color:#28a745;}

/* ── Table ── */
table{width:100%;border-collapse:collapse;}
th{background:#f8f8f8;padding:10px 14px;font-size:0.77rem;font-weight:700;color:#555;text-align:left;text-transform:uppercase;letter-spacing:0.4px;}
body.dark-mode th{background:#252525;color:#aaa;}
td{padding:11px 14px;font-size:0.86rem;color:#333;border-bottom:1px solid #f5f5f5;vertical-align:middle;}
body.dark-mode td{color:#ccc;border-color:#2a2a2a;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fafffe;}
body.dark-mode tr:hover td{background:#232323;}

/* ── Status badge ── */
.status-badge{padding:3px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;text-transform:uppercase;}
.status-badge.pending  {background:#fff3cd;color:#856404;}
.status-badge.completed{background:#d4edda;color:#155724;}
.status-badge.cancelled{background:#f8d7da;color:#721c24;}
body.dark-mode .status-badge.pending  {background:#3a2e00;color:#f0c040;}
body.dark-mode .status-badge.completed{background:#1a3327;color:#6fcf97;}
body.dark-mode .status-badge.cancelled{background:#3b1f1f;color:#f5a5a5;}

/* ── Payment badge ── */
.pay-badge{padding:3px 9px;border-radius:20px;font-size:0.7rem;font-weight:600;background:#e8f0fe;color:#3498db;}
body.dark-mode .pay-badge{background:#1a2a3a;color:#7fb3e8;}

/* ── Action area ── */
.order-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.status-select{
    padding:5px 10px;border-radius:7px;
    border:1.5px solid #ddd;font-size:0.8rem;
    background:#fafafa;color:#333;outline:none;cursor:pointer;
    transition:border-color 0.2s;
}
.status-select:focus{border-color:#28a745;}
body.dark-mode .status-select{background:#2a2a2a;border-color:#444;color:#eee;}
.btn-save{
    padding:5px 13px;background:#28a745;color:#fff;
    border:none;border-radius:7px;font-size:0.78rem;font-weight:600;
    cursor:pointer;transition:background 0.2s;display:inline-flex;align-items:center;gap:4px;
}
.btn-save:hover{background:#218838;}
.btn-delete{
    padding:5px 11px;background:#e74c3c;color:#fff;
    border-radius:7px;text-decoration:none;font-size:0.78rem;font-weight:600;
    transition:background 0.2s;display:inline-flex;align-items:center;gap:4px;
}
.btn-delete:hover{background:#c0392b;}
.btn-view{
    padding:5px 11px;background:#3498db;color:#fff;
    border-radius:7px;text-decoration:none;font-size:0.78rem;font-weight:600;
    cursor:pointer;border:none;transition:background 0.2s;display:inline-flex;align-items:center;gap:4px;
}
.btn-view:hover{background:#2980b9;}

/* ── Order items modal ── */
.modal-overlay{
    display:none;position:fixed;inset:0;
    background:rgba(0,0,0,0.55);z-index:2000;
    align-items:center;justify-content:center;
}
.modal-overlay.open{display:flex;}
.modal-box{
    background:#fff;border-radius:16px;
    padding:0;width:100%;max-width:540px;
    max-height:80vh;overflow:hidden;
    display:flex;flex-direction:column;
    animation:fadeCard 0.3s ease both;
    box-shadow:0 16px 48px rgba(0,0,0,0.2);
}
body.dark-mode .modal-box{background:#1e1e1e;}
.modal-header{
    padding:16px 22px;border-bottom:1.5px solid #f0f0f0;
    display:flex;align-items:center;justify-content:space-between;
}
body.dark-mode .modal-header{border-color:#2a2a2a;}
.modal-header h3{margin:0;font-size:1rem;font-weight:700;color:#1a1a1a;display:flex;align-items:center;gap:8px;}
body.dark-mode .modal-header h3{color:#f0f0f0;}
.modal-header h3 i{color:#28a745;}
.modal-close{background:none;border:none;font-size:1.2rem;color:#aaa;cursor:pointer;padding:4px;}
.modal-close:hover{color:#e74c3c;}
.modal-body{padding:20px 22px;overflow-y:auto;}
.modal-customer{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:18px;}
.modal-field{font-size:0.83rem;color:#555;}
body.dark-mode .modal-field{color:#aaa;}
.modal-field strong{display:block;font-size:0.78rem;color:#aaa;font-weight:600;margin-bottom:2px;text-transform:uppercase;letter-spacing:0.3px;}
.modal-items-title{font-size:0.82rem;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:0.4px;margin-bottom:10px;}
body.dark-mode .modal-items-title{color:#888;}
.modal-item{
    display:flex;justify-content:space-between;align-items:center;
    padding:9px 0;border-bottom:1px solid #f5f5f5;font-size:0.87rem;
}
body.dark-mode .modal-item{border-color:#2a2a2a;}
.modal-item:last-child{border-bottom:none;}
.modal-item-name{color:#222;flex:1;}
body.dark-mode .modal-item-name{color:#eee;}
.modal-item-qty{color:#aaa;font-size:0.78rem;margin:0 10px;}
.modal-item-price{font-weight:700;color:#28a745;}
.modal-total{
    display:flex;justify-content:space-between;
    font-size:1rem;font-weight:700;color:#1a1a1a;
    border-top:2px solid #f0f0f0;margin-top:10px;padding-top:12px;
}
body.dark-mode .modal-total{color:#f0f0f0;border-color:#2a2a2a;}
.modal-total span:last-child{color:#28a745;}

/* ── Alert message ── */
.alert{padding:11px 16px;border-radius:8px;margin-bottom:18px;font-size:0.9rem;display:flex;align-items:center;gap:8px;}
.alert.success{background:#eafaf1;color:#1e7e34;border:1px solid #b2dfdb;}
body.dark-mode .alert.success{background:#1a3327;border-color:#2d6a4f;color:#6fcf97;}

/* ── Empty state ── */
.empty-state{text-align:center;padding:50px 20px;color:#aaa;}
.empty-state i{font-size:3rem;display:block;margin-bottom:12px;color:#ccc;}
body.dark-mode .empty-state i{color:#444;}

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
body.dark-mode #mode-toggle{background:#1a1a1a;color:#28a745;border-color:#28a745;}
body.dark-mode #mode-toggle:hover{background:#28a745;color:#1a1a1a;}

/* ── Footer ── */
.main-footer{text-align:center;padding:12px 10px;font-size:13px;flex-shrink:0;transition:background 0.3s,color 0.3s;}
body.light-mode .main-footer{background:#f0f0f0;color:#555;}
body.dark-mode  .main-footer{background:#1a1a1a;color:#aaa;}

@media(max-width:700px){
    .admin-wrap{padding:20px 12px 70px;}
    .modal-customer{grid-template-columns:1fr;}
    .order-actions{flex-direction:column;align-items:flex-start;}
}
</style>
</head>
<body class="light-mode">
<script>(function(){if(localStorage.getItem('mode')==='dark'){document.body.classList.remove('light-mode');document.body.classList.add('dark-mode');}})();</script>

<?php include("../includes/header.php"); ?>

<div class="page-outer">
<div class="admin-wrap">

    <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h2 class="page-title"><i class="fa-solid fa-receipt"></i> Orders</h2>
    <p class="page-sub">Manage and update all customer orders</p>

    <?php if(isset($_GET['updated'])): ?>
        <div class="alert success"><i class="fa-solid fa-circle-check"></i> Order status updated successfully.</div>
    <?php endif; ?>
    <?php if(isset($_GET['deleted'])): ?>
        <div class="alert success"><i class="fa-solid fa-circle-check"></i> Order deleted successfully.</div>
    <?php endif; ?>

    <!-- ── Stats ── -->
    <div class="stats-grid">
        <div class="stat-card" style="animation-delay:0s">
            <div class="stat-icon blue"><i class="fa-solid fa-receipt"></i></div>
            <div class="stat-info"><h3><?= $totalOrders ?></h3><p>Total Orders</p></div>
        </div>
        <div class="stat-card" style="animation-delay:0.07s">
            <div class="stat-icon orange"><i class="fa-solid fa-clock"></i></div>
            <div class="stat-info"><h3><?= $pendingOrders ?></h3><p>Pending</p></div>
        </div>
        <div class="stat-card" style="animation-delay:0.14s">
            <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-info"><h3><?= $completedOrders ?></h3><p>Completed</p></div>
        </div>
        <div class="stat-card" style="animation-delay:0.21s">
            <div class="stat-icon purple"><i class="fa-solid fa-coins"></i></div>
            <div class="stat-info"><h3>LKR <?= number_format((float)$totalRevenue, 0) ?></h3><p>Total Revenue</p></div>
        </div>
    </div>

    <!-- ── Filter Bar ── -->
    <div class="filter-bar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Search by name or order #…" oninput="filterOrders()">
        </div>
        <div class="status-tabs">
            <button class="tab-btn <?= $statusFilter==='all'?'active':'' ?>"       onclick="setStatus('all',this)">All</button>
            <button class="tab-btn pending-tab   <?= $statusFilter==='Pending'?'active':'' ?>"   onclick="setStatus('Pending',this)">Pending</button>
            <button class="tab-btn completed-tab <?= $statusFilter==='Completed'?'active':'' ?>" onclick="setStatus('Completed',this)">Completed</button>
            <button class="tab-btn cancelled-tab <?= $statusFilter==='Cancelled'?'active':'' ?>" onclick="setStatus('Cancelled',this)">Cancelled</button>
        </div>
        <span class="results-count" id="resultsCount"></span>
    </div>

    <!-- ── Orders Table ── -->
    <div class="section-card">
        <div class="section-card-header">
            <i class="fa-solid fa-list"></i>
            <h3>All Orders</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="ordersBody">
            <?php if($orders && $orders->num_rows > 0):
                while($o = $orders->fetch_assoc()):
                    $statusLower = strtolower($o['status']);
            ?>
            <tr class="order-row"
                data-name="<?= strtolower(htmlspecialchars($o['full_name'])) ?>"
                data-user="<?= strtolower(htmlspecialchars($o['uname'])) ?>"
                data-id="<?= $o['id'] ?>"
                data-status="<?= $o['status'] ?>">

                <td><strong>#<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></strong></td>
                <td>
                    <div style="font-weight:600;color:#1a1a1a;"><?= htmlspecialchars($o['full_name']) ?></div>
                    <div style="font-size:0.76rem;color:#aaa;"><?= htmlspecialchars($o['uemail']) ?></div>
                </td>
                <td><?= htmlspecialchars($o['phone']) ?></td>
                <td><?= htmlspecialchars($o['city']) ?></td>
                <td><strong>LKR <?= number_format($o['total_amount'], 0) ?></strong></td>
                <td><span class="pay-badge"><?= htmlspecialchars($o['payment_method']) ?></span></td>
                <td><span class="status-badge <?= $statusLower ?>"><?= $o['status'] ?></span></td>
                <td style="white-space:nowrap;"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                <td>
                    <div class="order-actions">
                        <!-- View items -->
                        <button class="btn-view" onclick="viewOrder(<?= $o['id'] ?>)">
                            <i class="fa-solid fa-eye"></i> Items
                        </button>
                        <!-- Update status -->
                        <form method="POST" style="display:flex;gap:6px;align-items:center;">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="status" class="status-select">
                                <option value="Pending"   <?= $o['status']==='Pending'  ?'selected':'' ?>>Pending</option>
                                <option value="Completed" <?= $o['status']==='Completed'?'selected':'' ?>>Completed</option>
                                <option value="Cancelled" <?= $o['status']==='Cancelled'?'selected':'' ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="btn-save">
                                <i class="fa-solid fa-floppy-disk"></i>
                            </button>
                        </form>
                        <!-- Delete -->
                        <a href="orders.php?delete=<?= $o['id'] ?>" class="btn-delete"
                           onclick="return confirm('Delete order #<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?>?')">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="9">
                    <div class="empty-state">
                        <i class="fa-solid fa-box-open"></i>
                        No orders found.
                    </div>
                </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div><!-- end admin-wrap -->

<!-- ── Order Items Modal ── -->
<div class="modal-overlay" id="orderModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fa-solid fa-receipt"></i> <span id="modalTitle">Order Details</span></h3>
            <button class="modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="modalBody">
            <p style="text-align:center;color:#aaa;">Loading…</p>
        </div>
    </div>
</div>

<!-- Mode toggle — bottom-right, same as login page -->
<div class="mode-toggle-container">
    <button id="mode-toggle" title="Toggle Light/Dark Mode">
        <i class="fa-solid fa-moon"></i>
    </button>
</div>
</div><!-- end page-outer -->

<?php include("../includes/footer.php"); ?>

<!-- Order items data embedded as JSON for the modal -->
<script>
var ordersData = {
<?php
// Re-query to get items for each order (for JS modal)
$allOrders = $conn->query("SELECT o.id, o.full_name, o.phone, o.address, o.city, o.postal, o.payment_method, o.total_amount, o.status FROM orders o ORDER BY o.created_at DESC");
if($allOrders && $allOrders->num_rows > 0){
    $rows = [];
    while($o = $allOrders->fetch_assoc()){
        $items = [];
        $itemsRes = $conn->query("SELECT * FROM order_items WHERE order_id=".$o['id']);
        if($itemsRes) while($it = $itemsRes->fetch_assoc()) $items[] = $it;
        $o['items'] = $items;
        echo $o['id'].': '.json_encode($o).','."\n";
    }
}
?>
};

function viewOrder(id){
    var o = ordersData[id];
    if(!o){ return; }
    document.getElementById('modalTitle').textContent = 'Order #' + String(id).padStart(5,'0');
    var html = '<div class="modal-customer">';
    html += '<div class="modal-field"><strong>Customer</strong>'+escHtml(o.full_name)+'</div>';
    html += '<div class="modal-field"><strong>Phone</strong>'+escHtml(o.phone)+'</div>';
    html += '<div class="modal-field"><strong>Address</strong>'+escHtml(o.address)+'</div>';
    html += '<div class="modal-field"><strong>City</strong>'+escHtml(o.city+(o.postal?' - '+o.postal:''))+'</div>';
    html += '<div class="modal-field"><strong>Payment</strong>'+escHtml(o.payment_method)+'</div>';
    html += '<div class="modal-field"><strong>Status</strong><span class="status-badge '+o.status.toLowerCase()+'">'+escHtml(o.status)+'</span></div>';
    html += '</div>';
    html += '<div class="modal-items-title">Ordered Items</div>';
    if(o.items && o.items.length > 0){
        o.items.forEach(function(it){
            var sub = (parseFloat(it.price) * parseInt(it.quantity));
            html += '<div class="modal-item">';
            html += '<span class="modal-item-name">'+escHtml(it.product_name)+'</span>';
            html += '<span class="modal-item-qty">×'+it.quantity+'</span>';
            html += '<span class="modal-item-price">LKR '+formatNum(sub)+'</span>';
            html += '</div>';
        });
    } else {
        html += '<p style="color:#aaa;font-size:0.88rem;">No items found.</p>';
    }
    html += '<div class="modal-total"><span>Total</span><span>LKR '+formatNum(parseFloat(o.total_amount))+'</span></div>';
    document.getElementById('modalBody').innerHTML = html;
    document.getElementById('orderModal').classList.add('open');
}

function closeModal(){
    document.getElementById('orderModal').classList.remove('open');
}
// Close on overlay click
document.getElementById('orderModal').addEventListener('click', function(e){
    if(e.target === this) closeModal();
});

function escHtml(str){ var d=document.createElement('div');d.appendChild(document.createTextNode(str||''));return d.innerHTML; }
function formatNum(n){ return Math.round(n).toLocaleString(); }

/* ── Search & Status filter ── */
var activeStatus = '<?= $statusFilter ?>';

function setStatus(s, btn){
    activeStatus = s;
    document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    filterOrders();
}

function filterOrders(){
    var q   = document.getElementById('searchInput').value.toLowerCase().trim();
    var rows = document.querySelectorAll('.order-row');
    var visible = 0;
    rows.forEach(function(row){
        var name   = row.getAttribute('data-name') || '';
        var user   = row.getAttribute('data-user') || '';
        var id     = row.getAttribute('data-id')   || '';
        var status = row.getAttribute('data-status')|| '';
        var matchSearch = !q || name.includes(q) || user.includes(q) || id.includes(q);
        var matchStatus = activeStatus === 'all' || status === activeStatus;
        if(matchSearch && matchStatus){ row.style.display=''; visible++; }
        else { row.style.display='none'; }
    });
    document.getElementById('resultsCount').textContent = visible + ' order' + (visible!==1?'s':'');
}

filterOrders();

/* ── Dark / Light Mode ── */
(function(){
    var b = document.getElementById('mode-toggle');
    var i = b.querySelector('i');
    if(document.body.classList.contains('dark-mode')){
        i.classList.remove('fa-moon'); i.classList.add('fa-sun');
    }
    b.addEventListener('click', function(){
        var d = document.body.classList.contains('dark-mode');
        if(d){
            document.body.classList.remove('dark-mode'); document.body.classList.add('light-mode');
            i.classList.remove('fa-sun'); i.classList.add('fa-moon');
            localStorage.setItem('mode','light');
        } else {
            document.body.classList.remove('light-mode'); document.body.classList.add('dark-mode');
            i.classList.remove('fa-moon'); i.classList.add('fa-sun');
            localStorage.setItem('mode','dark');
        }
    });
})();
</script>
</body>
</html>