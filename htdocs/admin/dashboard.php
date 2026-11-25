<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/meches.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

$pdo = getPDO();
creneaux_colonnes_meches($pdo); // ajoute les colonnes du choix des mèches si besoin

$creneaux = $pdo->query("SELECT * FROM creneaux ORDER BY date ASC, heure ASC")->fetchAll();
$maintenant = time();
$aVenir = $libres = $passees = [];
foreach ($creneaux as $c) {
    $futur = strtotime($c['date'] . ' ' . $c['heure']) >= $maintenant;
    if (!$c['disponible'] && $futur) {
        $aVenir[] = $c;
    } elseif ($c['disponible'] && $futur) {
        $libres[] = $c;
    } elseif (!$c['disponible']) {
        $passees[] = $c;
    }
}
$passees = array_slice(array_reverse($passees), 0, 50); // les plus récentes d'abord

function meches_texte(array $c): string
{
    return !empty($c['avec_meches']) ? 'Avec mèche : ' . ($c['meche'] ?? '') : 'Ses propres mèches';
}

admin_debut('Réservations', 'dashboard');
?>
<div class="a-titre">
  <div>
    <h1>Bonjour <?= e($_SESSION['admin_user'] ?? '') ?></h1>
    <p class="a-intro">Tes prochains rendez-vous, du plus proche au plus lointain.</p>
  </div>
  <a href="creneaux_add.php" class="b b-plein b-grand">Ajouter un créneau</a>
</div>

<?php if (strcasecmp($_SESSION['admin_user'] ?? '', 'admin') === 0): ?>
  <p class="a-msg a-msg-err" role="alert">Ton identifiant est « admin », le premier que les robots essaient. <a href="compte.php">Change ton identifiant et ton mot de passe</a> dès maintenant.</p>
<?php endif; ?>
<?php admin_flash(); ?>

<div class="stats">
  <div class="carte stat"><div class="n"><?= count($aVenir) ?></div><div class="l">réservation<?= count($aVenir) > 1 ? 's' : '' ?> à venir</div></div>
  <div class="carte stat"><div class="n"><?= count($libres) ?></div><div class="l">créneau<?= count($libres) > 1 ? 'x' : '' ?> libre<?= count($libres) > 1 ? 's' : '' ?> en ligne</div></div>
  <div class="carte stat"><div class="n"><?= count($passees) ?></div><div class="l">rendez-vous passé<?= count($passees) > 1 ? 's' : '' ?></div></div>
</div>

<h2 class="a-sous">Prochaines réservations</h2>
<?php if (!$aVenir): ?>
  <div class="carte vide">Aucune réservation à venir pour l’instant.</div>
<?php else: ?>
  <div class="resas">
    <?php foreach ($aVenir as $c): $d = date_morceaux($c['date']); ?>
      <article class="carte resa">
        <div class="resa-date">
          <div class="j"><?= e($d['jour_court']) ?></div>
          <div class="n"><?= e($d['num']) ?></div>
          <div class="j"><?= e($d['mois']) ?></div>
          <div class="h"><?= e(heure_fr($c['heure'])) ?></div>
        </div>
        <div class="resa-infos">
          <span class="nom"><?= e($c['nom_client'] ?? 'Sans nom') ?></span>
          <?php if (!empty($c['telephone'])): ?><a href="tel:<?= e($c['telephone']) ?>"><?= e($c['telephone']) ?></a><?php endif; ?>
          <span><?= prix($c['prestation'] ?? '') ?></span>
          <span class="doux"><?= prix(meches_texte($c)) ?></span>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<h2 class="a-sous">Rendez-vous passés</h2>
<?php if (!$passees): ?>
  <div class="carte vide">Aucun rendez-vous passé.</div>
<?php else: ?>
  <table class="tableau">
    <thead><tr><th>Date</th><th>Cliente</th><th>Téléphone</th><th>Prestation</th><th>Mèches</th></tr></thead>
    <tbody>
    <?php foreach ($passees as $c): ?>
      <tr>
        <td data-label="Date"><?= e(date('d/m/Y', strtotime($c['date']))) ?> à <?= e(heure_fr($c['heure'])) ?></td>
        <td data-label="Cliente" class="titre-ligne"><?= e($c['nom_client'] ?? '-') ?></td>
        <td data-label="Téléphone"><?= e($c['telephone'] ?? '-') ?></td>
        <td data-label="Prestation"><?= e($c['prestation'] ?? '-') ?></td>
        <td data-label="Mèches"><?= e(meches_texte($c)) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
<?php
admin_fin();
