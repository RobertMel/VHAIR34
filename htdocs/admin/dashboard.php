<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/meches.php';
require_admin();

$pdo = getPDO();
creneaux_colonnes_meches($pdo); // ajoute les colonnes du choix des mèches si besoin

// Récupérer tous les créneaux
$stmt = $pdo->query("SELECT * FROM creneaux ORDER BY date ASC, heure ASC");
$creneaux = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Dashboard Admin</title>
<link rel="stylesheet" href="/assets/style.css?v=<?=time()?>">
<style>
.table-wrap { overflow-x: auto; }
table { min-width: 760px; width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
th { background: #ffd700; color: black; }
tr:nth-child(even) { background: #fff8dc; }
</style>
</head>
<body>
<div class="container">
    <h1>Dashboard Admin</h1>
    <p>Bienvenue, <?= e($_SESSION['admin_user'] ?? '') ?> | <a href="logout.php">Déconnexion</a></p>

    <!-- Accès à la gestion des créneaux, des prestations et des mèches -->
    <p style="display:flex; flex-wrap:wrap; gap:10px;">
        <a href="creneaux.php" class="button" style="background:black; color:gold; padding:10px 15px; border-radius:8px; text-decoration:none;">Gérer les créneaux</a>
        <a href="prestations.php" class="button" style="background:black; color:gold; padding:10px 15px; border-radius:8px; text-decoration:none;">Gérer les prestations</a>
        <a href="meches.php" class="button" style="background:black; color:gold; padding:10px 15px; border-radius:8px; text-decoration:none;">Gérer les mèches</a>
    </p>

    <h2>Liste des créneaux et réservations</h2>
    <div class="table-wrap">
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
                <th>Mèches</th>
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
                <td><?= !empty($c['avec_meches']) ? e($c['meche'] ?? 'Avec mèches') : ($c['disponible'] ? '-' : 'Les siennes') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

</body>
</html>
