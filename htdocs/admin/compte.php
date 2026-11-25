<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/securite.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

$pdo = getPDO();

// Compte connecté (par son id ; repli sur l'identifiant pour les sessions ouvertes avant cette page)
if (!empty($_SESSION['admin_id'])) {
    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE id = ?');
    $stmt->execute([(int) $_SESSION['admin_id']]);
} else {
    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = ?');
    $stmt->execute([$_SESSION['admin_user'] ?? '']);
}
$compte = $stmt->fetch();
if (!$compte) {
    $_SESSION = [];
    header('Location: login.php');
    exit;
}

$error = '';
$identifiant = $compte['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $identifiant = trim((string) ($_POST['identifiant'] ?? ''));
    $actuel = (string) ($_POST['actuel'] ?? '');
    $nouveau = (string) ($_POST['nouveau'] ?? '');
    $confirmation = (string) ($_POST['confirmation'] ?? '');

    if (!password_verify($actuel, $compte['password_hash'])) {
        sleep(1);
        $error = 'Le mot de passe actuel est incorrect.';
    } elseif (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $identifiant)) {
        $error = 'L’identifiant doit faire de 3 à 50 caractères : lettres sans accent, chiffres, point, tiret ou tiret bas.';
    } elseif (strcasecmp($identifiant, 'admin') === 0 || strcasecmp($identifiant, 'administrateur') === 0) {
        $error = 'Choisis un identifiant moins évident que « admin ».';
    } elseif ($nouveau !== '' && $nouveau !== $confirmation) {
        $error = 'Les deux nouveaux mots de passe ne sont pas identiques.';
    } elseif ($nouveau !== '' && ($probleme = mot_de_passe_probleme($nouveau, $identifiant))) {
        $error = $probleme;
    } elseif ($nouveau !== '' && password_verify($nouveau, $compte['password_hash'])) {
        $error = 'Le nouveau mot de passe doit être différent de l’actuel.';
    } elseif ($nouveau === '' && $identifiant === $compte['username']) {
        $error = 'Rien à changer : modifie ton identifiant ou saisis un nouveau mot de passe.';
    } else {
        $pris = $pdo->prepare('SELECT COUNT(*) FROM admins WHERE username = ? AND id <> ?');
        $pris->execute([$identifiant, $compte['id']]);
        if ((int) $pris->fetchColumn() > 0) {
            $error = 'Cet identifiant est déjà utilisé.';
        } else {
            $hash = $nouveau !== '' ? password_hash($nouveau, PASSWORD_DEFAULT) : $compte['password_hash'];
            $pdo->prepare('UPDATE admins SET username = ?, password_hash = ? WHERE id = ?')->execute([$identifiant, $hash, $compte['id']]);

            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $compte['id'];
            $_SESSION['admin_user'] = $identifiant;

            $changes = [];
            if ($identifiant !== $compte['username']) {
                $changes[] = 'identifiant';
            }
            if ($nouveau !== '') {
                $changes[] = 'mot de passe';
            }
            $_SESSION['flash'] = ucfirst(implode(' et ', $changes)) . ' mis à jour. Utilise-les dès ta prochaine connexion.';
            header('Location: compte.php');
            exit;
        }
    }
}

admin_debut('Mon compte', 'compte');
?>
<div class="a-titre">
  <div>
    <h1>Mon compte</h1>
    <p class="a-intro">Change ton identifiant et ton mot de passe de connexion. Ton mot de passe actuel est demandé pour toute modification.</p>
  </div>
</div>
<?php admin_flash(); ?>
<?php admin_erreur($error); ?>

<form method="post" class="carte form" autocomplete="off">
  <?= csrf_field() ?>

  <div class="champ">
    <label for="identifiant">Identifiant</label>
    <input type="text" id="identifiant" name="identifiant" value="<?= e($identifiant) ?>" autocomplete="username" maxlength="50" required>
    <p class="aide">Évite « admin » ou ton prénom : un identifiant peu évident est une barrière de plus.</p>
  </div>

  <div class="champ">
    <label for="nouveau">Nouveau mot de passe</label>
    <input type="password" id="nouveau" name="nouveau" autocomplete="new-password" minlength="12" maxlength="200">
    <p class="aide">Laisse vide pour garder l’actuel. Au moins 12 caractères mélangeant minuscules, majuscules, chiffres et symboles, ou une phrase de 16 caractères et plus (ex. « Trois tresses sous la pluie ! »). L’idéal : le faire générer et retenir par un gestionnaire de mots de passe (trousseau iCloud, Bitwarden…).</p>
  </div>

  <div class="champ">
    <label for="confirmation">Confirme le nouveau mot de passe</label>
    <input type="password" id="confirmation" name="confirmation" autocomplete="new-password" maxlength="200">
  </div>

  <div class="champ">
    <label for="actuel">Mot de passe actuel *</label>
    <input type="password" id="actuel" name="actuel" autocomplete="current-password" required>
  </div>

  <button class="b b-plein b-grand">Enregistrer</button>
</form>

<h2 class="a-sous">Protections en place</h2>
<div class="carte" style="padding: 16px 20px; max-width: 600px">
  <ul style="margin: 0; padding-left: 20px; line-height: 1.8; font-size: 15px">
    <li>Après <?= ECHECS_MAX_PAR_IP ?> essais ratés, la connexion est bloquée <?= ECHECS_FENETRE_MINUTES ?> minutes.</li>
    <li>Déconnexion automatique après <?= ADMIN_INACTIVITE_MAX / 3600 ?> h sans activité.</li>
    <li>Les mots de passe sont stockés chiffrés (bcrypt) : personne ne peut les relire, même avec l’accès à la base.</li>
  </ul>
</div>
<?php
admin_fin();
