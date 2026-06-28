# 🎓 Support et Trame de Soutenance - Projet Loove
**Durée totale de la soutenance** : 30 minutes (15 min Présentation + 10 min Q&R + 5 min Délibération)

Ce document est votre guide complet pour réussir votre soutenance de fin d'année devant le jury. Il respecte à la lettre les consignes transmises et détaille ce qu'il faut dire et faire minute par minute.

---

## ⏱️ Découpage Temporel Global

| Phase | Durée | Objectif Principal |
| :--- | :--- | :--- |
| **1. Soutenance & Démo** | **15 min** | Exposer le projet, les choix techniques, la méthodologie et faire la démo en direct. |
| **2. Questions / Réponses** | **10 min** | Justifier vos choix, prouver votre maîtrise technique et expliquer la gestion des obstacles. |
| **3. Délibération & Débrief** | **5 min** | Sortie de la salle pendant la délibération du jury, puis retour pour la note et le feedback. |

---

# 🎤 Phase 1 : Soutenance et Démonstration (15 min)

---

### 1. Introduction Générale du Projet (⏱️ 3 minutes)

#### 🎙️ Ce que vous devez dire (Script oral) :
> *"Bonjour à toutes et à tous, membres du jury. Je suis ravi de vous présenter aujourd'hui mon projet de fin d'année intitulé **Loove**.*
> 
> *Dans le cadre des sujets proposés, j'ai choisi de développer une application web dynamique et interactive de mise en relation et d'échanges (plateforme de rencontre / réseau social thématique). Mon objectif principal était de concevoir une application complète, moderne, fluide et sécurisée, capable d'offrir une expérience utilisateur intuitive aussi bien sur ordinateur que sur mobile.*
> 
> *Les enjeux majeurs de ce projet étaient triples :*
> 1. **Prono-technique** : Mettre en place une architecture découplée avec une API REST performante.
> 2. **Ergonomique** : Offrir une interface Single Page Application (SPA) dynamique sans rechargement de page intempestif.
> 3. **Sécuritaire** : Garantir la confidentialité des données, l'authentification robuste (mots de passe hachés, vérification OTP) et la modération des contenus."*

---

### 2. Description Technique et Choix d'Architecture (⏱️ 4 minutes)

#### 💡 Justification des choix technologiques :

* **Backend : PHP 8.x en Programmation Orientée Objet (POO) & PDO**
  * **Pourquoi ?** PHP est idéal pour la gestion des données côté serveur et la création rapide d'une API REST. L'utilisation de la POO permet une meilleure structuration du code (maintenabilité).
  * **PDO (PHP Data Objects)** : Choisi pour l'interaction avec la base de données MySQL via des requêtes préparées, éliminant 100% des risques d'injections SQL.
* **Frontend : JavaScript Vanilla (ES6+) & HTML5 / CSS3 Moderne**
  * **Pourquoi JavaScript Vanilla ?** Pour maîtriser pleinement le DOM, les requêtes asynchrones via l'API `fetch()`, et éviter la lourdeur ou la complexité d'un framework externe pour ce projet. Cela prouve ma maîtrise des fondamentaux du web.
  * **HTML5 & CSS3** : Utilisation de balises sémantiques, de CSS Grid, Flexbox et de variables CSS pour garantir un design moderne, réactif (responsive design) et des animations fluides.
* **Base de Données : MySQL 8.0 (MariaDB)**
  * **Pourquoi ?** Un système relationnel robuste adapté aux relations complexes (utilisateurs, photos, likes, matches, messages, signalements).

#### 🏗️ Architecture Globale (Modèle MVC Découplé) :
Expliquez la structure découplée :
```
[ Client (HTML/CSS/JS Vanilla) ]  <--- HTTP JSON (API REST) --->  [ Front Controller (backend/index.php) ]
                                                                          |
                                                                   [ Contrôleurs & Modèles PHP ]
                                                                          |
                                                                   [ Base MySQL (PDO) ]
```

---

### 3. Méthodologie de Développement (⏱️ 3 minutes)

#### 🔄 Étapes et Organisation du Travail :
1. **Conception & Modélisation** : Création du schéma relationnel SQL (tables `users`, `matches`, `messages`, `reports`, `visits`).
2. **Développement Backend (API First)** : Implémentation du Front Controller (`backend/index.php`), des classes modèles et des contrôleurs métier.
3. **Développement Frontend** : Structuration des pages HTML, intégration du style CSS et écriture des modules JS pour l'interactivité.
4. **Tests & Modération** : Réalisation de scripts de diagnostic et de validation de syntaxe (47 fichiers validés sans erreur).

#### 🎨 Interface Utilisateur & Intégration :
* Ergonomie pensée pour l'utilisateur : tableau de bord (swipe/découverte), espace de messagerie en direct, gestion du profil et galerie photo, espace Premium et modération Admin.

---

### 4. Résultats Obtenus & Démonstration en Direct (⏱️ 5 minutes)

#### 📊 Résultats Concrets :
* **Qualité de code** : 47 fichiers (PHP, JS, HTML, CSS, SQL) validés et exempts d'erreurs de syntaxe.
* **Fonctionnalités opérationnelles** : Inscription avec code de vérification à 6 chiffres (OTP), connexion sécurisée, système de likes/matches, messagerie instantanée, téléversement de photos et panneau d'administration.

---

#### 🎬 Scénario de Démonstration en Live devant le Jury :

*(Préparez vos onglets à l'avance sur le navigateur !)*

1. **Étape 1 : Accueil & Inscription / Connexion**
   * Présentez la page de connexion ([login.html](file:///c:/xampp/htdocs/app-loove/frontend/public/login.html)). Connectez-vous avec un compte de test ou simulez une inscription montrant la vérification du code OTP.
2. **Étape 2 : Découverte & Système de Match (Dashboard)**
   * Allez sur [dashboard.html](file:///c:/xampp/htdocs/app-loove/frontend/public/dashboard.html). Montrez le défilement des profils. Effectuez un **Like** sur un profil. Expliquez que lorsque deux utilisateurs se likent mutuellement, un **Match** est instantanément créé en BDD.
3. **Étape 3 : Messagerie Instantanée**
   * Ouvrez l'espace [messages.html](file:///c:/xampp/htdocs/app-loove/frontend/public/messages.html). Montrez la discussion entre profils matchés. Envoyez un message en direct pour prouver l'injection dynamique en JS sans rechargement de page.
4. **Étape 4 : Profil & Téléversement de Photos**
   * Rendez-vous sur [profile.html](file:///c:/xampp/htdocs/app-loove/frontend/public/profile.html). Montrez la gestion des informations et la galerie photo alimentée par le contrôleur `PhotoController.php`.
5. **Étape 5 : Espace Premium & Administration**
   * Montrez rapidement la vue [premium.html](file:///c:/xampp/htdocs/app-loove/frontend/public/premium.html) (fonctionnalités exclusives) et l'interface [admin.html](file:///c:/xampp/htdocs/app-loove/frontend/public/admin.html) pour la modération des signalements.

---

# ❓ Phase 2 : Questions et Réponses (10 min)

Le jury va chercher à tester votre compréhension profonde et la gestion des obstacles. Voici les questions les plus probables et comment y répondre :

### ❓ Q1 : Pourquoi avoir choisi du PHP natif au lieu d'un framework comme Laravel ou Symfony ?
> **💡 Réponse modèle** : *"J'ai délibérément choisi PHP natif pour cette soutenance afin de maîtriser les mécanismes fondamentaux du langage, de la gestion des sessions HTTP, du routing et des requêtes SQL PDO sans dépendre de la 'magie' d'un framework. Cela m'a permis de concevoir ma propre architecture MVC légère et sur-mesure."*

### ❓ Q2 : Comment gérez-vous la sécurité contre les failles Web (SQLi, XSS, CSRF) ?
> **💡 Réponse modèle** :
> * **Injections SQL** : *"Toutes les requêtes en BDD utilisent l'extension PDO avec des requêtes préparées et des liaisons de variables (`bindValue`)."*
> * **XSS (Cross-Site Scripting)** : *"Côté Frontend, les données saisies par les utilisateurs sont échappées ou injectées via des propriétés sécurisées comme `textContent` au lieu de `innerHTML` quand cela est nécessaire."*
> * **Mots de passe** : *"Ils sont hachés en BDD avec l'algorithme fort `password_hash()` (BCrypt)."*

### ❓ Q3 : Quelles difficultés majeures avez-vous rencontrées et comment les avez-vous résolues ?
> **💡 Réponse modèle** :
> 1. **Gestion des Sessions et CORS** : *"Au début, la séparation Frontend/Backend causait des pertes de session HTTP. J'ai résolu cela en configurant correctement les en-têtes CORS (`Access-Control-Allow-Credentials: true`) et en synchronisant `credentials: 'include'` dans les requêtes `fetch()` JavaScript."*
> 2. **Validation des inscriptions par Email** : *"Pour la vérification par code OTP à 6 chiffres, comme je travaillais en environnement local (XAMPP), j'ai mis en place un système de logs d'audit via une classe `Logger` qui enregistre les codes générés pour valider le flux sans dépendre d'un serveur SMTP externe."*

### ❓ Q4 : Comment fonctionne l'algorithme de Match ?
> **💡 Réponse modèle** : *"Lorsqu'un utilisateur A 'like' un utilisateur B, une entrée est créée dans la table `matches`. Le système vérifie immédiatement si une entrée inverse existe déjà (utilisateur B a liké utilisateur A). Si c'est le cas, le statut passe automatiquement à `match`, débloquant la possibilité d'échanger des messages."*

---

# ⚖️ Phase 3 : Délibération et Débriefing (5 min)

### 🚪 Durant la délibération :
* Vous devez quitter la salle. Restez calme et concentré à l'extérieur.

### 📋 Rappel des critères sur lesquels le jury vous note :
1. **Pertinence du projet & Qualité technique** (Architecture propre, POO, PDO, SPA JS).
2. **Clarté de la présentation et de la démo en live**.
3. **Autonomie et capacité à justifier vos choix technologiques**.
4. **Gestion des problèmes et maturité face aux difficultés**.

---

# 💡 5 Conseils d'Or pour le Jour J
1. **Chrono en main** : Entraînez-vous à voix haute avec un chronomètre pour ne pas dépasser les 15 minutes de présentation !
2. **Navigateur prêt** : Ouvrez votre projet dans XAMPP, vérifiez que MySQL tourne, et ouvrez les 5 pages clés dans des onglets séparés avant d'entrer dans la salle.
3. **Assurez vos réponses** : Si vous ne savez pas répondre à une question du jury, ne meublez pas. Dites : *"C'est un point très intéressant. Dans l'état actuel de mon projet, j'ai privilégié la solution X, mais pour une version 2.0, j'implémenterais Y."*
4. **Soyez fier du travail accompli** : 47 fichiers propres, un système complet (Auth, Swipe, Chat, Admin), c'est un excellent projet de fin d'année !
