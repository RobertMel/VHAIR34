<?php

require_once __DIR__ . '/../config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT id, password_hash FROM admins WHERE username = ?');
    $stmt->execute([$u]);
    $row = $stmt->fetch();
    if ($row && password_verify($p, $row['password_hash'])) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_user'] = $u;
        header('Location: /admin/dashboard.php');
        exit;
    } else {
        $error = 'Identifiants incorrects';
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Admin login</title></head><body>
<h1>Admin - Connexion</h1>
<?php if(!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post">
  <input name="username" placeholder="Utilisateur" required>
  <input name="password" type="password" placeholder="Mot de passe" required>
  <button>Connexion</button>
</form>
</body></html>
