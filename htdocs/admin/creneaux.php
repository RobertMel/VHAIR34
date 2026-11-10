<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_admin();

$pdo = getPDO();
$creneaux = $pdo->query("SELECT * FROM creneaux ORDER BY date ASC, heure ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="robots" content="noindex">
<title>Gestion des créneaux</title>
<style>
body { font-family: Arial; padding:20px; background:#f9f9f9; }
h1 { margin-bottom:20px; }
table { border-collapse: collapse; width: 100%; background:white; }
th, td { border: 1px solid #ccc; padding: 8px; text-align:center; }
th { background: #eee; }
a.button, button.button { display:inline-block; padding:5px 10px; background:#007BFF; color:white; text-decoration:none; border:none; border-radius:4px; font:inherit; cursor:pointer; }
a.button:hover, button.button:hover { background:#0056b3; }
button.danger { background:#c0392b; }
button.danger:hover { background:#922b21; }
form.inline { display:inline; }
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

<?php foreach ($creneaux as $c): ?>
<tr>
    <td><?= (int) $c['id'] ?></td>
    <td><?= e($c['date']) ?></td>
    <td><?= e($c['heure']) ?></td>
    <td class="<?= $c['disponible'] ? 'status-yes' : 'status-no' ?>">
        <?= $c['disponible'] ? 'Oui' : 'Non' ?>
    </td>
    <td><?= e($c['nom_client'] ?? '-') ?></td>
    <td><?= e($c['telephone'] ?? '-') ?></td>
    <td>
        <a href="creneaux_edit.php?id=<?= (int) $c['id'] ?>" class="button">Éditer</a>
        <form method="post" action="creneaux_delete.php" class="inline" onsubmit="return confirm('Supprimer ce créneau ?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
            <button class="button danger">Supprimer</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>

<p><a href="dashboard.php">Retour au dashboard</a></p>
</body>
</html>
