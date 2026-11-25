<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

$pdo = getPDO();
$error = '';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$creneau = false;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM creneaux WHERE id = ?");
    $stmt->execute([$id]);
    $creneau = $stmt->fetch();
}

if (!$creneau) {
    http_response_code(404);
    exit('Créneau introuvable');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $date = trim((string) ($_POST['date'] ?? ''));
    $heure = trim((string) ($_POST['heure'] ?? ''));
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    if (!is_valid_date($date) || !is_valid_time($heure)) {
        $error = 'Merci de saisir une date et une heure valides.';
    } else {
        $stmt = $pdo->prepare("UPDATE creneaux SET date = ?, heure = ?, disponible = ? WHERE id = ?");
        $stmt->execute([$date, $heure, $disponible, $id]);
        $_SESSION['flash'] = 'Créneau du ' . mb_strtolower(date_fr($date)) . ' à ' . heure_fr($heure) . ' modifié.';
        header('Location: creneaux.php');
        exit;
    }
}

admin_debut('Modifier un créneau', 'creneaux');
?>
<div class="a-titre"><h1>Modifier le créneau</h1></div>
<?php admin_erreur($error); ?>

<?php if (!$creneau['disponible'] && !empty($creneau['nom_client'])): ?>
  <p class="a-msg a-msg-ok" style="background: var(--lin); border-color: var(--ligne); color: var(--bordeaux)">
    Réservé par <strong><?= e($creneau['nom_client']) ?></strong><?= !empty($creneau['telephone']) ? ' (' . e($creneau['telephone']) . ')' : '' ?> pour <?= e($creneau['prestation'] ?? '') ?>.
    Si tu changes la date ou l’heure, préviens-la.
  </p>
<?php endif; ?>

<form method="post" class="carte form">
  <?= csrf_field() ?>
  <div class="deux">
    <div class="champ">
      <label for="date">Date</label>
      <input type="date" id="date" name="date" value="<?= e($creneau['date']) ?>" required>
    </div>
    <div class="champ">
      <label for="heure">Heure</label>
      <input type="time" id="heure" name="heure" value="<?= e(substr((string) $creneau['heure'], 0, 5)) ?>" step="300" required>
    </div>
  </div>
  <label class="case"><input type="checkbox" name="disponible" <?= $creneau['disponible'] ? 'checked' : '' ?>> Libre (proposé sur le site)</label>
  <button class="b b-plein b-grand">Enregistrer</button>
</form>

<a href="creneaux.php" class="a-retour">← Retour aux créneaux</a>
<?php
admin_fin();
