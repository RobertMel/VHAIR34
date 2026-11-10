<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_logged'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? '';
$pdo = getPDO();

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM creneaux WHERE id=?");
    $stmt->execute([$id]);
}

header('Location: creneaux.php');
exit;
