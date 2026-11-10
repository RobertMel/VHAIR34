<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT id, date, heure, disponible FROM creneaux ORDER BY date ASC, heure ASC");
$stmt->execute();
$rows = $stmt->fetchAll();
echo json_encode($rows);
