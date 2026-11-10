<?php
require_once __DIR__ . '/config.php';

// Afficher les erreurs pour debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pdo = getPDO();

// Récupérer l'ID du créneau
$id = $_GET['id'] ?? null;
if (!$id) {
    die("Créneau invalide.");
}

// Récupérer le créneau
$stmt = $pdo->prepare("SELECT * FROM creneaux WHERE id = ?");
$stmt->execute([$id]);
$creneau = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$creneau) {
    die("Créneau introuvable.");
}

// Liste des prestations
$prestations = [
    "PONYTAIL SIGNATURE CLASSIC 45€ ( haut , bas , milieu )",
    "Ponytail Middle part 45€",
    "Ponytail Frange Signature 50€",
    "Ponytail V Part Signature 50€ + Franges, boucles 5€",
    "Ponytail Demi tête ( simple ) - 50€ + V Part 5€ + V Part et frange 10€",
    "Ponytail demi tête carré 55€( Barbie , bouclés )",
    "Ponytail Bun / Tresse - 40€",
    "TISSAGES SEW IN 50€ + 10€ styling ( layers , boucles )",
    "Braids ( Knotless , BOHO braids ,fulani …) à partir de 40€ sur devis : écrivez moi en DM",
    "Tissage quickweave : 60€ + 10€ styling ( boucles , layers )"
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $prestation = $_POST['prestation'] ?? '';
    $prestation_libre = $_POST['prestation_libre'] ?? '';
    $final_prestation = $prestation_libre ?: $prestation;

    if (empty($nom) || empty($telephone) || empty($final_prestation)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE creneaux SET nom_client = ?, telephone = ?, prestation = ?, disponible = 0 WHERE id = ?");
            $stmt->execute([$nom, $telephone, $final_prestation, $id]);
            $success = "Réservation enregistrée ! Merci, $nom.";
        } catch (Exception $e) {
            $error = "Erreur lors de la réservation : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Réserver le créneau</title>
<link rel="stylesheet" href="/assets/style.css?v=<?=time()?>">
</head>
<body>
<div class="container">
    <h1>Réserver un créneau</h1>

    <?php if(!empty($error)): ?>
        <p class="error-message"><?= $error ?></p>
    <?php endif; ?>

   <?php if(!empty($success)): ?>
    <div class="success-overlay">
        <div class="success-pop-up fade-in">
            <div class="pop-up-icon">✅</div>
            <h2>Réservation confirmée !</h2>
            <p>Merci <strong><?= htmlspecialchars($nom) ?></strong>, ton créneau est bien enregistré.</p>
            
            <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.2); margin: 20px 0;">
            
            <p><strong>⚠️ ÉTAPE IMPORTANTE :</strong></p>
            <p>Pour valider définitivement ton rendez-vous et discuter des détails, envoie-moi un message dès maintenant :</p>
            
            <a href="https://www.instagram.com/v.hair_34/" target="_blank" class="insta-confirm-button">
                Envoyer un DM sur Instagram
            </a>
            
            <p style="font-size: 0.8em; margin-top: 15px; opacity: 0.8;">
                (Précise ton nom et la prestation choisie)
            </p>
        </div>
    </div>
<?php endif; ?>

    <div class="service">
        <h2>Créneau sélectionné</h2>
        <p><strong>Date :</strong> <?= $creneau['date'] ?></p>
        <p><strong>Heure :</strong> <?= $creneau['heure'] ?></p>
    </div>

    <div class="service">
        <h2>Vos informations et prestation</h2>
        <form method="post" class="reservation-form">
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="tel" name="telephone" placeholder="Numéro de téléphone" required>

            <label for="prestation">Choisissez votre prestation :</label>
            <select name="prestation" id="prestation">
                <option value="">Sélectionnez votre prestation</option>
                <?php foreach($prestations as $p): ?>
                    <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="prestation_libre">Ou autre prestation :</label>
            <input type="text" name="prestation_libre" id="prestation_libre" placeholder="Décrivez votre prestation">

            <button type="submit" class="button">Réserver</button>
        </form>
    </div>
</div>
</body>
</html>
