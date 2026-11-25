<?php
// Mèches proposées par la coiffeuse (page « Nos mèches » et option « avec mèches » à la réservation).
// Les tables sont créées automatiquement au premier usage, comme pour les prestations.

function meches_install(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS meches (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(120) NOT NULL,
        prix VARCHAR(80) NOT NULL DEFAULT '',
        description TEXT NULL,
        longueurs VARCHAR(150) NOT NULL DEFAULT '',
        coloris VARCHAR(150) NOT NULL DEFAULT '',
        ordre INT NOT NULL DEFAULT 0,
        actif TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS meche_photos (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        meche_id INT NOT NULL,
        fichier VARCHAR(40) NOT NULL,
        ordre INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_meche (meche_id, ordre),
        CONSTRAINT fk_meche_photos FOREIGN KEY (meche_id) REFERENCES meches(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/** Exécute une requête sur les tables des mèches, en les créant si elles n'existent pas encore. */
function meches_query(PDO $pdo, string $sql, array $params = []): PDOStatement
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        if ($e->getCode() !== '42S02') { // table inexistante
            throw $e;
        }
        meches_install($pdo);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

/** Ajoute à chaque mèche la liste de ses photos (la première = la photo principale). */
function meches_avec_photos(PDO $pdo, array $meches): array
{
    if (!$meches) {
        return [];
    }
    $ids = array_map(fn($m) => (int) $m['id'], $meches);
    $marques = implode(',', array_fill(0, count($ids), '?'));
    $photos = meches_query($pdo, "SELECT * FROM meche_photos WHERE meche_id IN ($marques) ORDER BY ordre ASC, id ASC", $ids)->fetchAll();
    $parMeche = [];
    foreach ($photos as $p) {
        $parMeche[(int) $p['meche_id']][] = $p;
    }
    foreach ($meches as &$m) {
        $m['photos'] = $parMeche[(int) $m['id']] ?? [];
    }
    return $meches;
}

/** Mèches visibles sur le site, avec leurs photos. */
function meches_actives(PDO $pdo): array
{
    $m = meches_query($pdo, "SELECT * FROM meches WHERE actif = 1 ORDER BY ordre ASC, id ASC")->fetchAll();
    return meches_avec_photos($pdo, $m);
}

/** Toutes les mèches (admin), avec leurs photos. */
function meches_toutes(PDO $pdo): array
{
    $m = meches_query($pdo, "SELECT * FROM meches ORDER BY ordre ASC, id ASC")->fetchAll();
    return meches_avec_photos($pdo, $m);
}

function meche_par_id(PDO $pdo, int $id)
{
    $m = meches_query($pdo, "SELECT * FROM meches WHERE id = ?", [$id])->fetch();
    if (!$m) {
        return false;
    }
    return meches_avec_photos($pdo, [$m])[0];
}

/** Texte enregistré avec la réservation, ex. « Mèche naturelle – 15 € ». */
function meche_libelle(array $m): string
{
    return $m['prix'] !== '' ? $m['nom'] . ' – ' . $m['prix'] : $m['nom'];
}

/**
 * Ajoute à la table creneaux les colonnes du choix des mèches si elles manquent
 * (sites déjà en ligne avant cette fonctionnalité).
 */
function creneaux_colonnes_meches(PDO $pdo): void
{
    try {
        $pdo->query("SELECT avec_meches, meche FROM creneaux LIMIT 0");
    } catch (PDOException $e) {
        if ($e->getCode() !== '42S22') { // colonne inconnue
            throw $e;
        }
        $cols = $pdo->query("SHOW COLUMNS FROM creneaux")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('avec_meches', $cols, true)) {
            $pdo->exec("ALTER TABLE creneaux ADD COLUMN avec_meches TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (!in_array('meche', $cols, true)) {
            $pdo->exec("ALTER TABLE creneaux ADD COLUMN meche VARCHAR(255) NULL");
        }
    }
}
