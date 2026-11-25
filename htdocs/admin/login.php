<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';

if (($_SESSION['admin_logged'] ?? false) === true) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = null;
$u = '';

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
    $error = 'Identifiant ou mot de passe incorrect.';
}
$v = @filemtime(__DIR__ . '/../assets/admin.css') ?: 1;
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#F4EBE2">
<title>Connexion — Admin V.Hair 34</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Italiana&amp;family=Pinyon+Script&amp;family=Jost:wght@400;500&amp;display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin.css?v=<?= (int) $v ?>">
</head>
<body>
<main class="login">
  <form method="post" class="carte login-boite">
    <div class="login-logo">
      <div class="v" aria-hidden="true">V</div>
      <div class="mot">V.HAIR 34</div>
      <p>Espace admin</p>
    </div>
    <?php if ($error): ?><p class="a-msg a-msg-err" role="alert" style="margin: 0"><?= e($error) ?></p><?php endif; ?>
    <?= csrf_field() ?>
    <div class="champ">
      <label for="username">Identifiant</label>
      <input type="text" id="username" name="username" autocomplete="username" value="<?= e($u) ?>" required>
    </div>
    <div class="champ">
      <label for="password">Mot de passe</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required>
    </div>
    <button class="b b-plein b-grand">Se connecter</button>
    <a href="/" style="text-align: center; font-size: 15px">Retour au site</a>
  </form>
</main>
</body>
</html>
