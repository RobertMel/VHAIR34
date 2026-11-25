<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/meches.php';
require_once __DIR__ . '/includes/images.php';
require_once __DIR__ . '/includes/layout.php';

$meches = meches_actives(getPDO());

page_debut('Nos mèches — V.Hair 34', 'Les mèches proposées par V.Hair 34 à Montpellier : photos, longueurs, coloris et prix.');
site_nav('meches');
?>
<main>
  <section class="page-titre">
    <h1>Nos mèches</h1>
    <p class="script">Choisies avec soin, pour un rendu naturel</p>
    <p class="intro">Tu peux venir avec tes mèches, ou choisir l’une des miennes au moment de réserver : sélectionne « Avec mèches », puis celle qui te plaît.</p>
  </section>

  <?php if (!$meches): ?>
    <p class="intro-section" style="padding: 0 20px 96px">Les mèches arrivent bientôt sur le site. En attendant, écris-moi sur Instagram pour savoir lesquelles sont disponibles.</p>
  <?php else: ?>
    <div class="meches">
      <?php foreach ($meches as $m): $photos = $m['photos']; ?>
        <article class="meche">
          <?php if ($photos): ?>
            <img class="meche-photo" src="<?= e(image_url($photos[0]['fichier'])) ?>" alt="<?= e($m['nom']) ?>" loading="lazy" data-principale>
            <?php if (count($photos) > 1): ?>
              <div class="vignettes">
                <?php foreach ($photos as $i => $ph): ?>
                  <a class="vignette" href="<?= e(image_url($ph['fichier'])) ?>" data-grande="<?= e(image_url($ph['fichier'])) ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Photo <?= $i + 1 ?> de <?= e($m['nom']) ?>">
                    <img src="<?= e(image_url($ph['fichier'], true)) ?>" alt="" loading="lazy">
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <div class="meche-vide" aria-hidden="true">V</div>
          <?php endif; ?>
          <div class="meche-entete">
            <h2 class="meche-nom"><?= e($m['nom']) ?></h2>
            <span class="pointilles" aria-hidden="true"></span>
            <span class="meche-prix"><?= prix($m['prix']) ?></span>
          </div>
          <?php if (trim((string) $m['description']) !== ''): ?><p class="meche-desc"><?= e($m['description']) ?></p><?php endif; ?>
          <?php if ($m['longueurs'] !== ''): ?><p class="meche-infos">Longueurs : <?= e($m['longueurs']) ?></p><?php endif; ?>
          <?php if ($m['coloris'] !== ''): ?><p class="meche-infos">Coloris : <?= e($m['coloris']) ?></p><?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <section class="section-bordeaux sur-fonce">
    <div class="appel">
      <p class="grand">Ta mèche est choisie ?</p>
      <p class="doux">Réserve ton créneau et sélectionne-la à l’étape suivante.</p>
      <a href="/#creneaux" class="btn btn-nude">Voir les créneaux</a>
    </div>
  </section>
</main>
<?php
site_pied();
// Confort : une vignette remplace la photo principale sans quitter la page (sans JavaScript, elle ouvre la photo)
page_fin(<<<'JS'
document.querySelectorAll('.meche').forEach(function (carte) {
  var grande = carte.querySelector('[data-principale]');
  carte.querySelectorAll('.vignette').forEach(function (v) {
    v.addEventListener('click', function (e) {
      e.preventDefault();
      grande.src = v.dataset.grande;
      carte.querySelectorAll('.vignette').forEach(function (x) { x.setAttribute('aria-pressed', x === v ? 'true' : 'false'); });
    });
  });
});
JS);
