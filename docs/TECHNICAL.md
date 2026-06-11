# ⚙️ Documentation Technique - Projet Heart

Cette application est un gestionnaire d'albums photo développé en architecture **MVC (Model-View-Controller)**, respectant les principes de la **Programmation Orientée Objet (POO)**.

## 1. Architecture du Projet

Le projet suit une séparation stricte des responsabilités :

- **Controllers (`/controllers`)** : Reçoivent les requêtes, valident les entrées et dirigent les modèles.
- **Models (`/models`)** : Représentent les entités (ex: `Album`, `Photo`, `User`).
- **Managers (`/managers`)** : Couche d'accès aux données gérant les requêtes SQL.
- **Views (`/views`)** : Templates `.phtml` pour le rendu côté client.
- **Core (`/core`)** : Contient le routeur et la connexion PDO à la base de données.

## 2. Pile Technologique

- **Backend** : PHP 8+ avec typage strict.
- **Frontend** : JavaScript ES6+, CSS3 (Flexbox/Grid), Responsive Design, HTML5.
- **Base de données** : MySQL.

## 3. Sécurité

La sécurité est une priorité du projet :

- **Authentification** : Utilisation de l'algorithme **Argon2id** (`password_hash`) pour le stockage des mots de passe.
- **Protection SQL** : Utilisation systématique des **requêtes préparées (PDO)** pour prévenir toute injection SQL.
- **Sessions** : Gestion sécurisée des sessions utilisateur (`session_start` et vérification des droits via `AuthMiddleware`).
- **Nettoyage des données** : Utilisation de `htmlspecialchars()` pour contrer les failles XSS lors de l'affichage des données utilisateurs.

## 4. Modèle de Données (Bases)

La base de données repose sur une structure relationnelle :

- **users** : ID, email, password_hash, username.
- **albums** : ID, user_id, title, description, visibility, created_at.
- **photos** : ID, album_id, filename, description.
- **tags** : ID, name.
- **album_tags / photo_tags** : Tables de jointure pour la relation N-M.

## 5. Routage

Le système de routage centralisé dans `public/index.php` utilise un `Router` qui mappe les URLs (ex: `/albums/update`) aux méthodes des contrôleurs.

- _Exemple de flux_ : Requête `POST` -> `Router` -> `AlbumController::update()` -> `AlbumManager::updateAlbumInfo()` -> `MySQL`.

_Projet Heart - Architecture développée en 2026_
