<?php
require_once __DIR__ . '/../config.php';
session_start();

// Vérification si admin connecté
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
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
    <p>Bienvenue, <?= $_SESSION['admin_user'] ?> | <a href="logout.php">Déconnexion</a></p>

    <!-- Bouton Ajouter un créneau -->
    <p>
        <a href="creneaux.php" class="button" style="background:black; color:gold; padding:10px 15px; border-radius:8px; text-decoration:none;">Ajouter un créneau</a>
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
                <td><?= $c['id'] ?></td>
                <td><?= $c['date'] ?></td>
                <td><?= $c['heure'] ?></td>
                <td><?= $c['disponible'] ? 'Oui' : 'Réservé' ?></td>
                <td><?= $c['nom_client'] ?? '-' ?></td>
                <td><?= $c['telephone'] ?? '-' ?></td>
                <td><?= $c['prestation'] ?? '-' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
