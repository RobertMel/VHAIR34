<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/prestations.php';
require_once __DIR__ . '/includes/meches.php';
require_once __DIR__ . '/includes/images.php';
require_once __DIR__ . '/includes/layout.php';

$pdo = getPDO();
$prestations = prestations_actives($pdo);
$meches = meches_actives($pdo);

// Créneau demandé
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit('Créneau invalide.');
}
$stmt = $pdo->prepare("SELECT id, date, heure, disponible FROM creneaux WHERE id = ?");
$stmt->execute([$id]);
$creneau = $stmt->fetch();
if (!$creneau) {
    http_response_code(404);
    exit('Créneau introuvable.');
}

$estPasse = strtotime($creneau['date'] . ' ' . $creneau['heure']) < time();
$reservable = (int) $creneau['disponible'] === 1 && !$estPasse;

$error = null;
$success = false;
$nom = $telephone = $prestation = $prestation_libre = $meche = '';
$mode = 'sans';
$libellePrestation = $libelleMeche = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reservable) {
    csrf_check();

    $nom              = trim((string) ($_POST['nom'] ?? ''));
    $telephone        = trim((string) ($_POST['telephone'] ?? ''));
    $prestation       = trim((string) ($_POST['prestation'] ?? ''));   // id de la prestation choisie
    $prestation_libre = trim((string) ($_POST['prestation_libre'] ?? ''));
    $mode             = ($_POST['meches_mode'] ?? 'sans') === 'avec' && $meches ? 'avec' : 'sans';
    $meche            = trim((string) ($_POST['meche'] ?? ''));        // id de la mèche choisie
    $telephone_net    = preg_replace('/[\s.\-()]/', '', $telephone);

    $choisie = null;
    foreach ($prestations as $p) {
        if ((string) $p['id'] === $prestation) {
            $choisie = $p;
        }
    }
    $mecheChoisie = null;
    foreach ($meches as $m) {
        if ((string) $m['id'] === $meche) {
            $mecheChoisie = $m;
        }
    }
    $libellePrestation = $prestation_libre !== '' ? $prestation_libre : ($choisie ? prestation_libelle($choisie) : '');

    if ($prestation !== '' && $prestation_libre === '' && !$choisie) {
        $error = "Cette prestation n'est plus proposée, choisis-en une autre dans la liste.";
    } elseif ($nom === '' || $telephone === '' || $libellePrestation === '') {
        $error = "Merci de remplir ton nom, ton téléphone et la prestation.";
    } elseif (mb_strlen($nom) > 100) {
        $error = "Le nom est trop long.";
    } elseif (!preg_match('/^(\+|00)?\d{9,15}$/', $telephone_net)) {
        $error = "Le numéro de téléphone n'est pas valide.";
    } elseif (mb_strlen($libellePrestation) > 255) {
        $error = "La description de la prestation est trop longue (255 caractères maximum).";
    } elseif ($mode === 'avec' && !$mecheChoisie) {
        $error = $meche === '' ? "Tu as choisi « Avec mèches » : sélectionne ta mèche dans la liste." : "Cette mèche n'est plus proposée, choisis-en une autre.";
    } else {
        $libelleMeche = $mode === 'avec' ? meche_libelle($mecheChoisie) : '';
        try {
            creneaux_colonnes_meches($pdo);
            // La condition « disponible = 1 » garantit qu'on n'écrase jamais une réservation existante,
            // même si deux personnes valident le même créneau en même temps.
            $stmt = $pdo->prepare("UPDATE creneaux
                                   SET nom_client = ?, telephone = ?, prestation = ?, avec_meches = ?, meche = ?, disponible = 0
                                   WHERE id = ? AND disponible = 1");
            $stmt->execute([$nom, $telephone_net, $libellePrestation, $mode === 'avec' ? 1 : 0, $libelleMeche !== '' ? $libelleMeche : null, $id]);

            if ($stmt->rowCount() === 1) {
                $success = true;
            } else {
                $reservable = false;
                $error = "Désolée, ce créneau vient d'être réservé par quelqu'un d'autre.";
            }
        } catch (PDOException $e) {
            error_log('Erreur réservation créneau ' . $id . ' : ' . $e->getMessage());
            $error = "Une erreur est survenue, la réservation n'a pas été enregistrée. Réessaie ou écris-moi sur Instagram.";
        }
    }
}

$d = date_morceaux($creneau['date']);
page_debut('Réserver le ' . date_fr($creneau['date']) . ' — V.Hair 34');
?>
<main class="resa">
  <div class="resa-entete">
    <a href="/#creneaux" class="icone-btn" aria-label="Retour aux créneaux">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M15 18l-6-6 6-6"></path></svg>
    </a>
    <a href="/" class="nav-logo" aria-label="V.Hair 34, accueil">V</a>
    <span style="width: 44px"></span>
  </div>

  <div class="resa-titre">
    <h1>Ton rendez-vous</h1>
    <p>Plus qu’une coiffure, une expérience</p>
  </div>

  <div class="creneau-choisi">
    <div style="display: flex; align-items: center; gap: 16px">
      <span class="gros" aria-hidden="true"><?= e($d['num']) ?></span>
      <div>
        <p class="date"><?= e(date_fr($creneau['date'])) ?></p>
        <p class="heure">à <?= e(heure_fr($creneau['heure'])) ?></p>
      </div>
    </div>
    <a href="/#creneaux" style="font-size: 15px; padding: 10px 0">Changer</a>
  </div>

  <?php if ($error): ?>
    <p class="erreur" role="alert"><?= e($error) ?></p>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="voile" role="dialog" aria-modal="true" aria-labelledby="titre-confirmation">
      <div class="popup">
        <h2 id="titre-confirmation">C’est réservé !</h2>
        <p>Merci <strong><?= e($nom) ?></strong>, ton créneau du <?= e(date_fr($creneau['date'])) ?> à <?= e(heure_fr($creneau['heure'])) ?> est enregistré.</p>
        <p class="recap"><?= e($libellePrestation) ?><?= $libelleMeche !== '' ? '<br>Avec mèche : ' . e($libelleMeche) : '' ?></p>
        <p class="important">Dernière étape : envoie-moi un message sur Instagram pour valider ton rendez-vous.</p>
        <a href="<?= e(SITE_INSTAGRAM) ?>" class="btn btn-plein" rel="noopener"><?= icone_instagram() ?> Écrire à <?= e(SITE_INSTAGRAM_NOM) ?></a>
        <a href="/" style="font-size: 15px; padding: 8px 0">Retour à l’accueil</a>
      </div>
    </div>
  <?php elseif (!$reservable): ?>
    <div class="indispo">
      <p>Ce créneau n’est plus disponible.</p>
      <a href="/#creneaux" class="btn btn-plein">Voir les autres créneaux</a>
    </div>
  <?php else: ?>
    <form method="post" class="form">
      <?= csrf_field() ?>

      <div class="champ">
        <label for="nom">Ton nom</label>
        <input id="nom" name="nom" type="text" placeholder="Prénom et nom" autocomplete="name" maxlength="100" value="<?= e($nom) ?>" required>
      </div>
      <div class="champ">
        <label for="telephone">Téléphone</label>
        <input id="telephone" name="telephone" type="tel" placeholder="06 12 34 56 78" autocomplete="tel" maxlength="20" value="<?= e($telephone) ?>" required>
      </div>

      <fieldset>
        <legend>Ta prestation</legend>
        <?php foreach ($prestations as $p): ?>
          <label class="choix">
            <input type="radio" name="prestation" value="<?= (int) $p['id'] ?>" <?= (string) $p['id'] === $prestation ? 'checked' : '' ?>>
            <span class="choix-nom">
              <span class="choix-titre"><?= e($p['nom']) ?></span>
              <?php if ($p['prix'] !== ''): ?><span class="choix-sous"><?= prix($p['prix']) ?></span><?php endif; ?>
            </span>
          </label>
        <?php endforeach; ?>
        <a href="/#carte" class="lien-discret">Voir le détail des prestations</a>
      </fieldset>

      <div class="champ">
        <label for="prestation_libre">Autre demande (facultatif)</label>
        <input id="prestation_libre" name="prestation_libre" type="text" placeholder="Ex. knotless mi-longues" maxlength="255" value="<?= e($prestation_libre) ?>">
      </div>

      <?php if ($meches): ?>
        <fieldset>
          <legend>Mèches</legend>
          <div class="modes">
            <label class="mode">
              <input type="radio" name="meches_mode" value="sans" id="sans-meches" <?= $mode === 'sans' ? 'checked' : '' ?>>
              <span class="fort">J’apporte mes mèches</span>
              <span class="sous">Sans supplément</span>
            </label>
            <label class="mode">
              <input type="radio" name="meches_mode" value="avec" id="avec-meches" <?= $mode === 'avec' ? 'checked' : '' ?>>
              <span class="fort">Avec mèches</span>
              <span class="sous">Fournies par V.Hair</span>
            </label>
          </div>
          <div class="liste-meches" id="liste-meches">
            <p class="sous-titre">Choisis ta mèche :</p>
            <?php foreach ($meches as $m): $photo = $m['photos'][0]['fichier'] ?? ''; ?>
              <label class="choix">
                <input type="radio" name="meche" value="<?= (int) $m['id'] ?>" <?= (string) $m['id'] === $meche ? 'checked' : '' ?>>
                <?php if ($photo !== ''): ?><img src="<?= e(image_url($photo, true)) ?>" alt="" width="44" height="44" loading="lazy"><?php endif; ?>
                <span class="choix-nom"><span class="choix-titre"><?= e($m['nom']) ?></span></span>
                <span class="choix-prix"><?= $m['prix'] !== '' ? '+&nbsp;' . prix($m['prix']) : '' ?></span>
              </label>
            <?php endforeach; ?>
            <a href="/meches.php" class="lien-discret">Voir les photos des mèches</a>
          </div>
        </fieldset>
      <?php endif; ?>

      <button type="submit" class="btn-resa">Réserver ce créneau</button>
      <p class="note">Ensuite, envoie-moi un message sur Instagram pour valider définitivement ton rendez-vous.</p>
    </form>
  <?php endif; ?>
</main>
<?php
page_fin();
