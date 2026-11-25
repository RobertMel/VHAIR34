<?php
// Protection de la connexion admin : limitation des essais et règles de mot de passe.

const ECHECS_MAX_PAR_IP = 5;          // essais ratés autorisés depuis une même connexion…
const ECHECS_MAX_PAR_IDENTIFIANT = 20; // …et sur un même identifiant (attaques réparties)
const ECHECS_FENETRE_MINUTES = 15;

function securite_install(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS connexions_ratees (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        ip VARCHAR(45) NOT NULL,
        identifiant VARCHAR(100) NOT NULL,
        created_at DATETIME NOT NULL,
        KEY idx_ip (ip, created_at),
        KEY idx_identifiant (identifiant, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function securite_query(PDO $pdo, string $sql, array $params = []): PDOStatement
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        if ($e->getCode() !== '42S02') {
            throw $e;
        }
        securite_install($pdo);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'inconnue'), 0, 45);
}

/** Vrai si trop d'essais ratés récents depuis cette connexion ou sur cet identifiant. */
function connexion_bloquee(PDO $pdo, string $identifiant): bool
{
    $depuis = date('Y-m-d H:i:s', time() - ECHECS_FENETRE_MINUTES * 60);
    $parIp = (int) securite_query($pdo, "SELECT COUNT(*) FROM connexions_ratees WHERE ip = ? AND created_at > ?", [client_ip(), $depuis])->fetchColumn();
    if ($parIp >= ECHECS_MAX_PAR_IP) {
        return true;
    }
    $parId = (int) securite_query($pdo, "SELECT COUNT(*) FROM connexions_ratees WHERE identifiant = ? AND created_at > ?", [mb_substr($identifiant, 0, 100), $depuis])->fetchColumn();
    return $parId >= ECHECS_MAX_PAR_IDENTIFIANT;
}

function connexion_echec(PDO $pdo, string $identifiant): void
{
    securite_query($pdo, "INSERT INTO connexions_ratees (ip, identifiant, created_at) VALUES (?, ?, ?)", [client_ip(), mb_substr($identifiant, 0, 100), date('Y-m-d H:i:s')]);
    // Ménage : on ne garde qu'une journée d'historique
    securite_query($pdo, "DELETE FROM connexions_ratees WHERE created_at < ?", [date('Y-m-d H:i:s', time() - 86400)]);
}

function connexion_reussie(PDO $pdo): void
{
    securite_query($pdo, "DELETE FROM connexions_ratees WHERE ip = ?", [client_ip()]);
}

/**
 * Vérifie qu'un mot de passe est assez robuste. Retourne null s'il convient, sinon le problème à corriger.
 * Règle : 12 caractères minimum avec au moins 3 types de caractères (minuscule, majuscule, chiffre, symbole),
 * ou une phrase de 16 caractères et plus ; jamais l'identifiant ni un mot de passe courant.
 */
function mot_de_passe_probleme(string $mdp, string $identifiant): ?string
{
    $longueur = mb_strlen($mdp);
    if ($longueur < 12) {
        return 'Le mot de passe doit faire au moins 12 caractères.';
    }
    if ($longueur > 200) {
        return 'Le mot de passe est trop long (200 caractères maximum).';
    }
    $types = (int) preg_match('/\p{Ll}/u', $mdp) + (int) preg_match('/\p{Lu}/u', $mdp)
           + (int) preg_match('/\d/', $mdp) + (int) preg_match('/[^\p{L}\d]/u', $mdp);
    if ($longueur < 16 && $types < 3) {
        return 'Mélange au moins 3 types de caractères (minuscules, majuscules, chiffres, symboles), ou choisis une phrase de 16 caractères ou plus.';
    }
    $bas = mb_strtolower($mdp);
    if (mb_strlen($identifiant) >= 3 && str_contains($bas, mb_strtolower($identifiant))) {
        return 'Le mot de passe ne doit pas contenir ton identifiant.';
    }
    foreach (['password', 'motdepasse', 'azerty', 'qwerty', '123456', '000000', 'admin', 'vhair', 'coiffure', 'iloveyou', 'soleil', 'bonjour'] as $courant) {
        if (str_contains($bas, $courant)) {
            return "Le mot de passe contient « $courant », trop facile à deviner.";
        }
    }
    if (preg_match('/(.)\1{3,}/u', $mdp)) {
        return 'Le mot de passe contient trop de caractères répétés à la suite.';
    }
    return null;
}
