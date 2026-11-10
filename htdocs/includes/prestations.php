<?php
// Prestations stockées en base, gérées depuis l'admin (admin/prestations.php).
// La table est créée et pré-remplie automatiquement au premier chargement.

const PRESTATIONS_CATEGORIES = ['Ponytails', 'Tissages', 'Braids', 'Autre'];

function prestations_install(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS prestations (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        categorie VARCHAR(50) NOT NULL DEFAULT '',
        nom VARCHAR(150) NOT NULL,
        prix VARCHAR(80) NOT NULL DEFAULT '',
        duree VARCHAR(50) NOT NULL DEFAULT '',
        materiel VARCHAR(255) NOT NULL DEFAULT '',
        description TEXT NULL,
        ordre INT NOT NULL DEFAULT 0,
        actif TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if ((int) $pdo->query("SELECT COUNT(*) FROM prestations")->fetchColumn() > 0) {
        return;
    }

    $propres = "Les cheveux doivent être propres ! Sans aucune matière grasse, ni crème, huile, pommade…";
    $initiales = [
        ['Ponytails', 'Ponytail Signature Classic (haut, bas, milieu)', '45 €', '2h30', '2 paquets de tissage : mèche naturelle ou semi-naturelle', $propres],
        ['Ponytails', 'Ponytail Middle Part', '45 €', '2h30', '2 paquets de tissage : mèches naturelles ou semi-naturelles', $propres],
        ['Ponytails', 'Ponytail Frange Signature', '50 €', '3h', '2 paquets de tissage : mèches naturelles ou semi-naturelles', $propres],
        ['Ponytails', 'Ponytail V Part Signature', '50 € (+5 € franges, boucles)', '2h30', '2 paquets de tissage', $propres],
        ['Ponytails', 'Ponytail Demi-tête (simple)', '50 € (+5 € V Part, +10 € V Part et frange)', '3h', '3 paquets de tissage : mèche naturelle ou semi-naturelle', $propres],
        ['Ponytails', 'Ponytail Demi-tête carré (Barbie, bouclés)', '55 €', '3h', '3 paquets de mèches naturelles ou semi-naturelles', $propres],
        ['Ponytails', 'Ponytail Bun / Tresse', '40 €', '2h30', '1 ou 2 paquets de mèches Xpression', $propres],
        ['Tissages', 'Tissage ouvert classique sew-in', '50 € (+10 € styling layers, boucles)', '', '', 'Demande spécifique selon la coiffure.'],
        ['Braids', 'Braids (knotless, boho braids, fulani…)', 'À partir de 40 €, sur devis', '', '', 'Écris-moi en DM sur Instagram pour un devis.'],
        ['Tissages', 'Tissage quick weave', '60 € (+10 € styling boucles, layers)', '', '3 paquets de raw hair, virgin hair ou Remy hair (non inclus)',
            "Le quick weave est une technique de pose américaine où les tissages sont fixés sur une base protégée pour un rendu plat, pratique et durable. Deux choix de raie : milieu ou sur le côté. Le stylisme est en supplément.\nMerci de venir avec les cheveux propres, démêlés, secs (tirés), sans aucun produit appliqué. La coupe des pointes est vivement recommandée."],
    ];

    $stmt = $pdo->prepare("INSERT INTO prestations (categorie, nom, prix, duree, materiel, description, ordre) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($initiales as $i => $p) {
        $stmt->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $p[5], ($i + 1) * 10]);
    }
}

/** Exécute une requête sur la table prestations, en la créant si elle n'existe pas encore. */
function prestations_query(PDO $pdo, string $sql, array $params = []): PDOStatement
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        if ($e->getCode() !== '42S02') { // 42S02 = table inexistante
            throw $e;
        }
        prestations_install($pdo);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

/** Prestations visibles sur le site, dans l'ordre d'affichage. */
function prestations_actives(PDO $pdo): array
{
    return prestations_query($pdo, "SELECT * FROM prestations WHERE actif = 1 ORDER BY ordre ASC, id ASC")->fetchAll();
}

/** Toutes les prestations, y compris masquées (pour l'admin). */
function prestations_toutes(PDO $pdo): array
{
    return prestations_query($pdo, "SELECT * FROM prestations ORDER BY ordre ASC, id ASC")->fetchAll();
}

function prestation_par_id(PDO $pdo, int $id)
{
    return prestations_query($pdo, "SELECT * FROM prestations WHERE id = ?", [$id])->fetch();
}

/** Texte enregistré avec la réservation, ex. « Ponytail Middle Part – 45 € ». */
function prestation_libelle(array $p): string
{
    return $p['prix'] !== '' ? $p['nom'] . ' – ' . $p['prix'] : $p['nom'];
}
