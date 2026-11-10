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
- `ancienne-maquette-html/` : premier prototype statique (localStorage), conservé pour référence

## Installation
1. Copier `htdocs/config.example.php` en `htdocs/config.php`
2. Renseigner les identifiants MySQL dans `config.php` (ce fichier n'est jamais versionné)
