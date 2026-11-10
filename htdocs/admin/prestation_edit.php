<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/prestations.php';
require_admin();

$pdo = getPDO();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$edition = (bool) $id;

if ($edition) {
    $p = prestation_par_id($pdo, $id);
    if (!$p) {
        http_response_code(404);
        exit('Prestation introuvable');
    }
} else {
    $max = (int) prestations_query($pdo, "SELECT COALESCE(MAX(ordre), 0) FROM prestations")->fetchColumn();
    $p = ['categorie' => 'Ponytails', 'nom' => '', 'prix' => '', 'duree' => '', 'materiel' => '', 'description' => '', 'ordre' => $max + 10, 'actif' => 1];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $p = [
        'categorie'   => trim((string) ($_POST['categorie'] ?? '')),
        'nom'         => trim((string) ($_POST['nom'] ?? '')),
        'prix'        => trim((string) ($_POST['prix'] ?? '')),
        'duree'       => trim((string) ($_POST['duree'] ?? '')),
        'materiel'    => trim((string) ($_POST['materiel'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
        'ordre'       => (int) ($_POST['ordre'] ?? 0),
        'actif'       => isset($_POST['actif']) ? 1 : 0,
    ];

    if ($p['nom'] === '') {
        $error = 'Le nom de la prestation est obligatoire.';
    } elseif (!in_array($p['categorie'], PRESTATIONS_CATEGORIES, true)) {
        $error = 'Choisis une catégorie dans la liste.';
    } elseif (mb_strlen($p['nom']) > 150 || mb_strlen($p['prix']) > 80 || mb_strlen($p['duree']) > 50 || mb_strlen($p['materiel']) > 255) {
        $error = 'Un des champs est trop long : nom 150, prix 80, durée 50 et matériel 255 caractères maximum.';
    } elseif (mb_strlen($p['description']) > 2000) {
        $error = 'La description est trop longue (2000 caractères maximum).';
    } else {
        $valeurs = [$p['categorie'], $p['nom'], $p['prix'], $p['duree'], $p['materiel'], $p['description'], $p['ordre'], $p['actif']];
        if ($edition) {
            prestations_query($pdo, "UPDATE prestations SET categorie = ?, nom = ?, prix = ?, duree = ?, materiel = ?, description = ?, ordre = ?, actif = ? WHERE id = ?", array_merge($valeurs, [$id]));
            $_SESSION['flash'] = "Prestation « {$p['nom']} » modifiée.";
        } else {
            prestations_query($pdo, "INSERT INTO prestations (categorie, nom, prix, duree, materiel, description, ordre, actif) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", $valeurs);
            $_SESSION['flash'] = "Prestation « {$p['nom']} » ajoutée.";
        }
        header('Location: prestations.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= $edition ? 'Modifier une prestation' : 'Ajouter une prestation' ?></title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; color: #222; }
h1 { margin-bottom: 20px; }
form { max-width: 560px; display: flex; flex-direction: column; gap: 6px; }
label { font-weight: bold; margin-top: 10px; }
.aide { font-size: 13px; color: #666; margin: 0; }
input[type=text], input[type=number], select, textarea { padding: 8px; font: inherit; border: 1px solid #bbb; border-radius: 4px; }
textarea { min-height: 110px; resize: vertical; }
.check { font-weight: normal; display: flex; gap: 8px; align-items: center; margin-top: 14px; }
button { margin-top: 18px; align-self: flex-start; padding: 10px 18px; background: #007BFF; color: white; border: none; border-radius: 4px; font: inherit; cursor: pointer; }
button:hover { background: #0056b3; }
.error { color: #b00020; background: #fdecea; border: 1px solid #f5c2c0; padding: 10px 14px; border-radius: 6px; max-width: 560px; }
</style>
</head>
<body>
<h1><?= $edition ? 'Modifier la prestation' : 'Ajouter une prestation' ?></h1>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

<form method="post">
    <?= csrf_field() ?>

    <label for="nom">Nom de la prestation *</label>
    <input type="text" id="nom" name="nom" maxlength="150" required value="<?= e($p['nom']) ?>" placeholder="Ex. Ponytail Middle Part">

    <label for="categorie">Catégorie</label>
    <select id="categorie" name="categorie">
        <?php foreach (PRESTATIONS_CATEGORIES as $c): ?>
            <option value="<?= e($c) ?>" <?= $c === $p['categorie'] ? 'selected' : '' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="prix">Prix</label>
    <input type="text" id="prix" name="prix" maxlength="80" value="<?= e($p['prix']) ?>" placeholder="Ex. 45 €  ou  50 € (+5 € franges)">
    <p class="aide">Texte libre : tu peux écrire « À partir de 40 € » ou « Sur devis ».</p>

    <label for="duree">Durée</label>
    <input type="text" id="duree" name="duree" maxlength="50" value="<?= e($p['duree']) ?>" placeholder="Ex. 2h30">

    <label for="materiel">Matériel à prévoir</label>
    <input type="text" id="materiel" name="materiel" maxlength="255" value="<?= e($p['materiel']) ?>" placeholder="Ex. 2 paquets de mèches naturelles">

    <label for="description">Description ou instructions</label>
    <textarea id="description" name="description" maxlength="2000" placeholder="Ex. Les cheveux doivent être propres, sans produit."><?= e($p['description']) ?></textarea>

    <label for="ordre">Ordre d'affichage</label>
    <input type="number" id="ordre" name="ordre" step="1" value="<?= (int) $p['ordre'] ?>">
    <p class="aide">Les prestations sont triées du plus petit au plus grand nombre (10, 20, 30…).</p>

    <label class="check"><input type="checkbox" name="actif" <?= $p['actif'] ? 'checked' : '' ?>> Visible sur le site</label>

    <button><?= $edition ? 'Enregistrer les modifications' : 'Ajouter la prestation' ?></button>
</form>

<p><a href="prestations.php">Retour à la liste des prestations</a></p>
</body>
</html>
