<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/meches.php';
require_once __DIR__ . '/../includes/images.php';
require_admin();

$meches = meches_toutes(getPDO());
$message = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Nos mèches — Admin</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; color: #222; }
h1 { margin-bottom: 8px; }
.intro { color: #555; margin: 0 0 20px; max-width: 720px; line-height: 1.5; }
.flash { background: #e8f5e9; border: 1px solid #a5d6a7; padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; white-space: pre-line; }
.table-wrap { overflow-x: auto; }
table { border-collapse: collapse; width: 100%; background: white; min-width: 720px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: middle; }
th { background: #eee; }
tr.masquee td { color: #888; background: #fafafa; }
td img { width: 56px; height: 56px; object-fit: cover; border-radius: 4px; display: block; }
.sans-photo { width: 56px; height: 56px; border-radius: 4px; background: #eee; display: flex; align-items: center; justify-content: center; font-size: 11px; color: #777; }
a.button, button.button { display: inline-block; padding: 6px 12px; background: #007BFF; color: white; text-decoration: none; border: none; border-radius: 4px; font: inherit; font-size: 14px; cursor: pointer; margin: 2px 0; }
a.button:hover, button.button:hover { background: #0056b3; }
button.secondary { background: #6c757d; }
button.secondary:hover { background: #545b62; }
button.danger { background: #c0392b; }
button.danger:hover { background: #922b21; }
form.inline { display: inline; }
.status-yes { color: green; font-weight: bold; }
.status-no { color: #a15c00; font-weight: bold; }
</style>
</head>
<body>
<h1>Nos mèches</h1>
<p class="intro">Les mèches visibles s'affichent sur la page « Nos mèches » du site et dans le formulaire de réservation, quand la cliente choisit « Avec mèches ». Tu peux ajouter plusieurs photos par mèche. <strong>Masquer</strong> retire une mèche du site sans la supprimer, par exemple en cas de rupture de stock.</p>

<?php if ($message): ?><p class="flash"><?= e($message) ?></p><?php endif; ?>

<p><a href="meche_edit.php" class="button">Ajouter une mèche</a></p>

<div class="table-wrap">
<table>
<tr>
    <th>Photo</th>
    <th>Ordre</th>
    <th>Mèche</th>
    <th>Prix</th>
    <th>Photos</th>
    <th>Sur le site</th>
    <th>Actions</th>
</tr>
<?php if (!$meches): ?>
<tr><td colspan="7">Aucune mèche pour l'instant. Clique sur « Ajouter une mèche » pour commencer.</td></tr>
<?php endif; ?>
<?php foreach ($meches as $m): ?>
<tr class="<?= $m['actif'] ? '' : 'masquee' ?>">
    <td><?php if ($m['photos']): ?><img src="<?= e(image_url($m['photos'][0]['fichier'], true)) ?>" alt=""><?php else: ?><span class="sans-photo">Aucune</span><?php endif; ?></td>
    <td><?= (int) $m['ordre'] ?></td>
    <td><?= e($m['nom']) ?></td>
    <td><?= e($m['prix']) ?></td>
    <td><?= count($m['photos']) ?></td>
    <td class="<?= $m['actif'] ? 'status-yes' : 'status-no' ?>"><?= $m['actif'] ? 'Visible' : 'Masquée' ?></td>
    <td>
        <a href="meche_edit.php?id=<?= (int) $m['id'] ?>" class="button">Modifier et photos</a>
        <form method="post" action="meche_action.php" class="inline">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
            <input type="hidden" name="action" value="toggle">
            <button class="button secondary"><?= $m['actif'] ? 'Masquer' : 'Afficher' ?></button>
        </form>
        <form method="post" action="meche_action.php" class="inline" onsubmit="return confirm('Supprimer définitivement « <?= e(addslashes($m['nom'])) ?> » et ses photos ?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
            <input type="hidden" name="action" value="delete">
            <button class="button danger">Supprimer</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>
</div>

<p><a href="dashboard.php">Retour au dashboard</a></p>
</body>
</html>
