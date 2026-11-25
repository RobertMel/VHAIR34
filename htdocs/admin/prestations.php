<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/prestations.php';
require_once __DIR__ . '/../includes/images.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

$prestations = prestations_toutes(getPDO());

admin_debut('Prestations', 'prestations');
?>
<div class="a-titre">
  <div>
    <h1>Prestations</h1>
    <p class="a-intro">Les prestations visibles s’affichent sur la carte du site et dans le formulaire de réservation, dans l’ordre indiqué. « Masquer » retire une prestation du site sans la supprimer. Les réservations déjà faites gardent leur prestation, même si tu la modifies ou la supprimes.</p>
  </div>
  <a href="prestation_edit.php" class="b b-plein b-grand">Ajouter une prestation</a>
</div>
<?php admin_flash(); ?>

<?php if (!$prestations): ?>
  <div class="carte vide">Aucune prestation pour l’instant. Clique sur « Ajouter une prestation » pour commencer.</div>
<?php else: ?>
  <table class="tableau">
    <thead><tr><th>Photo</th><th>Ordre</th><th>Prestation</th><th>Catégorie</th><th>Prix</th><th>Durée</th><th>Site</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($prestations as $p): ?>
      <tr class="<?= $p['actif'] ? '' : 'attenue' ?>">
        <td data-label="Photo">
          <?php if ($p['photos']): ?><img class="vignette" src="<?= e(image_url($p['photos'][0]['fichier'], true, 'prestations')) ?>" alt=""><?php else: ?><span class="sans-photo" aria-hidden="true">V</span><?php endif; ?>
        </td>
        <td data-label="Ordre"><?= (int) $p['ordre'] ?></td>
        <td data-label="Prestation" class="titre-ligne"><?= e($p['nom']) ?></td>
        <td data-label="Catégorie"><?= e($p['categorie']) ?></td>
        <td data-label="Prix"><?= e($p['prix']) ?></td>
        <td data-label="Durée"><?= e($p['duree'] !== '' ? $p['duree'] : '–') ?></td>
        <td data-label="Sur le site"><span class="etat <?= $p['actif'] ? 'etat-libre' : 'etat-masque' ?>"><?= $p['actif'] ? 'Visible' : 'Masquée' ?></span></td>
        <td class="actions-ligne sans-label">
          <div class="actions">
            <a href="prestation_edit.php?id=<?= (int) $p['id'] ?>" class="b">Modifier et photos</a>
            <form method="post" action="prestation_action.php" class="en-ligne">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
              <input type="hidden" name="action" value="toggle">
              <button class="b"><?= $p['actif'] ? 'Masquer' : 'Afficher' ?></button>
            </form>
            <form method="post" action="prestation_action.php" class="en-ligne" onsubmit="return confirm('Supprimer définitivement « <?= e(addslashes($p['nom'])) ?> » ?')">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
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
