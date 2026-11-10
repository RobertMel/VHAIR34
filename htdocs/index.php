<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
$pdo = getPDO();

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

    <!-- PRESTATIONS (inchangées, juste remises proprement) -->
    <div class="service">
        <h2>PONYTAIL SIGNATURE CLASSIC 45€ ( haut , bas , milieu )</h2>
        <p><strong>Durée :</strong> 2h30mn</p>
        <p><strong>Matériel :</strong> 2 paquets de tissage : Mèche naturelle, Mèche semi naturelle</p>
        <p><strong>Instructions :</strong> LES CHEVEUX DOIVENT ÊTRE PROPRES ! Sans aucune matière grasse , ni crème, huile , pommade…</p>
    </div>

    <div class="service">
        <h2>Ponytail Middle part 45€</h2>
        <p><strong>Durée :</strong> 2h30</p>
        <p><strong>Matériel :</strong> 2 paquets de tissages : mèches naturelles ou semi naturel</p>
        <p><strong>Instructions :</strong> LES CHEVEUX DOIVENT ÊTRE PROPRES ! Sans aucune matière grasse , ni crème, huile , pommade…</p>
    </div>

    <div class="service">
        <h2>Ponytail Frange Signature 50€</h2>
        <p><strong>Durée :</strong> 3h00</p>
        <p><strong>Matériel :</strong> 2 paquets de tissages : mèches naturelles ou semi naturelles</p>
        <p><strong>Instructions :</strong> LES CHEVEUX DOIVENT ÊTRE PROPRES ! Sans aucune matière grasse , ni crème, huile , pommade…</p>
    </div>

    <div class="service">
        <h2>Ponytail V Part Signature 50€ + Franges, boucles 5€</h2>
        <p><strong>Durée :</strong> 2h30</p>
        <p><strong>Matériel :</strong> 2 paquets de tissages</p>
        <p><strong>Instructions :</strong> LES CHEVEUX DOIVENT ÊTRE PROPRES ! Sans aucune matière grasse , ni crème, huile , pommade…</p>
    </div>

    <div class="service">
        <h2>Ponytail Demi tête ( simple ) - 50€ + V Part 5€ + V Part et frange 10€</h2>
        <p><strong>Durée :</strong> 3h</p>
        <p><strong>Matériel :</strong> 3 paquets de tissage : Mèche naturelle, Mèche semi naturelle</p>
        <p><strong>Instructions :</strong> LES CHEVEUX DOIVENT ÊTRE PROPRES ! Sans aucune matière grasse , ni crème, huile , pommade…</p>
    </div>

    <div class="service">
        <h2>Ponytail demi tête carré 55€( Barbie , bouclés )</h2>
        <p><strong>Durée :</strong> 3h00</p>
        <p><strong>Matériel :</strong> 3 paquets de mèches naturelles ou semi naturelle</p>
        <p><strong>Instructions :</strong> LES CHEVEUX DOIVENT ÊTRE PROPRES ! Sans aucune matière grasse , ni crème, huile , pommade…</p>
    </div>

    <div class="service">
        <h2>Ponytail Bun / Tresse - 40€</h2>
        <p><strong>Durée :</strong> 2h30</p>
        <p><strong>Matériel :</strong> 1 ou 2 paquet de mèche Xpression</p>
        <p><strong>Instructions :</strong> LES CHEVEUX DOIVENT ÊTRE PROPRES ! Sans aucune matière grasse , ni crème, huile , pommade…</p>
    </div>

    <div class="service">
        <h2>Tissage ouvert classique sew in 50€ + 10€ styling ( layers , boucles )</h2>
        <p>Demande spécifique selon la coiffure.</p>
    </div>

    <div class="service">
        <h2>Braids ( Knotless , BOHO braids ,fulani …) à partir de 40€ sur devis : écrivez moi en DM</h2>
    </div>

    <div class="service">
        <h2>Tissage quickweave : 60€ + 10€ styling ( boucles , layers )</h2>
        <p>Le quick weave est une technique de pose americaine où les tissages sont fixés sur une base protégée pour un rendu plat, pratique et durable. Il y a deux choix de raie : milieu ou sur le coté. Le stylisme est en supplément.</p>
        <p>Coiffure réalisée avec 3 paquets de raw hair , virgin hair, Remy hair (non inclus). Merci de venir avec les cheveux propres, démêlés, secs (tirés), sans aucun produit appliqué. La coupe des pointes est vivement recommandée.</p>
    </div>

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
