<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/prestations.php';
$pdo = getPDO();
$prestations = prestations_actives($pdo);

// Créneaux disponibles à venir (les créneaux d'aujourd'hui déjà passés sont masqués)
$stmt = $pdo->prepare("SELECT id, date, heure FROM creneaux
                       WHERE disponible = 1
                       AND (date > :today OR (date = :today2 AND heure > :now))
                       ORDER BY date ASC, heure ASC");
$stmt->execute([
    'today'  => date('Y-m-d'),
    'today2' => date('Y-m-d'),
    'now'    => date('H:i:s'),
]);
$creneaux = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>VHair34 - Accueil</title>
<link rel="stylesheet" href="/assets/style.css?v=<?=time()?>">

<style>
/* Logo Insta fixe */
.instagram-logo {
    position: fixed;
    top: 15px;
    left: 15px;
    z-index: 999;
}

.instagram-logo img {
    width: 40px;
    height: 40px;
    border-radius: 12px;
}

/* Animation douce */
.fade-in {
    animation: fadeIn 1s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Message de bienvenue plus élégant */
.welcome-message {
    text-align: center;
    margin-top: 80px;
    padding: 20px;
    border-radius: 12px;
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(4px);
    animation: fadeIn 1.2s ease-in-out;
    font-size: 18px;
}

/* Charte stylée */
.charte {
    margin-top: 40px;
    padding: 20px;
    border-radius: 12px;
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(4px);
    line-height: 1.6;
}

.charte h2 {
    margin-bottom: 15px;
}

/* Slots cachés */
.hidden {
    display: none;
}
</style>

<script>
// Script afficher plus / afficher moins
function toggleCreneaux() {
    const hiddenSlots = document.querySelectorAll('.slot.hidden');
    const btn = document.getElementById('toggleCreneaux');

    hiddenSlots.forEach(el => el.classList.toggle('hidden'));

    btn.textContent = (btn.textContent === 'Afficher plus') ? 'Afficher moins' : 'Afficher plus';
}
</script>

</head>
<body>

<a href="https://www.instagram.com/v.hair_34/" target="_blank" class="instagram-logo">
    <img src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Instagram_icon.png" alt="Instagram">
</a>

<div class="container fade-in">

    <div class="welcome-message">
        <h1>V.Hair_34</h1>
        <p>🌷 Bienvenue chez V.Hair34 ✨, spécialiste en Ponytail, tissages et braids à Montpellier.</p>
        <p>Choisis ton créneau, détends-toi, et laisse-moi sublimer ta beauté 💇🏾‍♀️.</p>
        <p>Je t’attends avec le sourire pour un moment 100 % beauté.</p>
    </div>

    <h1>Prestations</h1>

    <!-- PRESTATIONS (gérées depuis l'admin) -->
    <?php foreach ($prestations as $p): ?>
    <div class="service">
        <h2><?= e($p['nom']) ?><?php if ($p['prix'] !== ''): ?> – <?= e($p['prix']) ?><?php endif; ?></h2>
        <?php if ($p['duree'] !== ''): ?><p><strong>Durée :</strong> <?= e($p['duree']) ?></p><?php endif; ?>
        <?php if ($p['materiel'] !== ''): ?><p><strong>Matériel :</strong> <?= e($p['materiel']) ?></p><?php endif; ?>
        <?php if (trim((string) $p['description']) !== ''): ?><p><?= nl2br(e($p['description'])) ?></p><?php endif; ?>
    </div>
    <?php endforeach; ?>

    <!-- CHARTE -->
    <div class="charte">
        <h2>Charte de fonctionnement – V.HAIR</h2>

        <h3>1. Hygiène & préparation</h3>
        <p>Pour assurer une prestation de qualité, merci de venir avec les cheveux propres, entièrement secs ( sans aucun résidus, gel, pommade, crème , huile …)</p>

        <h3>2. Réservation</h3>
        <p>En réservant, vous vous engagez à honorer votre créneau et à respecter les conditions de la présente charte.
        Toute réservation vaut engagement : merci de respecter votre créneau ou de prévenir en cas d’empêchement.</p>

        <h3>3. Retards</h3>
        <p>Par respect pour le planning, tout retard de plus de 20 minutes entraîne l’annulation du rendez-vous.
        Cela permet de maintenir un service fluide et de garantir le même niveau de qualité pour toutes.
        Je vous invite donc à prévoir un léger temps d’avance.🥰</p>

        <h3>4. Accompagnateurs</h3>
        <p>Pour garantir un environnement calme, confortable et sécurisé aucun accompagnateur n’est autorisé.</p>

        <h3>5. Paiement</h3>
        <ul>
            <li>Espèces (merci de prévoir l’appoint)</li>
            <li>PayPal</li>
        </ul>

        <h3>6. Engagement qualité</h3>
        <p>V.HAIR s’engage à fournir un service professionnel, soigné et personnalisé.
        En retour, nous vous remercions de respecter le cadre de travail et les règles établies afin de garantir la meilleure expérience possible pour toutes.</p>
    </div>

    <!-- CRENEAUX -->
    <h1>Créneaux Disponibles</h1>

    <?php if (!$creneaux): ?>
        <p>Aucun créneau disponible pour le moment. Écris-moi sur Instagram pour en savoir plus 💌</p>
    <?php endif; ?>

    <ul>
        <?php foreach($creneaux as $index => $c): ?>
            <li class="slot <?= $index >= 5 ? 'hidden' : '' ?>">
                <span class="slot-info">
                    <?= e(date_fr($c['date'])) ?> à <?= e(heure_fr($c['heure'])) ?>
                </span>
                <a href="reserver.php?id=<?= (int) $c['id'] ?>" class="button">Réserver</a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if(count($creneaux) > 5): ?>
        <button id="toggleCreneaux" onclick="toggleCreneaux()" class="button">Afficher plus</button>
    <?php endif; ?>

</div>
</body>
</html>
