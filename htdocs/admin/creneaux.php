<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

$pdo = getPDO();
$avecPasses = isset($_GET['passes']);
$creneaux = $pdo->query("SELECT * FROM creneaux ORDER BY date ASC, heure ASC")->fetchAll();
$maintenant = time();

$liste = [];
$nbPasses = 0;
foreach ($creneaux as $c) {
    $c['passe'] = strtotime($c['date'] . ' ' . $c['heure']) < $maintenant;
    if ($c['passe']) {
        $nbPasses++;
        if (!$avecPasses) {
            continue;
        }
    }
    $liste[] = $c;
}

admin_debut('Créneaux', 'creneaux');
?>
<div class="a-titre">
  <div>
    <h1>Créneaux</h1>
    <p class="a-intro">Les créneaux libres et à venir s’affichent sur le site. Un créneau réservé n’est plus proposé aux autres clientes.</p>
  </div>
  <a href="creneaux_add.php" class="b b-plein b-grand">Ajouter un créneau</a>
</div>
<?php admin_flash(); ?>

<p style="margin: 0 0 14px; font-size: 15px">
  <?php if ($avecPasses): ?>
    <a href="creneaux.php">Masquer les créneaux passés</a>
  <?php elseif ($nbPasses): ?>
    <a href="creneaux.php?passes=1">Voir aussi les <?= $nbPasses ?> créneau<?= $nbPasses > 1 ? 'x' : '' ?> passé<?= $nbPasses > 1 ? 's' : '' ?></a>
  <?php endif; ?>
</p>

<?php if (!$liste): ?>
  <div class="carte vide">Aucun créneau à venir. Clique sur « Ajouter un créneau » pour en proposer.</div>
<?php else: ?>
  <table class="tableau">
    <thead><tr><th>Date</th><th>Heure</th><th>État</th><th>Cliente</th><th>Téléphone</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($liste as $c): ?>
      <tr class="<?= $c['passe'] ? 'attenue' : '' ?>">
        <td data-label="Date" class="titre-ligne"><?= e(date_fr($c['date'])) ?></td>
        <td data-label="Heure"><?= e(heure_fr($c['heure'])) ?></td>
        <td data-label="État">
          <?php if ($c['passe']): ?><span class="etat etat-passe">Passé</span>
          <?php elseif ($c['disponible']): ?><span class="etat etat-libre">Libre</span>
          <?php else: ?><span class="etat etat-pris">Réservé</span><?php endif; ?>
        </td>
        <td data-label="Cliente"><?= e($c['nom_client'] ?? '–') ?></td>
        <td data-label="Téléphone"><?php if (!empty($c['telephone'])): ?><a href="tel:<?= e($c['telephone']) ?>"><?= e($c['telephone']) ?></a><?php else: ?>–<?php endif; ?></td>
        <td class="actions-ligne sans-label">
          <div class="actions">
            <a href="creneaux_edit.php?id=<?= (int) $c['id'] ?>" class="b">Modifier</a>
            <form method="post" action="creneaux_delete.php" class="en-ligne"
                  onsubmit="return confirm('<?= $c['disponible'] ? 'Supprimer ce créneau ?' : 'Ce créneau est réservé par ' . e(addslashes($c['nom_client'] ?? '')) . '. Le supprimer quand même ?' ?>')">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
              <button class="b b-danger">Supprimer</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
<?php
admin_fin();
