<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';

if (($_SESSION['admin_logged'] ?? false) === true) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $u = trim((string) ($_POST['username'] ?? ''));
    $p = (string) ($_POST['password'] ?? '');

    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT id, password_hash FROM admins WHERE username = ?');
    $stmt->execute([$u]);
    $row = $stmt->fetch();

    if ($row && password_verify($p, $row['password_hash'])) {
        // Nouvel identifiant de session à la connexion (empêche la fixation de session)
        session_regenerate_id(true);
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_user'] = $u;
        header('Location: /admin/dashboard.php');
        exit;
    }

    // Ralentit les tentatives de mot de passe en série
    sleep(1);
    $error = 'Identifiants incorrects';
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Admin - Connexion</title>
</head>
<body>
<h1>Admin - Connexion</h1>
<?php if ($error): ?><p style="color:red;"><?= e($error) ?></p><?php endif; ?>
<form method="post">
  <?= csrf_field() ?>
  <input name="username" placeholder="Utilisateur" autocomplete="username" required>
  <input name="password" type="password" placeholder="Mot de passe" autocomplete="current-password" required>
  <button>Connexion</button>
</form>
</body>
</html>
