<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';

$pdo = getPDO();
$prestations = require __DIR__ . '/includes/prestations.php';

// Récupérer le créneau demandé
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit('Créneau invalide.');
}

$stmt = $pdo->prepare("SELECT id, date, heure, disponible FROM creneaux WHERE id = ?");
$stmt->execute([$id]);
$creneau = $stmt->fetch();

if (!$creneau) {
    http_response_code(404);
    exit('Créneau introuvable.');
}

$estPasse = strtotime($creneau['date'] . ' ' . $creneau['heure']) < time();
$reservable = (int) $creneau['disponible'] === 1 && !$estPasse;

$error = null;
$success = false;
$nom = $telephone = $prestation = $prestation_libre = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reservable) {
    csrf_check();

    $nom              = trim((string) ($_POST['nom'] ?? ''));
    $telephone        = trim((string) ($_POST['telephone'] ?? ''));
    $prestation       = trim((string) ($_POST['prestation'] ?? ''));
    $prestation_libre = trim((string) ($_POST['prestation_libre'] ?? ''));
    $final_prestation = $prestation_libre !== '' ? $prestation_libre : $prestation;
    $telephone_net    = preg_replace('/[\s.\-()]/', '', $telephone);

    if ($nom === '' || $telephone === '' || $final_prestation === '') {
        $error = "Merci de remplir ton nom, ton téléphone et la prestation.";
    } elseif (mb_strlen($nom) > 100) {
        $error = "Le nom est trop long.";
    } elseif (!preg_match('/^(\+|00)?\d{9,15}$/', $telephone_net)) {
        $error = "Le numéro de téléphone n'est pas valide.";
    } elseif (mb_strlen($final_prestation) > 255) {
        $error = "La description de la prestation est trop longue (255 caractères maximum).";
    } elseif ($prestation_libre === '' && !in_array($prestation, $prestations, true)) {
        $error = "Prestation inconnue, choisis-en une dans la liste.";
    } else {
        try {
            // La condition « disponible = 1 » garantit qu'on n'écrase jamais une réservation existante,
            // même si deux personnes valident le même créneau en même temps.
            $stmt = $pdo->prepare("UPDATE creneaux
                                   SET nom_client = ?, telephone = ?, prestation = ?, disponible = 0
                                   WHERE id = ? AND disponible = 1");
            $stmt->execute([$nom, $telephone_net, $final_prestation, $id]);

            if ($stmt->rowCount() === 1) {
                $success = true;
            } else {
                $reservable = false;
                $error = "Désolée, ce créneau vient d'être réservé par quelqu'un d'autre.";
            }
        } catch (PDOException $e) {
            error_log('Erreur réservation créneau ' . $id . ' : ' . $e->getMessage());
            $error = "Une erreur est survenue, la réservation n'a pas été enregistrée. Réessaie ou écris-moi sur Instagram.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Réserver le créneau - V.Hair_34</title>
<link rel="stylesheet" href="/assets/style.css?v=<?= time() ?>">
</head>
<body>
<div class="container">
    <h1>Réserver un créneau</h1>

    <?php if ($error): ?>
        <p class="error-message"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="success-overlay">
        <div class="success-pop-up fade-in">
            <div class="pop-up-icon">✅</div>
            <h2>Réservation confirmée !</h2>
            <p>Merci <strong><?= e($nom) ?></strong>, ton créneau est bien enregistré.</p>

            <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.2); margin: 20px 0;">

            <p><strong>⚠️ ÉTAPE IMPORTANTE :</strong></p>
            <p>Pour valider définitivement ton rendez-vous et discuter des détails, envoie-moi un message dès maintenant :</p>

            <a href="https://www.instagram.com/v.hair_34/" target="_blank" rel="noopener" class="insta-confirm-button">
                Envoyer un DM sur Instagram
            </a>

            <p style="font-size: 0.8em; margin-top: 15px; opacity: 0.8;">
                (Précise ton nom et la prestation choisie)
            </p>
        </div>
    </div>
    <?php endif; ?>

    <div class="service">
        <h2>Créneau sélectionné</h2>
        <p><strong>Date :</strong> <?= e(date_fr($creneau['date'])) ?></p>
        <p><strong>Heure :</strong> <?= e(heure_fr($creneau['heure'])) ?></p>
    </div>

    <?php if (!$reservable && !$success): ?>
        <div class="service">
            <p>Ce créneau n'est plus disponible.</p>
            <a href="/" class="button">Voir les autres créneaux</a>
        </div>
    <?php elseif (!$success): ?>
    <div class="service">
        <h2>Tes informations et ta prestation</h2>
        <form method="post" class="reservation-form">
            <?= csrf_field() ?>
            <input type="text" name="nom" placeholder="Nom" maxlength="100" value="<?= e($nom) ?>" required>
            <input type="tel" name="telephone" placeholder="Numéro de téléphone" maxlength="20" value="<?= e($telephone) ?>" required>

            <label for="prestation">Choisis ta prestation :</label>
            <select name="prestation" id="prestation">
                <option value="">Sélectionne ta prestation</option>
                <?php foreach ($prestations as $p): ?>
                    <option value="<?= e($p) ?>" <?= $p === $prestation ? 'selected' : '' ?>><?= e($p) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="prestation_libre">Ou autre prestation :</label>
            <input type="text" name="prestation_libre" id="prestation_libre" maxlength="255" placeholder="Décris ta prestation" value="<?= e($prestation_libre) ?>">

            <button type="submit" class="button">Réserver</button>
        </form>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
