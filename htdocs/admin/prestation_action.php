<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/prestations.php';
require_once __DIR__ . '/../includes/images.php';
require_admin();

// Actions sur une prestation ou ses photos : uniquement par formulaire POST protégé
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}
csrf_check();

$pdo = getPDO();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$photoId = filter_input(INPUT_POST, 'photo', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';
$p = $id ? prestation_par_id($pdo, $id) : false;

if (!$p) {
    header('Location: prestations.php');
    exit;
}

// La photo doit appartenir à cette prestation
$photo = null;
foreach ($p['photos'] as $ph) {
    if ((int) $ph['id'] === (int) $photoId) {
        $photo = $ph;
    }
}

switch ($action) {
    case 'toggle':
        prestations_query($pdo, "UPDATE prestations SET actif = 1 - actif WHERE id = ?", [$id]);
        $_SESSION['flash'] = $p['actif']
            ? "« {$p['nom']} » est masquée : elle n'apparaît plus sur le site."
            : "« {$p['nom']} » est de nouveau visible sur le site.";
        header('Location: prestations.php');
        exit;

    case 'delete':
        foreach ($p['photos'] as $ph) {
            image_supprimer($ph['fichier'], 'prestations');
        }
        prestations_query($pdo, "DELETE FROM prestations WHERE id = ?", [$id]); // les lignes de photos partent avec (ON DELETE CASCADE)
        $_SESSION['flash'] = "« {$p['nom']} » a été supprimée" . ($p['photos'] ? ', avec ses photos.' : '.');
        header('Location: prestations.php');
        exit;

    case 'photo_principale':
        if ($photo) {
            // La photo choisie passe en tête, les autres gardent leur ordre
            $ordre = 1;
            foreach ($p['photos'] as $ph) {
                $nouvel = (int) $ph['id'] === (int) $photo['id'] ? 0 : $ordre++;
                prestations_query($pdo, "UPDATE prestation_photos SET ordre = ? WHERE id = ?", [$nouvel, $ph['id']]);
            }
            $_SESSION['flash'] = 'Photo principale changée.';
        }
        break;

    case 'photo_supprimer':
        if ($photo) {
            prestations_query($pdo, "DELETE FROM prestation_photos WHERE id = ? AND prestation_id = ?", [$photo['id'], $id]);
            image_supprimer($photo['fichier'], 'prestations');
            $_SESSION['flash'] = 'Photo supprimée.';
        }
        break;
}

header('Location: prestation_edit.php?id=' . (int) $id);
exit;
