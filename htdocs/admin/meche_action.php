<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/meches.php';
require_once __DIR__ . '/../includes/images.php';
require_admin();

// Actions sur une mèche ou ses photos : uniquement par formulaire POST protégé
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}
csrf_check();

$pdo = getPDO();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$photoId = filter_input(INPUT_POST, 'photo', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';
$m = $id ? meche_par_id($pdo, $id) : false;

if (!$m) {
    header('Location: meches.php');
    exit;
}

// La photo doit appartenir à cette mèche
$photo = null;
foreach ($m['photos'] as $ph) {
    if ((int) $ph['id'] === (int) $photoId) {
        $photo = $ph;
    }
}

switch ($action) {
    case 'toggle':
        meches_query($pdo, "UPDATE meches SET actif = 1 - actif WHERE id = ?", [$id]);
        $_SESSION['flash'] = $m['actif']
            ? "« {$m['nom']} » est masquée : elle n'apparaît plus sur le site."
            : "« {$m['nom']} » est de nouveau visible sur le site.";
        header('Location: meches.php');
        exit;

    case 'delete':
        foreach ($m['photos'] as $ph) {
            image_supprimer($ph['fichier']);
        }
        meches_query($pdo, "DELETE FROM meches WHERE id = ?", [$id]); // les lignes de photos partent avec (ON DELETE CASCADE)
        $_SESSION['flash'] = "« {$m['nom']} » et ses photos ont été supprimées.";
        header('Location: meches.php');
        exit;

    case 'photo_principale':
        if ($photo) {
            // La photo choisie passe en tête, les autres gardent leur ordre
            $ordre = 1;
            foreach ($m['photos'] as $ph) {
                $nouvel = (int) $ph['id'] === (int) $photo['id'] ? 0 : $ordre++;
                meches_query($pdo, "UPDATE meche_photos SET ordre = ? WHERE id = ?", [$nouvel, $ph['id']]);
            }
            $_SESSION['flash'] = 'Photo principale changée.';
        }
        break;

    case 'photo_supprimer':
        if ($photo) {
            meches_query($pdo, "DELETE FROM meche_photos WHERE id = ? AND meche_id = ?", [$photo['id'], $id]);
            image_supprimer($photo['fichier']);
            $_SESSION['flash'] = 'Photo supprimée.';
        }
        break;
}

header('Location: meche_edit.php?id=' . (int) $id);
exit;
