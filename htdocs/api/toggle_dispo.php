<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
require_auth();
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['id'])) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }
$pdo = getPDO();
$stmt = $pdo->prepare('UPDATE creneaux SET disponible = NOT disponible WHERE id = ?');
$stmt->execute([$data['id']]);
echo json_encode(['success'=>true]);
