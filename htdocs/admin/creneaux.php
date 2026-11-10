<?php
session_start();
require_once __DIR__ . '/../config.php';

// Vérification login admin
if (!isset($_SESSION['admin_logged'])) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();

// Récupérer tous les créneaux
$stmt = $pdo->query("SELECT * FROM creneaux ORDER BY date ASC, heure ASC");
$creneaux = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des créneaux</title>
<style>
body { font-family: Arial; padding:20px; background:#f9f9f9; }
h1 { margin-bottom:20px; }
table { border-collapse: collapse; width: 100%; background:white; }
th, td { border: 1px solid #ccc; padding: 8px; text-align:center; }
th { background: #eee; }
a.button { display:inline-block; padding:5px 10px; background:#007BFF; color:white; text-decoration:none; border-radius:4px; }
a.button:hover { background:#0056b3; }
.status-yes { color: green; font-weight:bold; }
.status-no { color: red; font-weight:bold; }
</style>
</head>
<body>
<h1>Liste des créneaux</h1>
<p><a href="creneaux_add.php" class="button">Ajouter un créneau</a></p>

<table>
<tr>
    <th>ID</th>
    <th>Date</th>
    <th>Heure</th>
    <th>Disponible</th>
    <th>Client</th>
    <th>Téléphone</th>
    <th>Actions</th>
</tr>

<?php foreach($creneaux as $c): ?>
<tr>
    <td><?= $c['id'] ?></td>
    <td><?= $c['date'] ?></td>
    <td><?= $c['heure'] ?></td>
    <td class="<?= $c['disponible'] ? 'status-yes' : 'status-no' ?>">
        <?= $c['disponible'] ? 'Oui' : 'Non' ?>
    </td>
    <td><?= htmlspecialchars($c['nom_client'] ?? '-') ?></td>
    <td><?= htmlspecialchars($c['telephone'] ?? '-') ?></td>
    <td>
        <a href="creneaux_edit.php?id=<?= $c['id'] ?>" class="button">Éditer</a>
        <a href="creneaux_delete.php?id=<?= $c['id'] ?>" class="button" onclick="return confirm('Supprimer ce créneau ?')">Supprimer</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<p><a href="dashboard.php">Retour au dashboard</a></p>
</body>
</html>
