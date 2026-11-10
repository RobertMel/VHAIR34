<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/prestations.php';
require_admin();

$prestations = prestations_toutes(getPDO());
$message = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Gestion des prestations</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; color: #222; }
h1 { margin-bottom: 8px; }
.intro { color: #555; margin: 0 0 20px; max-width: 720px; line-height: 1.5; }
.flash { background: #e8f5e9; border: 1px solid #a5d6a7; padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; }
.table-wrap { overflow-x: auto; }
table { border-collapse: collapse; width: 100%; background: white; min-width: 760px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: top; }
th { background: #eee; }
tr.masquee td { color: #888; background: #fafafa; }
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
<h1>Prestations</h1>
<p class="intro">Les prestations visibles s'affichent sur la page d'accueil et dans le formulaire de réservation, dans l'ordre indiqué. <strong>Masquer</strong> retire une prestation du site sans la supprimer (pratique pour une prestation saisonnière). Les réservations déjà faites gardent le nom de la prestation choisie, même si tu la supprimes ensuite.</p>

<?php if ($message): ?><p class="flash"><?= e($message) ?></p><?php endif; ?>

<p><a href="prestation_edit.php" class="button">Ajouter une prestation</a></p>

<div class="table-wrap">
<table>
<tr>
    <th>Ordre</th>
    <th>Catégorie</th>
    <th>Prestation</th>
    <th>Prix</th>
    <th>Durée</th>
    <th>Sur le site</th>
    <th>Actions</th>
</tr>
<?php if (!$prestations): ?>
<tr><td colspan="7">Aucune prestation pour l'instant. Clique sur « Ajouter une prestation » pour commencer.</td></tr>
<?php endif; ?>
<?php foreach ($prestations as $p): ?>
<tr class="<?= $p['actif'] ? '' : 'masquee' ?>">
    <td><?= (int) $p['ordre'] ?></td>
    <td><?= e($p['categorie']) ?></td>
    <td><?= e($p['nom']) ?></td>
    <td><?= e($p['prix']) ?></td>
    <td><?= e($p['duree']) ?></td>
    <td class="<?= $p['actif'] ? 'status-yes' : 'status-no' ?>"><?= $p['actif'] ? 'Visible' : 'Masquée' ?></td>
    <td>
        <a href="prestation_edit.php?id=<?= (int) $p['id'] ?>" class="button">Modifier</a>
        <form method="post" action="prestation_action.php" class="inline">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <input type="hidden" name="action" value="toggle">
            <button class="button secondary"><?= $p['actif'] ? 'Masquer' : 'Afficher' ?></button>
        </form>
        <form method="post" action="prestation_action.php" class="inline" onsubmit="return confirm('Supprimer définitivement « <?= e(addslashes($p['nom'])) ?> » ?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
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
