<?php
// En-tête, menu et pied communs aux pages de l'espace admin.

/** $actif = 'dashboard' | 'creneaux' | 'prestations' | 'meches' | 'compte' (onglet surligné). */
function admin_debut(string $titre, string $actif = ''): void
{
    $v = @filemtime(__DIR__ . '/../assets/admin.css') ?: 1;
    $onglets = [
        'dashboard'   => ['dashboard.php', 'Réservations'],
        'creneaux'    => ['creneaux.php', 'Créneaux'],
        'prestations' => ['prestations.php', 'Prestations'],
        'meches'      => ['meches.php', 'Mèches'],
    ];
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#441518">
<title><?= e($titre) ?> — Admin V.Hair 34</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Italiana&amp;family=Pinyon+Script&amp;family=Jost:wght@400;500&amp;display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin.css?v=<?= (int) $v ?>">
</head>
<body>
<header class="a-entete">
  <div class="a-entete-in">
    <a href="dashboard.php" class="a-marque"><span class="v" aria-hidden="true">V</span><span class="mot">V.HAIR 34</span><span class="tag">Admin</span></a>
    <div class="a-compte">
      <a href="/" target="_blank" rel="noopener">Voir le site</a>
      <a href="compte.php"<?= $actif === 'compte' ? ' aria-current="page"' : '' ?>>Mon compte</a>
      <a href="logout.php">Déconnexion</a>
    </div>
  </div>
</header>
<nav class="a-menu" aria-label="Menu de l'admin">
  <div class="a-menu-in">
    <?php foreach ($onglets as $cle => [$lien, $nom]): ?>
      <a href="<?= $lien ?>"<?= $cle === $actif ? ' aria-current="page"' : '' ?>><?= $nom ?></a>
    <?php endforeach; ?>
  </div>
</nav>
<main class="a-page">
<?php
}

function admin_fin(): void
{
    echo "</main>\n</body>\n</html>\n";
}

/** Affiche puis efface le message laissé par l'action précédente. */
function admin_flash(): void
{
    $msg = $_SESSION['flash'] ?? '';
    unset($_SESSION['flash']);
    if ($msg !== '') {
        echo '<p class="a-msg a-msg-ok" role="status">' . e($msg) . '</p>';
    }
}

function admin_erreur(string $msg): void
{
    if ($msg !== '') {
        echo '<p class="a-msg a-msg-err" role="alert">' . e($msg) . '</p>';
    }
}
