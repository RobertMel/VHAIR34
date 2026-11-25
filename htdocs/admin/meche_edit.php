<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/meches.php';
require_once __DIR__ . '/../includes/images.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

const PHOTOS_MAX_PAR_MECHE = 12;

$pdo = getPDO();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$edition = (bool) $id;

if ($edition) {
    $m = meche_par_id($pdo, $id);
    if (!$m) {
        http_response_code(404);
        exit('Mèche introuvable');
    }
} else {
    $max = (int) meches_query($pdo, "SELECT COALESCE(MAX(ordre), 0) FROM meches")->fetchColumn();
    $m = ['nom' => '', 'prix' => '', 'description' => '', 'longueurs' => '', 'coloris' => '', 'ordre' => $max + 10, 'actif' => 1, 'photos' => []];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Envoi plus lourd que la limite de l'hébergement : PHP vide alors tout le formulaire
    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $error = "L'envoi est trop lourd pour l'hébergement. Envoie moins de photos à la fois (3 ou 4 maximum).";
    } else {
        csrf_check();

        $photosAvant = $m['photos'];
        $m = [
            'nom'         => trim((string) ($_POST['nom'] ?? '')),
            'prix'        => trim((string) ($_POST['prix'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'longueurs'   => trim((string) ($_POST['longueurs'] ?? '')),
            'coloris'     => trim((string) ($_POST['coloris'] ?? '')),
            'ordre'       => (int) ($_POST['ordre'] ?? 0),
            'actif'       => isset($_POST['actif']) ? 1 : 0,
            'photos'      => $photosAvant,
        ];
        $envoyees = images_liste_envoyees('photos');

        if ($m['nom'] === '') {
            $error = 'Le nom de la mèche est obligatoire.';
        } elseif (mb_strlen($m['nom']) > 120 || mb_strlen($m['prix']) > 80 || mb_strlen($m['longueurs']) > 150 || mb_strlen($m['coloris']) > 150) {
            $error = 'Un des champs est trop long : nom 120, prix 80, longueurs et coloris 150 caractères maximum.';
        } elseif (mb_strlen($m['description']) > 2000) {
            $error = 'La description est trop longue (2000 caractères maximum).';
        } elseif (count($photosAvant) + count($envoyees) > PHOTOS_MAX_PAR_MECHE) {
            $error = 'Une mèche peut avoir ' . PHOTOS_MAX_PAR_MECHE . ' photos au maximum. Supprimes-en ou envoies-en moins.';
        } else {
            $valeurs = [$m['nom'], $m['prix'], $m['description'], $m['longueurs'], $m['coloris'], $m['ordre'], $m['actif']];
            if ($edition) {
                meches_query($pdo, "UPDATE meches SET nom = ?, prix = ?, description = ?, longueurs = ?, coloris = ?, ordre = ?, actif = ? WHERE id = ?", array_merge($valeurs, [$id]));
                $flash = "Mèche « {$m['nom']} » enregistrée.";
            } else {
                meches_query($pdo, "INSERT INTO meches (nom, prix, description, longueurs, coloris, ordre, actif) VALUES (?, ?, ?, ?, ?, ?, ?)", $valeurs);
                $id = (int) $pdo->lastInsertId();
                $flash = "Mèche « {$m['nom']} » ajoutée.";
            }

            // Photos : chacune est contrôlée et réenregistrée ; une photo refusée n'empêche pas les autres
            $ajoutees = 0;
            $refus = [];
            $ordre = (int) meches_query($pdo, "SELECT COALESCE(MAX(ordre), -1) FROM meche_photos WHERE meche_id = ?", [$id])->fetchColumn();
            foreach ($envoyees as $f) {
                try {
                    $fichier = image_enregistrer($f);
                    meches_query($pdo, "INSERT INTO meche_photos (meche_id, fichier, ordre) VALUES (?, ?, ?)", [$id, $fichier, ++$ordre]);
                    $ajoutees++;
                } catch (RuntimeException $e) {
                    $refus[] = $e->getMessage();
                }
            }
            if ($ajoutees) {
                $flash .= " $ajoutees photo" . ($ajoutees > 1 ? 's ajoutées.' : ' ajoutée.');
            }
            if ($refus) {
                $flash .= "\nPhotos refusées :\n– " . implode("\n– ", $refus);
            }
            $_SESSION['flash'] = $flash;

            // On reste sur la fiche pour voir les photos ajoutées
            header('Location: meche_edit.php?id=' . $id);
            exit;
        }
    }
}

admin_debut($edition ? 'Modifier une mèche' : 'Ajouter une mèche', 'meches');
?>
<div class="a-titre"><h1><?= $edition ? e($m['nom']) : 'Ajouter une mèche' ?></h1></div>
<?php admin_erreur($error); ?>
<?php admin_flash(); ?>

<form method="post" enctype="multipart/form-data" class="carte form">
  <?= csrf_field() ?>
  <input type="hidden" name="MAX_FILE_SIZE" value="<?= IMAGES_TAILLE_MAX ?>">

  <div class="champ">
    <label for="nom">Nom de la mèche *</label>
    <input type="text" id="nom" name="nom" maxlength="120" required value="<?= e($m['nom']) ?>" placeholder="Ex. Mèche naturelle brésilienne">
  </div>

  <div class="champ">
    <label for="prix">Prix</label>
    <input type="text" id="prix" name="prix" maxlength="80" value="<?= e($m['prix']) ?>" placeholder="Ex. 15 €  ou  20 € le paquet">
    <p class="aide">Affiché comme supplément quand la cliente choisit « Avec mèches ».</p>
  </div>

  <div class="deux">
    <div class="champ">
      <label for="longueurs">Longueurs disponibles</label>
      <input type="text" id="longueurs" name="longueurs" maxlength="150" value="<?= e($m['longueurs']) ?>" placeholder="Ex. 40, 50 et 60 cm">
    </div>
    <div class="champ">
      <label for="coloris">Coloris</label>
      <input type="text" id="coloris" name="coloris" maxlength="150" value="<?= e($m['coloris']) ?>" placeholder="Ex. Noir naturel, châtain">
    </div>
  </div>

  <div class="champ">
    <label for="description">Description</label>
    <textarea id="description" name="description" maxlength="2000" placeholder="Texture, rendu, pour quelles coiffures…"><?= e($m['description']) ?></textarea>
  </div>

  <div class="champ">
    <label for="photos">Ajouter des photos</label>
    <input type="file" id="photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>
    <p class="aide">Tu peux en choisir plusieurs (3 ou 4 par envoi conseillé, 10 Mo maximum chacune). Elles sont allégées automatiquement et la localisation du téléphone est retirée.</p>
  </div>

  <div class="deux">
    <div class="champ">
      <label for="ordre">Ordre d’affichage</label>
      <input type="number" id="ordre" name="ordre" step="1" value="<?= (int) $m['ordre'] ?>">
      <p class="aide">Du plus petit au plus grand (10, 20, 30…).</p>
    </div>
    <div class="champ" style="justify-content: center">
      <label class="case"><input type="checkbox" name="actif" <?= $m['actif'] ? 'checked' : '' ?>> Visible sur le site</label>
    </div>
  </div>

  <button class="b b-plein b-grand"><?= $edition ? 'Enregistrer' : 'Ajouter la mèche' ?></button>
</form>

<?php if ($edition): ?>
  <h2 class="a-sous">Photos (<?= count($m['photos']) ?>/<?= PHOTOS_MAX_PAR_MECHE ?>)</h2>
  <?php if (!$m['photos']): ?>
    <div class="carte vide">Aucune photo pour l’instant. Choisis-les dans « Ajouter des photos », puis clique sur « Enregistrer ».</div>
  <?php else: ?>
    <p class="a-intro" style="margin: 0 0 12px">La première photo est la photo principale, affichée en grand sur le site.</p>
    <div class="photos">
      <?php foreach ($m['photos'] as $i => $ph): ?>
        <div class="carte photo">
          <img src="<?= e(image_url($ph['fichier'], true)) ?>" alt="Photo <?= $i + 1 ?> de <?= e($m['nom']) ?>">
          <?php if ($i === 0): ?>
            <span class="principale">Photo principale</span>
          <?php else: ?>
            <form method="post" action="meche_action.php">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $id ?>">
              <input type="hidden" name="photo" value="<?= (int) $ph['id'] ?>">
              <input type="hidden" name="action" value="photo_principale">
              <button class="b">Mettre en principale</button>
            </form>
          <?php endif; ?>
          <form method="post" action="meche_action.php" onsubmit="return confirm('Supprimer cette photo ?')">
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

<a href="meches.php" class="a-retour">← Retour aux mèches</a>
<?php
admin_fin();
