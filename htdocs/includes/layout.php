<?php
// Morceaux communs aux pages publiques : <head>, menu, pied de page.

const SITE_INSTAGRAM = 'https://www.instagram.com/v.hair_34/';
const SITE_INSTAGRAM_NOM = '@v.hair_34';

function page_debut(string $titre, string $description = ''): void
{
    $v = @filemtime(__DIR__ . '/../assets/site.css') ?: 1;
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titre) ?></title>
<?php if ($description !== ''): ?><meta name="description" content="<?= e($description) ?>"><?php endif; ?>
<meta name="theme-color" content="#F4EBE2">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Italiana&amp;family=Pinyon+Script&amp;family=Jost:wght@300;400;500&amp;display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/site.css?v=<?= (int) $v ?>">
</head>
<body>
<?php
}

/** Menu du site. $page = 'accueil' ou 'meches' (lien marqué comme page courante). */
function site_nav(string $page = 'accueil'): void
{
    $base = $page === 'accueil' ? '' : '/';
    ?>
<header>
  <nav class="nav" aria-label="Menu principal">
    <a href="/" class="nav-logo" aria-label="V.Hair 34, accueil">V</a>
    <div class="nav-liens">
      <a href="<?= $base ?>#carte">Prestations</a>
      <a href="/meches.php"<?= $page === 'meches' ? ' aria-current="page"' : '' ?>>Nos mèches</a>
      <a href="<?= $base ?>#infos">Avant de venir</a>
      <a href="<?= $base ?>#creneaux">Créneaux</a>
      <a href="<?= $base ?>#creneaux" class="btn btn-plein">Réserver</a>
    </div>
  </nav>
</header>
<?php
}

function site_pied(): void
{
    ?>
<footer class="pied">
  <div class="badge" aria-hidden="true">
    <span class="badge-v">V</span>
    <span class="badge-mot">V.HAIR</span>
    <span class="badge-34">34</span>
  </div>
  <p>Une question, un devis pour des braids ?</p>
  <a href="<?= e(SITE_INSTAGRAM) ?>" class="btn btn-plein" rel="noopener">
    <?= icone_instagram() ?>
    Écrire sur Instagram
  </a>
  <p class="petit">V.Hair 34, Montpellier</p>
</footer>
<?php
}

function page_fin(string $script = ''): void
{
    if ($script !== '') {
        echo "<script>\n" . $script . "\n</script>\n";
    }
    echo "</body>\n</html>\n";
}

function icone_instagram(int $taille = 18): string
{
    return '<svg width="' . $taille . '" height="' . $taille . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle cx="17.5" cy="6.5" r="0.8" fill="currentColor"></circle></svg>';
}
