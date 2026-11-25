<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/prestations.php';
require_once __DIR__ . '/includes/layout.php';

$pdo = getPDO();

// Prestations visibles, regroupées par catégorie dans l'ordre d'affichage
$categories = [];
foreach (prestations_actives($pdo) as $p) {
    $cat = $p['categorie'] !== '' ? $p['categorie'] : 'Prestations';
    $categories[$cat][] = $p;
}

// Créneaux libres à venir (ceux d'aujourd'hui déjà passés sont masqués)
$stmt = $pdo->prepare("SELECT id, date, heure FROM creneaux
                       WHERE disponible = 1
                       AND (date > :today OR (date = :today2 AND heure > :now))
                       ORDER BY date ASC, heure ASC");
$stmt->execute(['today' => date('Y-m-d'), 'today2' => date('Y-m-d'), 'now' => date('H:i:s')]);
$creneaux = $stmt->fetchAll();

const CRENEAUX_VISIBLES = 8;
$tous = isset($_GET['tous']);

page_debut('V.Hair 34 — Coiffure sur rendez-vous à Montpellier', 'Ponytails, tissages et braids à Montpellier, sur rendez-vous. Choisis ton créneau en ligne.');
site_nav('accueil');
?>
<main>
  <!-- Haut de page -->
  <section id="top" class="hero">
    <div class="logo-bloc">
      <span class="logo-v anim-v" aria-hidden="true">V</span>
      <h1 class="logo-mot anim-mot">V.HAIR<span class="sr-only"> 34</span></h1>
      <span class="logo-34 anim-mot" aria-hidden="true">34</span>
    </div>
    <p class="slogan anim-suite">BEAUTY • HAIR • CONFIDENCE</p>
    <p class="accroche anim-suite">Plus qu’une coiffure, une expérience</p>
    <p class="hero-ligne anim-suite">Ponytails | Tissages | Braids, Montpellier sur rdv</p>
    <p class="hero-ligne anim-suite">Choisis ton créneau, je m’occupe du reste</p>
    <div class="hero-actions anim-suite">
      <a href="#creneaux" class="btn btn-plein">Voir les créneaux</a>
      <a href="#carte" class="btn btn-contour">Prestations et tarifs</a>
    </div>
  </section>

  <!-- Galerie -->
  <div class="galerie">
    <img src="/assets/img/carte.jpg" alt="Carte de visite V.Hair 34 gaufrée" width="399" height="330" loading="lazy">
    <img src="/assets/img/cheveux.jpg" alt="Cheveux ondulés brun chocolat, signature V.Hair 34" width="425" height="259" loading="lazy">
    <img src="/assets/img/sac.jpg" alt="Sac V.Hair 34, vase et bougie" width="424" height="259" loading="lazy">
  </div>

  <!-- La carte -->
  <section id="carte" class="section section-lin">
    <div class="conteneur-etroit">
      <h2 class="titre-section">La carte</h2>
      <p class="intro-section">Mèches non incluses, sauf si tu choisis « Avec mèches » à la réservation. Viens avec les cheveux propres et secs, sans gel, crème, huile ni pommade.</p>

      <?php if (!$categories): ?>
        <p class="intro-section">La carte est en cours de mise à jour. Écris-moi sur Instagram pour connaître les tarifs.</p>
      <?php endif; ?>

      <?php foreach ($categories as $cat => $liste): ?>
        <div class="categorie">
          <h3 class="categorie-titre"><?= e($cat) ?></h3>
          <?php foreach ($liste as $p): ?>
            <div class="presta">
              <?php $prixLong = mb_strlen($p['prix']) > 10; // ex. « 50 € (+5 € franges) » : affiché sous le nom ?>
              <div class="presta-ligne">
                <span class="presta-nom"><?= e($p['nom']) ?></span>
                <?php if (!$prixLong): ?>
                  <span class="pointilles" aria-hidden="true"></span>
                  <span class="presta-prix"><?= prix($p['prix']) ?></span>
                <?php endif; ?>
              </div>
              <?php if ($prixLong): ?><p class="presta-prix-long"><?= prix($p['prix']) ?></p><?php endif; ?>
              <?php if ($p['materiel'] !== '' || $p['duree'] !== ''): ?>
                <div class="presta-detail">
                  <span><?= e($p['materiel']) ?></span>
                  <span><?= e($p['duree']) ?></span>
                </div>
              <?php endif; ?>
              <?php if (trim((string) $p['description']) !== ''): ?>
                <p class="presta-desc"><?= nl2br(e($p['description'])) ?></p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

      <p class="centre" style="margin-top: 8px"><a href="/meches.php" class="btn btn-contour">Voir nos mèches</a></p>
    </div>
  </section>

  <!-- Avant de venir -->
  <section id="infos" class="section">
    <h2 class="titre-section">Avant de venir</h2>
    <p class="intro-section">Réserver, c’est s’engager à honorer son créneau. Ces règles garantissent à chacune le même moment de qualité.</p>
    <div class="regles">
      <div class="regle">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#9C7E70" stroke-width="1.3" aria-hidden="true"><path d="M12 3c3 4 6 7 6 11a6 6 0 0 1-12 0c0-4 3-7 6-11z"></path></svg>
        <div><p class="regle-titre">Cheveux propres et secs</p><p class="regle-texte">Sans aucun résidu de gel, crème, huile ou pommade : c’est la base d’une pose nette.</p></div>
      </div>
      <div class="regle">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#9C7E70" stroke-width="1.3" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>
        <div><p class="regle-titre">20 minutes de retard au maximum</p><p class="regle-texte">Au-delà, le rendez-vous est annulé pour respecter le planning. Prévois un peu d’avance.</p></div>
      </div>
      <div class="regle">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#9C7E70" stroke-width="1.3" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21c0-4 4-6 8-6s8 2 8 6"></path></svg>
        <div><p class="regle-titre">Sans accompagnateur</p><p class="regle-texte">Pour garder un cadre calme, confortable et sécurisé.</p></div>
      </div>
      <div class="regle">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#9C7E70" stroke-width="1.3" aria-hidden="true"><rect x="3" y="6" width="18" height="12" rx="2"></rect><circle cx="12" cy="12" r="2.5"></circle></svg>
        <div><p class="regle-titre">Espèces ou PayPal</p><p class="regle-texte">Merci de prévoir l’appoint si tu règles en espèces.</p></div>
      </div>
    </div>
  </section>

  <!-- Prochains créneaux -->
  <section id="creneaux" class="section section-bordeaux sur-fonce">
    <div class="conteneur">
      <h2 class="titre-section">Prochains créneaux</h2>
      <p class="intro-section">Touche un créneau pour le réserver.</p>

      <?php if (!$creneaux): ?>
        <p class="vide">Aucun créneau disponible pour le moment.<br>Écris-moi sur Instagram pour en savoir plus.</p>
        <p class="centre"><a href="<?= e(SITE_INSTAGRAM) ?>" class="btn btn-clair" rel="noopener"><?= icone_instagram() ?> <?= e(SITE_INSTAGRAM_NOM) ?></a></p>
      <?php else: ?>
        <div class="creneaux" id="liste-creneaux">
          <?php foreach ($creneaux as $i => $c): $d = date_morceaux($c['date']); ?>
            <a href="/reserver.php?id=<?= (int) $c['id'] ?>" class="creneau<?= (!$tous && $i >= CRENEAUX_VISIBLES) ? ' cache' : '' ?>"
               aria-label="Réserver le <?= e(date_fr($c['date'])) ?> à <?= e(heure_fr($c['heure'])) ?>">
              <span class="creneau-jour"><?= e($d['jour']) ?></span>
              <span class="creneau-num"><?= e($d['num']) ?></span>
              <span class="creneau-mois"><?= e($d['mois']) ?></span>
              <span class="creneau-heure"><?= e(heure_fr($c['heure'])) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
        <?php if (!$tous && count($creneaux) > CRENEAUX_VISIBLES): ?>
          <p class="centre"><a href="/?tous=1#creneaux" class="btn btn-clair" id="plus-creneaux">Afficher plus de créneaux</a></p>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php
site_pied();
// Confort : afficher les créneaux suivants sans recharger la page (le lien fonctionne aussi sans JavaScript)
page_fin(<<<'JS'
document.getElementById('plus-creneaux')?.addEventListener('click', function (e) {
  e.preventDefault();
  document.querySelectorAll('#liste-creneaux .cache').forEach(function (el) { el.classList.remove('cache'); });
  this.parentElement.remove();
});
JS);
