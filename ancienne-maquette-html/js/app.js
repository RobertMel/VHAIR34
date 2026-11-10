// --- Gestion du stockage local ---
function getCreneaux() {
  return JSON.parse(localStorage.getItem("creneaux") || "[]");
}

function saveCreneaux(creneaux) {
  localStorage.setItem("creneaux", JSON.stringify(creneaux));
}

// --- AFFICHAGE SUR index.html ---
const list = document.getElementById("creneaux-list");
if (list) {
  const creneaux = getCreneaux();

  if (creneaux.length === 0) {
    list.innerHTML = "<p>Aucun créneau pour l’instant.</p>";
  } else {
    creneaux.forEach(c => {
      const li = document.createElement("li");
      li.textContent = `${c.date} à ${c.heure} - ${c.disponible ? "Disponible" : "Indisponible"}`;

      if (c.disponible) {
        const a = document.createElement("a");
        a.href = "reserver.html?id=" + c.id;
        a.textContent = " Réserver";
        li.appendChild(a);
      }

      list.appendChild(li);
    });
  }
}

// --- AJOUT D’UN CRENEAU VIA admin.html ---
const formAdd = document.getElementById("add-creneau-form");
if (formAdd) {
  formAdd.addEventListener("submit", (e) => {
    e.preventDefault();

    const date = formAdd.date.value;
    const heure = formAdd.heure.value;
    const disponible = formAdd.disponible.checked;

    const creneaux = getCreneaux();

    creneaux.push({
      id: Date.now(), // ID unique
      date,
      heure,
      disponible
    });

    saveCreneaux(creneaux);

    alert("Créneau ajouté !");
    formAdd.reset();
  });
}

// --- FORMULAIRE DE RESERVATION ---
const formReserve = document.getElementById("reserver-form");
if (formReserve) {
  formReserve.addEventListener("submit", (e) => {
    e.preventDefault();
    alert("Réservation enregistrée !");
    formReserve.reset();
  });
}
