<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_admin();

$error = '';

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
<title>Ajouter un créneau</title>
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
<h1>Ajouter un créneau</h1>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

<form method="post">
    <?= csrf_field() ?>
    <label>Date :</label><br>
    <input type="date" name="date" required><br>
    <label>Heure :</label><br>
    <input type="time" name="heure" required><br>
    <label>
        <input type="checkbox" name="disponible" checked> Disponible
    </label><br><br>
    <button>Ajouter</button>
</form>

<p><a href="creneaux.php">Retour à la liste</a></p>
</body>
</html>
