<?php
// Modèle de configuration. Copier ce fichier en config.php et renseigner le mot de passe.
// config.php n'est jamais versionné (voir .gitignore).

function getPDO(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host   = "sql105.infinityfree.com";
    $dbname = "if0_40537671_epiz_12345678_vhair";
    $user   = "if0_40537671";
    $pass   = "VOTRE_MOT_DE_PASSE";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // Le détail va dans les logs du serveur, jamais à l'écran.
        error_log('Connexion MySQL impossible : ' . $e->getMessage());
        http_response_code(503);
        exit('Le site est momentanément indisponible. Réessaie dans quelques minutes.');
    }
}
