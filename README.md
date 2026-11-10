# VHAIR34

Site de réservation de créneaux pour V.Hair_34, coiffeuse à Montpellier (ponytails, tissages, braids).

## Stack
- PHP 8 + MySQL (PDO)
- Hébergement : InfinityFree

## Structure
- `htdocs/` : le site en production (à déployer tel quel à la racine de l'hébergement)
  - `index.php` : accueil, prestations, charte, créneaux disponibles
  - `reserver.php` : formulaire de réservation
  - `admin/` : espace admin (connexion, gestion des créneaux)
  - `includes/helpers.php` : session, échappement HTML, protection CSRF, dates en français
  - `includes/prestations.php` : accès à la table `prestations` (créée et pré-remplie automatiquement au premier chargement)
  - `admin/prestations.php` : la coiffeuse ajoute, modifie, masque ou supprime ses prestations
- `ancienne-maquette-html/` : premier prototype statique (localStorage), conservé pour référence

## Installation
1. Copier `htdocs/config.example.php` en `htdocs/config.php`
2. Renseigner les identifiants MySQL dans `config.php` (ce fichier n'est jamais versionné)

## Déploiement sur InfinityFree
Envoyer tout le contenu de `htdocs/` dans le dossier `htdocs/` de l'hébergement (FTP ou gestionnaire de fichiers),
**y compris `config.php`** qui n'est que sur ta machine.
