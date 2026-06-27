# 💖 App-Loove - Application de Rencontre en Ligne

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1.svg)](https://www.mysql.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E.svg)](https://developer.mozilla.org/fr/docs/Web/JavaScript)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

**Loove** est une application web moderne et responsive de rencontre en ligne basée sur une architecture MVC propre en PHP natif (POO / PDO) et un client dynamique JavaScript Vanilla.

---

## 📚 Documentations & Livrables du Projet

L'ensemble des livrables exigés pour l'évaluation et la soutenance du projet sont disponibles ci-dessous :

- 📖 **[Documentation Utilisateur](DOC_UTILISATEUR.md)** : Guide complet sur le fonctionnement de l'application, l'inscription, le matchmaking, la messagerie et l'administration.
- 🛠️ **[Documentation Technique](DOC_TECHNIQUE.md)** : Spécifications architecturales MVC, schéma de la base de données, fonctionnement de l'API REST, sécurité et gestion des logs.
- 🎓 **[Support de Présentation & Soutenance](SUPPORT_SOUTENANCE.md)** : Trame détaillée des diapositives, guide de démonstration en direct (Live Demo) et FAQ des questions du jury.

---

## 🚀 Installation & Démarrage Rapide

### 1. Prérequis
- Un serveur web local avec PHP 8.x et MySQL (ex: **XAMPP**, WAMP ou MAMP).
- Un navigateur web moderne.

### 2. Installation de la Base de Données
1. Ouvrez votre gestionnaire MySQL (HeidiSQL, phpMyAdmin, etc.).
2. Créez une nouvelle base de données nommée `app-loove`.
3. Exécutez le script SQL d'initialisation situé dans : `backend/db-schema.sql`.
4. *(Optionnel)* Exécutez le script `backend/seed.php` pour insérer des jeux de données de démonstration.

### 3. Lancement de l'application
1. Placez le dossier `app-loove` dans le répertoire Web de votre serveur (ex: `c:/xampp/htdocs/app-loove`).
2. Ouvrez votre navigateur et accédez à l'URL :
   `http://localhost/app-loove/frontend/public/index.html`

---

## 🔒 Sécurité & Modération
- Mots de passe sécurisés via `password_hash()` (BCrypt).
- Protection contre les injections SQL grâce aux requêtes préparées PDO.
- Espace dédié à la modération et au traitement des signalements pour les administrateurs.
