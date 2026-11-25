<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

$error = '';
$date = $heure = '';
$disponible = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $date = trim((string) ($_POST['date'] ?? ''));
    $heure = trim((string) ($_POST['heure'] ?? ''));
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    if (!is_valid_date($date) || !is_valid_time($heure)) {
        $error = 'Merci de saisir une date et une heure valides.';
    } else {
        $stmt = getPDO()->prepare("INSERT INTO creneaux (date, heure, disponible) VALUES (?, ?, ?)");
        $stmt->execute([$date, $heure, $disponible]);
        $_SESSION['flash'] = 'Créneau du ' . mb_strtolower(date_fr($date)) . ' à ' . heure_fr($heure) . ' ajouté.';
        header('Location: creneaux.php');
        exit;
    }
}

admin_debut('Ajouter un créneau', 'creneaux');
?>
<div class="a-titre"><h1>Ajouter un créneau</h1></div>
<?php admin_erreur($error); ?>

<form method="post" class="carte form">
  <?= csrf_field() ?>
  <div class="deux">
    <div class="champ">
      <label for="date">Date</label>
      <input type="date" id="date" name="date" value="<?= e($date) ?>" min="<?= date('Y-m-d') ?>" required>
    </div>
    <div class="champ">
      <label for="heure">Heure</label>
      <input type="time" id="heure" name="heure" value="<?= e($heure) ?>" step="300" required>
    </div>
  </div>
  <label class="case"><input type="checkbox" name="disponible" <?= $disponible ? 'checked' : '' ?>> Proposer ce créneau sur le site</label>
  <button class="b b-plein b-grand">Ajouter le créneau</button>
</form>

<a href="creneaux.php" class="a-retour">← Retour aux créneaux</a>
<?php
admin_fin();
