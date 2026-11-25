# VHAIR34

Site de réservation de créneaux pour V.Hair_34, coiffeuse à Montpellier (ponytails, tissages, braids).

## Stack
- PHP 8 + MySQL (PDO)
- Hébergement : InfinityFree

## Structure
- `htdocs/` : le site en production (à déployer tel quel à la racine de l'hébergement)
  - `index.php` : accueil (design « L'Atelier ») : logo, galerie, carte des prestations, avant de venir, créneaux
  - `meches.php` : page « Nos mèches » (photos, prix, longueurs, coloris)
  - `reserver.php` : réservation, avec l'option « J'apporte mes mèches » / « Avec mèches » et le choix de la mèche
  - `assets/site.css` : styles du site public (mobile first) ; `assets/img/` : photos fixes
  - `admin/` : espace admin (connexion, gestion des créneaux)
  - `includes/helpers.php` : session, échappement HTML, protection CSRF, dates en français
  - `includes/prestations.php` : accès à la table `prestations` (créée et pré-remplie automatiquement au premier chargement)
  - `admin/prestations.php` : la coiffeuse ajoute, modifie, masque ou supprime ses prestations
  - `admin/meches.php` : la coiffeuse gère ses mèches et leurs photos (plusieurs par mèche)
  - `includes/meches.php` : tables `meches` et `meche_photos` (créées automatiquement), colonnes `avec_meches` et `meche` ajoutées à `creneaux` si besoin
  - `includes/images.php` : contrôle des photos envoyées, redimensionnement (1600 px + vignette 480 px), données GPS retirées
  - `includes/layout.php` : menu et pied de page communs
  - `uploads/meches/` : photos envoyées depuis l'admin (non versionnées, `.htaccess` interdit l'exécution de scripts)
- `ancienne-maquette-html/` : premier prototype statique (localStorage), conservé pour référence

## Installation
1. Copier `htdocs/config.example.php` en `htdocs/config.php`
2. Renseigner les identifiants MySQL dans `config.php` (ce fichier n'est jamais versionné)

## Déploiement sur InfinityFree
Envoyer tout le contenu de `htdocs/` dans le dossier `htdocs/` de l'hébergement (FTP ou gestionnaire de fichiers),
**y compris `config.php`** qui n'est que sur ta machine.
Ne jamais écraser le dossier `uploads/meches/` en ligne : il contient les photos envoyées par la coiffeuse.
