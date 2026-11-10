<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || empty($data['creneau_id']) || empty($data['nom']) || empty($data['email'])) {
    http_response_code(400); echo json_encode(['error'=>'Missing fields']); exit;
}
$pdo = getPDO();
$pdo->beginTransaction();
$stmt = $pdo->prepare('SELECT disponible FROM creneaux WHERE id = ? FOR UPDATE');
$stmt->execute([$data['creneau_id']]);
$cr = $stmt->fetch();
if (!$cr) { $pdo->rollBack(); http_response_code(404); echo json_encode(['error'=>'Creneau not found']); exit; }
if ((int)$cr['disponible'] === 0) { $pdo->rollBack(); http_response_code(409); echo json_encode(['error'=>'Creneau already reserved']); exit; }
$stmt = $pdo->prepare('INSERT INTO reservations (creneau_id, nom, email) VALUES (?, ?, ?)');
$stmt->execute([$data['creneau_id'], $data['nom'], $data['email']]);
$stmt = $pdo->prepare('UPDATE creneaux SET disponible = 0 WHERE id = ?');
$stmt->execute([$data['creneau_id']]);
$pdo->commit();
echo json_encode(['success'=>true, 'reservation_id'=>$pdo->lastInsertId()]);
