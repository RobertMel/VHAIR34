<?php
// Fonctions communes : session, échappement HTML, protection CSRF, accès admin, dates.

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

date_default_timezone_set('Europe/Paris');

// Les erreurs PHP ne doivent jamais s'afficher aux visiteurs.
ini_set('display_errors', '0');
error_reporting(E_ALL);

/** Échappe une valeur pour l'afficher dans du HTML. */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Champ caché à mettre dans chaque formulaire POST. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Bloque la requête si le jeton CSRF est absent ou faux. */
function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    $expected = $_SESSION['csrf'] ?? '';
    if (!is_string($sent) || $sent === '' || $expected === '' || !hash_equals($expected, $sent)) {
        http_response_code(400);
        exit('Requête invalide. Recharge la page et réessaie.');
    }
}

const ADMIN_INACTIVITE_MAX = 2 * 3600; // déconnexion automatique après 2 h sans activité

/** Redirige vers la connexion si l'admin n'est pas connecté ou inactif depuis trop longtemps. */
function require_admin(): void
{
    if (($_SESSION['admin_logged'] ?? false) !== true) {
        header('Location: /admin/login.php');
        exit;
    }
    $vu = (int) ($_SESSION['admin_vu'] ?? time());
    if (time() - $vu > ADMIN_INACTIVITE_MAX) {
        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: /admin/login.php?expire=1');
        exit;
    }
    $_SESSION['admin_vu'] = time();
}

/** Date en français, ex. « Samedi 14 novembre 2026 » (remplace strftime, supprimé de PHP). */
function date_fr(string $date): string
{
    $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $mois  = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet',
              'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $ts = strtotime($date);
    if ($ts === false) {
        return e($date);
    }
    return ucfirst($jours[(int) date('w', $ts)]) . ' ' . date('j', $ts) . ' '
        . $mois[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

/** Prix affiché sans retour à la ligne entre le nombre et « € » (ex. « 45 € » ne se coupe pas). */
function prix(string $texte): string
{
    return e(preg_replace('/(\d)\s+€/u', "$1\u{00A0}€", $texte));
}

/** Morceaux d'une date pour l'affichage en tuile : ['jour' => 'Samedi', 'jour_court' => 'Sam.', 'num' => '14', 'mois' => 'novembre']. */
function date_morceaux(string $date): array
{
    $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $mois  = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet',
              'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $ts = strtotime($date);
    if ($ts === false) {
        return ['jour' => '', 'jour_court' => '', 'num' => '', 'mois' => ''];
    }
    $j = $jours[(int) date('w', $ts)];
    return ['jour' => $j, 'jour_court' => mb_substr($j, 0, 3) . '.', 'num' => date('j', $ts), 'mois' => $mois[(int) date('n', $ts) - 1]];
}

/** Heure au format 14h30. */
function heure_fr(string $heure): string
{
    $ts = strtotime($heure);
    return $ts === false ? e($heure) : date('G\hi', $ts);
}

function is_valid_date(string $d): bool
{
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt !== false && $dt->format('Y-m-d') === $d;
}

function is_valid_time(string $t): bool
{
    return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $t);
}
