# Heart - Plateforme de Partage d'Albums Photo

Heart est une application web permettant aux utilisateurs de créer, organiser et partager des albums photo en ligne. Conçue avec une architecture MVC, elle offre une expérience utilisateur fluide et responsive pour la gestion des albums, avec un contrôle précis sur la confidentialité et la collaboration.

## Fonctionnalités

### Authentification

- Inscription et connexion sécurisées (mot de passe hachés avec ARGON2).
- Gestion des profils et des sessions utilisateurs.

### Gestion des Albums

- Création, modification et suppression d'albums.
- Édition centralisée et fluide (le titre, la description, la visibilité, les tags et les photos sont gérés depuis une même interface).
- Organisation via un système d'étiquettes personnalisées.

### Gestion des Photos

- Upload de fichiers optimisé avec gestion des formats (JPG, PNG, WEBP) et limites de taille (2MO).
- Chaque photo possède sa propre visibilité, sa date, sa description et ses propres étiquettes.
- Affichage sous forme de grille dynamique qui s'adapte à la taille de l'écran.
- Modales interactives pour la gestion rapide des tags et des accès par photo.

### Partage et Droits d'Accès

- 3 niveaux de visibilité au choix : **Public**, **Privé**, et **Restreint**.
- Génération de liens de partage directs.
- Interface de gestion des invités : invitation d'utilisateurs spécifiques.

### Interactions et Recherche

- Espace de discussion : ajout, modification et suppression de commentaires sur les photos.
- Filtrage dynamique : recherche et tri instantanés des photos au sein d'un album grâce aux tags associés.

## Architecture et Technologies

Le projet respecte les principes de la Programmation Orientée Objet (POO) de bout en bout, avec une architecture Model-View-Controller (MVC).

- **Backend : PHP (Natif)**
  - Routage dynamique capturant les URL pour distribuer les requêtes aux contrôleurs appropriés.
  - Architecture solide séparant logiques métiers (Managers), structures de données (Models) et vues (View).
  - Convention de code stricte privilégiant des noms de variables réels, descriptifs et explicites pour une maintenance aisée.
  - Système centralisé de gestion des erreurs et de logs PHP.
- **Frontend : HTML5, CSS3, JavaScript (Vanilla)**
  - Interface 100% responsive adaptée aux mobiles, tablettes et ordinateurs via des Media Queries.
  - Code modulaire en JavaScript orienté objet. L'interface d'édition, par exemple, est pilotée par la classe `AlbumEditView` qui orchestre la logique des évènements liés à l'upload et les changements dynamiques de visibilité.
  - Structure sémantique respectant les standards W3C.
- **Base de données : MySQL**
  - Base de données relationnelle optimisée.
  - Sécurité assurée par l'utilisation exclusive de requêtes préparées (PDO) contre les injections SQL.

## Installation et Déploiement

1. **Cloner le dépôt :**
   ```bash
   git clone [https://gitlab.com/votre-nom/app-photo-album.git](https://gitlab.com/votre-nom/app-photo-album.git)
   cd app-photo-album
   ```

## Documentation

- [Guide d'utilisation](docs/USER.md)
- [Documentation technique](docs/TECHNICAL.md)
