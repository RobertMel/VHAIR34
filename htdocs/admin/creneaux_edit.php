<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>


<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_logged'])) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();
$error = '';
$id = $_GET['id'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM creneaux WHERE id=?");
$stmt->execute([$id]);
$creneau = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$creneau) {
    die("Créneau introuvable");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $heure = $_POST['heure'] ?? '';
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    if ($date && $heure) {
        $stmt = $pdo->prepare("UPDATE creneaux SET date=?, heure=?, disponible=? WHERE id=?");
        $stmt->execute([$date, $heure, $disponible, $id]);
        header('Location: creneaux.php');
        exit;
    } else {
        $error = 'Veuillez remplir la date et l’heure';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
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
<?php if($error) echo "<p class='error'>$error</p>"; ?>

<form method="post">
    <label>Date :</label><br>
    <input type="date" name="date" value="<?= $creneau['date'] ?>" required><br>
    <label>Heure :</label><br>
    <input type="time" name="heure" value="<?= $creneau['heure'] ?>" required><br>
    <label>
        <input type="checkbox" name="disponible" <?= $creneau['disponible'] ? 'checked' : '' ?>> Disponible
    </label><br><br>
    <button>Modifier</button>
</form>

<p><a href="creneaux.php">Retour à la liste</a></p>
</body>
</html>
