# 🛠️ Documentation Technique & Architecture - Application Loove

Cette documentation technique présente l'architecture logicielle, la structure de la base de données, la conception des API REST et les choix d'implémentation réalisés pour le développement de l'application **Loove**.

---

## 📋 Table des Matières
1. [Architecture Globale du Projet](#1-architecture-globale-du-projet)
2. [Technologies et Dépendances](#2-technologies-et-dépendances)
3. [Structure des Fichiers et Dossiers](#3-structure-des-fichiers-et-dossiers)
4. [Base de Données et Schéma Relationnel](#4-base-de-données-et-schéma-relationnel)
5. [Routing et Architecture API Backend](#5-routing-et-architecture-api-backend)
6. [Frontend et Client Web](#6-frontend-et-client-web)
7. [Sécurité, Sessions et Robustesse](#7-sécurité-sessions-et-robustesse)

---

## 1. Architecture Globale du Projet

L'application **Loove** repose sur une architecture découplée orientée **MVC (Modèle-Vue-Contrôleur)** léger en PHP natif pour le Backend, et un client SPA (Single Page Application multi-vues) en HTML5 / CSS3 / JavaScript Vanilla côté Frontend.

```
+-------------------------------------------------------+
|                    CLIENT (Frontend)                  |
|  HTML5 / Vanilla JS (Fetch API) / Vanilla CSS         |
+-------------------------------------------------------+
                           |
                     HTTP JSON (REST)
                           |
+-------------------------------------------------------+
|                    SERVEUR (Backend)                  |
|  index.php (Front Controller & Router)                |
|  Controllers (Match, Message, User, Admin, etc.)      |
|  Models (User, MatchModel, MessageModel, etc.)        |
+-------------------------------------------------------+
                           |
                         PDO SQL
                           |
+-------------------------------------------------------+
|                 BASE DE DONNÉES (MySQL)              |
|  Tables: users, user_photos, matches, messages...     |
+-------------------------------------------------------+
```

---

## 2. Technologies et Dépendances

- **Environnement Serveur** : Apache / PHP 8.x (Compatible XAMPP, WAMP, LAMP).
- **Base de Données** : MySQL 8.0 / MariaDB avec moteur d'encodage `utf8mb4_unicode_ci`.
- **Backend PHP** :
  - Programmation Orientée Objet (POO).
  - Extension `PDO` pour les interactions sécurisées avec la base de données.
  - Autoloading dynamique des classes.
- **Frontend Web** :
  - HTML5 sémantique.
  - CSS3 avec Variables CSS, Flexbox & Grid moderne.
  - JavaScript ES6+ asynchrone (`async/await`, `fetch`).

---

## 3. Structure des Fichiers et Dossiers

```
app-loove/
├── backend/
│   ├── config/             # Configuration DB & Utilitaires (Database.php, Logger.php)
│   ├── controllers/        # Contrôleurs métier (UserController, MatchController, etc.)
│   ├── models/             # Modèles d'accès aux données (User.php, MessageModel.php, etc.)
│   ├── uploads/            # Fichiers médias & photos de profil téléversées
│   ├── db-schema.sql       # Script SQL d'initialisation de la base de données
│   ├── index.php           # Routeur principal (Front Controller)
│   ├── seed.php            # Script de seeding (données de test)
│   └── diagnostic.php      # Script de vérification et diagnostic système
├── frontend/
│   ├── public/             # Pages HTML (dashboard.html, login.html, profile.html, etc.)
│   └── src/                # Scripts JS dynamiques & configurations client (config.js)
├── DOC_UTILISATEUR.md      # Documentation utilisateur
├── DOC_TECHNIQUE.md        # Présente documentation technique
├── SUPPORT_SOUTENANCE.md   # Support pour le jury de soutenance
└── README.md               # Guide de démarrage rapide du projet
```

---

## 4. Base de Données et Schéma Relationnel

La base de données relationnelle est conçue pour optimiser les requêtes d'appariement et de messagerie.

### Principales Tables :
- **`users`** : Stocke les profils utilisateurs (id, nom, email, password_hash, genre, bio, role, status_premium).
- **`user_photos`** : Galerie photo liée aux utilisateurs (`user_id`, `filename`, `is_primary`).
- **`matches`** : Enregistre les interactions de Like (`user_id`, `target_id`, `status` [like/dislike/match]).
- **`messages`** : Échanges de messagerie instantanée (`sender_id`, `receiver_id`, `content`, `created_at`, `is_read`).
- **`visits`** : Traçabilité des visites de profil (`visitor_id`, `visited_id`, `visited_at`).
- **`reports`** : Signalements pour la modération (`reporter_id`, `reported_id`, `reason`, `status`).

---

## 5. Routing et Architecture API Backend

Le fichier [backend/index.php](file:///c:/xampp/htdocs/app-loove/backend/index.php) agit comme un **Front Controller**. Il intercepte toutes les requêtes, configure les en-têtes HTTP/CORS et aiguille le traitement vers le contrôleur approprié en fonction de la variable `action` passée en paramètre GET.

### Exemple de Routing API :
- `GET /backend/index.php?action=users` : Récupération des profils à découvrir ([UserController.php](file:///c:/xampp/htdocs/app-loove/backend/controllers/UserController.php)).
- `POST /backend/index.php?action=like` : Envoi d'un like et vérification de match ([MatchController.php](file:///c:/xampp/htdocs/app-loove/backend/controllers/MatchController.php)).
- `GET/POST /backend/index.php?action=messages` : Gestion des échanges de messages ([MessageController.php](file:///c:/xampp/htdocs/app-loove/backend/controllers/MessageController.php)).
- `POST /backend/index.php?action=upload_photo` : Téléversement et enregistrement de photos ([PhotoController.php](file:///c:/xampp/htdocs/app-loove/backend/controllers/PhotoController.php)).

---

## 6. Frontend et Client Web

Le client web communique exclusivement avec l'API backend via l'API native `fetch()`.
- **Modularité JS** : Les fichiers de logique client récupèrent dynamiquement les configurations réseaux depuis `frontend/src/config.js`.
- **Rendu dynamique** : Injection DOM asynchrone des profils, des messages et des cartes sans rechargement de page.
- **Responsive Design** : Interface adaptée pour ordinateurs, tablettes et smartphones.

---

## 7. Sécurité, Sessions et Robustesse

- **Authentification & Sessions** : Utilisation des sessions PHP sécurisées (`session_start()`). En-têtes `Access-Control-Allow-Credentials: true` configurés pour assurer la gestion de session multi-origine sur réseau local et mobile.
- **Protection des mots de passe** : Hachage fort via l'algorithme `password_hash()` (BCrypt).
- **Protection SQL** : Utilisation systématique de requêtes préparées PDO avec liaison de paramètres (`bindValue` / `bindParam`) pour éliminer tout risque d'injection SQL.
- **Gestion Globale des Erreurs** : Mise en place d'un `set_exception_handler` global enregistrant les erreurs critiques dans des fichiers de log via la classe `Logger` tout en renvoyant des réponses JSON 500 propres au client.
