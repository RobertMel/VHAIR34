<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
require_auth();
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || empty($data['date']) || empty($data['heure'])) {
    http_response_code(400); echo json_encode(['error'=>'Missing date or heure']); exit;
}
$pdo = getPDO();
$stmt = $pdo->prepare('INSERT INTO creneaux (date, heure, disponible) VALUES (?, ?, ?)');
$stmt->execute([$data['date'], $data['heure'], isset($data['disponible']) ? (int)$data['disponible'] : 1]);
echo json_encode(['success'=>true, 'id'=>$pdo->lastInsertId()]);
