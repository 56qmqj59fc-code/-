<?php
session_start();
require '../db.php';
require 'check_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF Attack blocked");
    }

    $id = (int)$_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: admin_panel.php");
    exit;
}
