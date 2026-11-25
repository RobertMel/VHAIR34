<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/prestations.php';
require_once __DIR__ . '/../includes/admin_layout.php';
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

admin_debut($edition ? 'Modifier une prestation' : 'Ajouter une prestation', 'prestations');
?>
<div class="a-titre"><h1><?= $edition ? 'Modifier la prestation' : 'Ajouter une prestation' ?></h1></div>
<?php admin_erreur($error); ?>

<form method="post" class="carte form">
  <?= csrf_field() ?>

  <div class="champ">
    <label for="nom">Nom de la prestation *</label>
    <input type="text" id="nom" name="nom" maxlength="150" required value="<?= e($p['nom']) ?>" placeholder="Ex. Ponytail Middle Part">
  </div>

  <div class="deux">
    <div class="champ">
      <label for="categorie">Catégorie</label>
      <select id="categorie" name="categorie">
        <?php foreach (PRESTATIONS_CATEGORIES as $c): ?>
          <option value="<?= e($c) ?>" <?= $c === $p['categorie'] ? 'selected' : '' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="champ">
      <label for="duree">Durée</label>
      <input type="text" id="duree" name="duree" maxlength="50" value="<?= e($p['duree']) ?>" placeholder="Ex. 2h30">
    </div>
  </div>

  <div class="champ">
    <label for="prix">Prix</label>
    <input type="text" id="prix" name="prix" maxlength="80" value="<?= e($p['prix']) ?>" placeholder="Ex. 45 €  ou  50 € (+5 € franges)">
    <p class="aide">Texte libre : tu peux écrire « À partir de 40 € » ou « Sur devis ».</p>
  </div>

  <div class="champ">
    <label for="materiel">Matériel à prévoir</label>
    <input type="text" id="materiel" name="materiel" maxlength="255" value="<?= e($p['materiel']) ?>" placeholder="Ex. 2 paquets de mèches naturelles">
  </div>

  <div class="champ">
    <label for="description">Description ou instructions</label>
    <textarea id="description" name="description" maxlength="2000" placeholder="Ex. Les cheveux doivent être propres, sans produit."><?= e($p['description']) ?></textarea>
  </div>

  <div class="champ">
    <label for="ordre">Ordre d’affichage</label>
    <input type="number" id="ordre" name="ordre" step="1" value="<?= (int) $p['ordre'] ?>">
    <p class="aide">Les prestations sont triées du plus petit au plus grand nombre (10, 20, 30…).</p>
  </div>

  <label class="case"><input type="checkbox" name="actif" <?= $p['actif'] ? 'checked' : '' ?>> Visible sur le site</label>

  <button class="b b-plein b-grand"><?= $edition ? 'Enregistrer' : 'Ajouter la prestation' ?></button>
</form>

<a href="prestations.php" class="a-retour">← Retour aux prestations</a>
<?php
admin_fin();
