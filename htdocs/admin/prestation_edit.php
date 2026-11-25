<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/prestations.php';
require_once __DIR__ . '/../includes/images.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

const PHOTOS_MAX_PAR_PRESTATION = 12;

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
    $p = ['categorie' => 'Ponytails', 'nom' => '', 'prix' => '', 'duree' => '', 'materiel' => '', 'description' => '', 'ordre' => $max + 10, 'actif' => 1, 'photos' => []];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    // Envoi plus lourd que la limite de l'hébergement : PHP vide alors tout le formulaire
    $error = "L'envoi est trop lourd pour l'hébergement. Envoie moins de photos à la fois (3 ou 4 maximum).";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $photosAvant = $p['photos'];
    $p = [
        'categorie'   => trim((string) ($_POST['categorie'] ?? '')),
        'nom'         => trim((string) ($_POST['nom'] ?? '')),
        'prix'        => trim((string) ($_POST['prix'] ?? '')),
        'duree'       => trim((string) ($_POST['duree'] ?? '')),
        'materiel'    => trim((string) ($_POST['materiel'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
        'ordre'       => (int) ($_POST['ordre'] ?? 0),
        'actif'       => isset($_POST['actif']) ? 1 : 0,
        'photos'      => $photosAvant,
    ];
    $envoyees = images_liste_envoyees('photos');

    if ($p['nom'] === '') {
        $error = 'Le nom de la prestation est obligatoire.';
    } elseif (!in_array($p['categorie'], PRESTATIONS_CATEGORIES, true)) {
        $error = 'Choisis une catégorie dans la liste.';
    } elseif (mb_strlen($p['nom']) > 150 || mb_strlen($p['prix']) > 80 || mb_strlen($p['duree']) > 50 || mb_strlen($p['materiel']) > 255) {
        $error = 'Un des champs est trop long : nom 150, prix 80, durée 50 et matériel 255 caractères maximum.';
    } elseif (mb_strlen($p['description']) > 2000) {
        $error = 'La description est trop longue (2000 caractères maximum).';
    } elseif (count($photosAvant) + count($envoyees) > PHOTOS_MAX_PAR_PRESTATION) {
        $error = 'Une prestation peut avoir ' . PHOTOS_MAX_PAR_PRESTATION . ' photos au maximum. Supprimes-en ou envoies-en moins.';
    } else {
        $valeurs = [$p['categorie'], $p['nom'], $p['prix'], $p['duree'], $p['materiel'], $p['description'], $p['ordre'], $p['actif']];
        if ($edition) {
            prestations_query($pdo, "UPDATE prestations SET categorie = ?, nom = ?, prix = ?, duree = ?, materiel = ?, description = ?, ordre = ?, actif = ? WHERE id = ?", array_merge($valeurs, [$id]));
            $_SESSION['flash'] = "Prestation « {$p['nom']} » modifiée.";
        } else {
            prestations_query($pdo, "INSERT INTO prestations (categorie, nom, prix, duree, materiel, description, ordre, actif) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", $valeurs);
            $id = (int) $pdo->lastInsertId();
            $_SESSION['flash'] = "Prestation « {$p['nom']} » ajoutée.";
        }

        // Photos : chacune est contrôlée et réenregistrée ; une photo refusée n'empêche pas les autres
        if ($envoyees) {
            $ajoutees = 0;
            $refus = [];
            $ordre = (int) prestations_query($pdo, "SELECT COALESCE(MAX(ordre), -1) FROM prestation_photos WHERE prestation_id = ?", [$id])->fetchColumn();
            foreach ($envoyees as $f) {
                try {
                    $fichier = image_enregistrer($f, 'prestations');
                    prestations_query($pdo, "INSERT INTO prestation_photos (prestation_id, fichier, ordre) VALUES (?, ?, ?)", [$id, $fichier, ++$ordre]);
                    $ajoutees++;
                } catch (RuntimeException $e) {
                    $refus[] = $e->getMessage();
                }
            }
            if ($ajoutees) {
                $_SESSION['flash'] .= " $ajoutees photo" . ($ajoutees > 1 ? 's ajoutées.' : ' ajoutée.');
            }
            if ($refus) {
                $_SESSION['flash'] .= "\nPhotos refusées :\n– " . implode("\n– ", $refus);
            }
            // On reste sur la fiche pour voir les photos ajoutées
            header('Location: prestation_edit.php?id=' . $id);
            exit;
        }
        header('Location: prestations.php');
        exit;
    }
}

admin_debut($edition ? 'Modifier une prestation' : 'Ajouter une prestation', 'prestations');
?>
<div class="a-titre"><h1><?= $edition ? 'Modifier la prestation' : 'Ajouter une prestation' ?></h1></div>
<?php admin_erreur($error); ?>
<?php admin_flash(); ?>

<form method="post" enctype="multipart/form-data" class="carte form">
  <input type="hidden" name="MAX_FILE_SIZE" value="<?= IMAGES_TAILLE_MAX ?>">
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
    <label for="photos">Ajouter des photos</label>
    <input type="file" id="photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>
    <p class="aide">Des exemples de cette coiffure. Plusieurs à la fois possible (3 ou 4 par envoi conseillé, 10 Mo maximum chacune). Elles sont allégées automatiquement et la localisation du téléphone est retirée.</p>
  </div>

  <div class="champ">
    <label for="ordre">Ordre d’affichage</label>
    <input type="number" id="ordre" name="ordre" step="1" value="<?= (int) $p['ordre'] ?>">
    <p class="aide">Les prestations sont triées du plus petit au plus grand nombre (10, 20, 30…).</p>
  </div>

  <label class="case"><input type="checkbox" name="actif" <?= $p['actif'] ? 'checked' : '' ?>> Visible sur le site</label>

  <button class="b b-plein b-grand"><?= $edition ? 'Enregistrer' : 'Ajouter la prestation' ?></button>
</form>

<?php if ($edition): ?>
  <h2 class="a-sous">Photos (<?= count($p['photos']) ?>/<?= PHOTOS_MAX_PAR_PRESTATION ?>)</h2>
  <?php if (!$p['photos']): ?>
    <div class="carte vide">Aucune photo pour l’instant. Choisis-les dans « Ajouter des photos », puis clique sur « Enregistrer ».</div>
  <?php else: ?>
    <p class="a-intro" style="margin: 0 0 12px">La première photo est la photo principale, affichée en premier sur le site.</p>
    <div class="photos">
      <?php foreach ($p['photos'] as $i => $ph): ?>
        <div class="carte photo">
          <img src="<?= e(image_url($ph['fichier'], true, 'prestations')) ?>" alt="Photo <?= $i + 1 ?> de <?= e($p['nom']) ?>">
          <?php if ($i === 0): ?>
            <span class="principale">Photo principale</span>
          <?php else: ?>
            <form method="post" action="prestation_action.php">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $id ?>">
              <input type="hidden" name="photo" value="<?= (int) $ph['id'] ?>">
              <input type="hidden" name="action" value="photo_principale">
              <button class="b">Mettre en principale</button>
            </form>
          <?php endif; ?>
          <form method="post" action="prestation_action.php" onsubmit="return confirm('Supprimer cette photo ?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $id ?>">
            <input type="hidden" name="photo" value="<?= (int) $ph['id'] ?>">
            <input type="hidden" name="action" value="photo_supprimer">
            <button class="b b-danger">Supprimer</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<a href="prestations.php" class="a-retour">← Retour aux prestations</a>
<?php
admin_fin();
