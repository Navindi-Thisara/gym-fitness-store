<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){ header("Location: ../auth/login.php"); exit; }
include("../config/db.php");
$id = intval($_GET['id'] ?? 0);
if($id){
    $row = $conn->query("SELECT image FROM products WHERE id=$id")->fetch_assoc();
    if($row && !empty($row['image']) && file_exists("../assets/images/".$row['image'])){
        unlink("../assets/images/".$row['image']);
    }
    $conn->query("DELETE FROM products WHERE id=$id");
}
header("Location: dashboard.php"); exit;