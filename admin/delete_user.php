<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){ header("Location: ../auth/login.php"); exit; }
include("../config/db.php");
$id = intval($_GET['id'] ?? 0);
// Prevent deleting self
if($id && $id !== intval($_SESSION['user']['id'])){
    $conn->query("DELETE FROM users WHERE id=$id");
}
header("Location: users.php"); exit;