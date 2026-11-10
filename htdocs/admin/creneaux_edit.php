<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
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
        header('Location: creneaux.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="robots" content="noindex">
<title>Modifier un créneau</title>
<style>
body { font-family: Arial; padding:20px; background:#f9f9f9; }
h1 { margin-bottom:20px; }
input[type=text], input[type=date], input[type=time] { padding:5px; width:200px; margin-bottom:10px; }
button { padding:5px 10px; background:#007BFF; color:white; border:none; border-radius:4px; cursor:pointer; }
button:hover { background:#0056b3; }
.error { color:red; margin-bottom:10px; }
</style>
</head>
<body>
<h1>Modifier le créneau</h1>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

<form method="post">
    <?= csrf_field() ?>
    <label>Date :</label><br>
    <input type="date" name="date" value="<?= e($creneau['date']) ?>" required><br>
    <label>Heure :</label><br>
    <input type="time" name="heure" value="<?= e(substr((string) $creneau['heure'], 0, 5)) ?>" required><br>
    <label>
        <input type="checkbox" name="disponible" <?= $creneau['disponible'] ? 'checked' : '' ?>> Disponible
    </label><br><br>
    <button>Modifier</button>
</form>

<p><a href="creneaux.php">Retour à la liste</a></p>
</body>
</html>
