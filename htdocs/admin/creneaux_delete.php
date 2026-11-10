<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_admin();

// Suppression uniquement par formulaire POST protégé : un simple lien ne peut plus supprimer un créneau
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}
csrf_check();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id) {
    $stmt = getPDO()->prepare("DELETE FROM creneaux WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: creneaux.php');
exit;
