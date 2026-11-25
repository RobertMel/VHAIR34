<?php
// Envoi de photos depuis l'admin : contrôle, redimensionnement et réenregistrement.
// Chaque photo est réécrite en JPEG par GD : tout contenu caché et les données GPS du téléphone disparaissent.

// Sous-dossiers de uploads/ autorisés : un par type de contenu
const IMAGES_RACINE = __DIR__ . '/../uploads/';
const IMAGES_URL_RACINE = '/uploads/';
const IMAGES_SOUS_DOSSIERS = ['meches', 'prestations'];
const IMAGES_TAILLE_MAX = 10 * 1024 * 1024;   // 10 Mo par photo
const IMAGES_PIXELS_MAX = 40000000;           // 40 mégapixels
const IMAGES_COTE_MAX = 1600;                 // photo affichée en grand
const IMAGES_COTE_VIGNETTE = 480;             // vignette

/** Remet $_FILES['champ'] (envoi multiple) sous forme d'une liste de fichiers. */
function images_liste_envoyees(string $champ): array
{
    if (empty($_FILES[$champ]) || !is_array($_FILES[$champ]['name'])) {
        return [];
    }
    $f = $_FILES[$champ];
    $liste = [];
    foreach ($f['name'] as $i => $nom) {
        if (($f['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $liste[] = ['name' => $nom, 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    }
    return $liste;
}

/**
 * Contrôle et enregistre une photo envoyée. Retourne le nom du fichier créé (ex. « 9f2c1a7b5e3d4c21.jpg »).
 * Lève une RuntimeException avec un message lisible par la coiffeuse en cas de problème.
 */
function image_enregistrer(array $fichier, string $sous = 'meches'): string
{
    $dossier = images_dossier($sous);
    $nomAffiche = mb_substr((string) $fichier['name'], 0, 60);

    if ($fichier['error'] === UPLOAD_ERR_INI_SIZE || $fichier['error'] === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException("« $nomAffiche » est trop lourde (10 Mo maximum).");
    }
    if ($fichier['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($fichier['tmp_name'])) {
        throw new RuntimeException("« $nomAffiche » n'a pas pu être envoyée. Réessaie.");
    }
    if ($fichier['size'] > IMAGES_TAILLE_MAX) {
        throw new RuntimeException("« $nomAffiche » est trop lourde (10 Mo maximum).");
    }

    // Le contenu doit vraiment être une image, quelle que soit l'extension
    $info = @getimagesize($fichier['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpeg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($types[$info[2]])) {
        throw new RuntimeException("« $nomAffiche » n'est pas une photo JPEG, PNG ou WebP.");
    }
    [$largeur, $hauteur] = $info;
    if ($largeur < 1 || $hauteur < 1 || $largeur * $hauteur > IMAGES_PIXELS_MAX) {
        throw new RuntimeException("« $nomAffiche » a une résolution trop grande.");
    }

    @ini_set('memory_limit', '256M');
    $type = $types[$info[2]];
    $fonction = 'imagecreatefrom' . $type;
    if (!function_exists($fonction)) {
        throw new RuntimeException("Le format de « $nomAffiche » n'est pas pris en charge par l'hébergement. Essaie en JPEG.");
    }
    $src = @$fonction($fichier['tmp_name']);
    if (!$src) {
        throw new RuntimeException("« $nomAffiche » est illisible ou abîmée.");
    }

    // Photos de téléphone : remettre à l'endroit selon l'orientation enregistrée
    if ($type === 'jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($fichier['tmp_name']);
        $angle = [3 => 180, 6 => -90, 8 => 90][(int) ($exif['Orientation'] ?? 1)] ?? 0;
        if ($angle !== 0) {
            $tourne = imagerotate($src, $angle, 0);
            if ($tourne) {
                imagedestroy($src);
                $src = $tourne;
            }
        }
    }

    if (!is_dir($dossier) && !@mkdir($dossier, 0755, true)) {
        imagedestroy($src);
        throw new RuntimeException("Le dossier des photos n'a pas pu être créé sur l'hébergement.");
    }

    $base = bin2hex(random_bytes(8));
    $ok = images_ecrire_redimensionnee($src, $dossier . "/$base.jpg", IMAGES_COTE_MAX, 82)
       && images_ecrire_redimensionnee($src, $dossier . "/{$base}_t.jpg", IMAGES_COTE_VIGNETTE, 78);
    imagedestroy($src);

    if (!$ok) {
        image_supprimer("$base.jpg", $sous);
        throw new RuntimeException("« $nomAffiche » n'a pas pu être enregistrée.");
    }
    return "$base.jpg";
}

/** Écrit une copie JPEG de $src dont le plus grand côté fait au plus $cote pixels. */
function images_ecrire_redimensionnee($src, string $chemin, int $cote, int $qualite): bool
{
    $l = imagesx($src);
    $h = imagesy($src);
    $ratio = min(1, $cote / max($l, $h));
    $nl = max(1, (int) round($l * $ratio));
    $nh = max(1, (int) round($h * $ratio));

    $dst = imagecreatetruecolor($nl, $nh);
    // Fond ivoire sous les zones transparentes (PNG, WebP)
    imagefill($dst, 0, 0, imagecolorallocate($dst, 244, 235, 226));
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nl, $nh, $l, $h);
    imageinterlace($dst, true);
    $ok = imagejpeg($dst, $chemin, $qualite);
    imagedestroy($dst);
    return $ok;
}

/** Supprime une photo et sa vignette. N'accepte que les noms créés par image_enregistrer(). */
function image_supprimer(string $fichier, string $sous = 'meches'): void
{
    if (!preg_match('/^([a-f0-9]{16})\.jpg$/', $fichier, $m)) {
        return;
    }
    $dossier = images_dossier($sous);
    foreach ([$dossier . "/{$m[1]}.jpg", $dossier . "/{$m[1]}_t.jpg"] as $chemin) {
        if (is_file($chemin)) {
            @unlink($chemin);
        }
    }
}

function image_url(string $fichier, bool $vignette = false, string $sous = 'meches'): string
{
    if (!preg_match('/^([a-f0-9]{16})\.jpg$/', $fichier, $m) || !in_array($sous, IMAGES_SOUS_DOSSIERS, true)) {
        return '';
    }
    return IMAGES_URL_RACINE . $sous . '/' . $m[1] . ($vignette ? '_t' : '') . '.jpg';
}

/** Chemin du sous-dossier de uploads/ (liste fermée, jamais construit depuis une saisie). */
function images_dossier(string $sous): string
{
    if (!in_array($sous, IMAGES_SOUS_DOSSIERS, true)) {
        throw new InvalidArgumentException('Dossier de photos inconnu');
    }
    return IMAGES_RACINE . $sous;
}
