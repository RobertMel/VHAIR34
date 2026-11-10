<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/prestations.php';
require_admin();

// Masquer/afficher ou supprimer une prestation : uniquement par formulaire POST protégé
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}
csrf_check();

$pdo = getPDO();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';
$p = $id ? prestation_par_id($pdo, $id) : false;

if ($p && $action === 'toggle') {
    prestations_query($pdo, "UPDATE prestations SET actif = 1 - actif WHERE id = ?", [$id]);
    $_SESSION['flash'] = $p['actif']
        ? "« {$p['nom']} » est masquée : elle n'apparaît plus sur le site."
        : "« {$p['nom']} » est de nouveau visible sur le site.";
} elseif ($p && $action === 'delete') {
    prestations_query($pdo, "DELETE FROM prestations WHERE id = ?", [$id]);
    $_SESSION['flash'] = "« {$p['nom']} » a été supprimée.";
}

header('Location: prestations.php');
exit;
