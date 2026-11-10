<?php
session_start();

// Si l’admin n’est pas connecté → redirection
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration</title>
</head>
<body>
    <h1>Bienvenue dans l'administration</h1>

    <p><a href="dashboard.php">Gérer les créneaux</a></p>
    <p><a href="logout.php">Se déconnecter</a></p>
</body>
</html>
