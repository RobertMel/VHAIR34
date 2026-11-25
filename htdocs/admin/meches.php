<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/meches.php';
require_once __DIR__ . '/../includes/images.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

$meches = meches_toutes(getPDO());

admin_debut('Mèches', 'meches');
?>
<div class="a-titre">
  <div>
    <h1>Mèches</h1>
    <p class="a-intro">Les mèches visibles s’affichent sur la page « Nos mèches » et dans la réservation, quand la cliente choisit « Avec mèches ». Plusieurs photos possibles par mèche. « Masquer » la retire du site sans la supprimer, par exemple en cas de rupture de stock.</p>
  </div>
  <a href="meche_edit.php" class="b b-plein b-grand">Ajouter une mèche</a>
</div>
<?php admin_flash(); ?>

<?php if (!$meches): ?>
  <div class="carte vide">Aucune mèche pour l’instant. Clique sur « Ajouter une mèche » pour commencer.</div>
<?php else: ?>
  <table class="tableau">
    <thead><tr><th>Photo</th><th>Mèche</th><th>Prix</th><th>Photos</th><th>Ordre</th><th>Site</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($meches as $m): ?>
      <tr class="<?= $m['actif'] ? '' : 'attenue' ?>">
        <td data-label="Photo">
          <?php if ($m['photos']): ?><img class="vignette" src="<?= e(image_url($m['photos'][0]['fichier'], true)) ?>" alt=""><?php else: ?><span class="sans-photo" aria-hidden="true">V</span><?php endif; ?>
        </td>
        <td data-label="Mèche" class="titre-ligne"><?= e($m['nom']) ?></td>
        <td data-label="Prix"><?= e($m['prix'] !== '' ? $m['prix'] : '–') ?></td>
        <td data-label="Photos"><?= count($m['photos']) ?></td>
        <td data-label="Ordre"><?= (int) $m['ordre'] ?></td>
        <td data-label="Sur le site"><span class="etat <?= $m['actif'] ? 'etat-libre' : 'etat-masque' ?>"><?= $m['actif'] ? 'Visible' : 'Masquée' ?></span></td>
        <td class="actions-ligne sans-label">
          <div class="actions">
            <a href="meche_edit.php?id=<?= (int) $m['id'] ?>" class="b">Modifier et photos</a>
            <form method="post" action="meche_action.php" class="en-ligne">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <input type="hidden" name="action" value="toggle">
              <button class="b"><?= $m['actif'] ? 'Masquer' : 'Afficher' ?></button>
            </form>
            <form method="post" action="meche_action.php" class="en-ligne" onsubmit="return confirm('Supprimer définitivement « <?= e(addslashes($m['nom'])) ?> » et ses photos ?')">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <input type="hidden" name="action" value="delete">
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
