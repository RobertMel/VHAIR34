<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_admin();

$pdo = getPDO();

// Récupérer tous les créneaux
$stmt = $pdo->query("SELECT * FROM creneaux ORDER BY date ASC, heure ASC");
$creneaux = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="robots" content="noindex">
<title>Dashboard Admin</title>
<link rel="stylesheet" href="/assets/style.css?v=<?=time()?>">
<style>
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
th { background: #ffd700; color: black; }
tr:nth-child(even) { background: #fff8dc; }
</style>
</head>
<body>
<div class="container">
    <h1>Dashboard Admin</h1>
    <p>Bienvenue, <?= e($_SESSION['admin_user'] ?? '') ?> | <a href="logout.php">Déconnexion</a></p>

    <!-- Accès à la gestion des créneaux et des prestations -->
    <p style="display:flex; flex-wrap:wrap; gap:10px;">
        <a href="creneaux.php" class="button" style="background:black; color:gold; padding:10px 15px; border-radius:8px; text-decoration:none;">Gérer les créneaux</a>
        <a href="prestations.php" class="button" style="background:black; color:gold; padding:10px 15px; border-radius:8px; text-decoration:none;">Gérer les prestations</a>
    </p>

    <h2>Liste des créneaux et réservations</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Heure</th>
                <th>Disponible</th>
                <th>Client</th>
                <th>Téléphone</th>
                <th>Prestation</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($creneaux as $c): ?>
            <tr>
                <td><?= e($c['id']) ?></td>
                <td><?= e($c['date']) ?></td>
                <td><?= e($c['heure']) ?></td>
                <td><?= $c['disponible'] ? 'Oui' : 'Réservé' ?></td>
                <td><?= e($c['nom_client'] ?? '-') ?></td>
                <td><?= e($c['telephone'] ?? '-') ?></td>
                <td><?= e($c['prestation'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
