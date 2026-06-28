# 🎙️ Script Oral Complet Mot à Mot - Soutenance Loove
**Présentateur** : Colas Johan  
**Durée de la présentation** : 15 minutes + Démonstration  
**Support visuel** : [slides.html](file:///c:/xampp/htdocs/app-loove/slides.html)

Ce document contient votre discours exact, slide par slide, rédigé à la première personne. Il vous suffit de le lire ou de vous en inspirer pendant que vous faites défiler votre diaporama.

---

## 📍 Slide 1 : Titre & Introduction (⏱️ 0m00s - 0m45s)

**[Afficher la Slide 1 sur slides.html]**

### 🗣️ Discours Mot à Mot :
> *"Bonjour à toutes et à tous, membres du jury.*
> 
> *Je m'appelle **Colas Johan** et je suis ravi de vous présenter aujourd'hui mon projet de fin d'année : l'application **Loove**.*
> 
> *Loove est une plateforme web dynamique et interactive dédiée à la mise en relation et à la messagerie instantanée. Au cours des 15 prochaines minutes, je vais vous expliquer le contexte de ce projet, mes choix architecturaux et techniques, la méthodologie adoptée, avant de vous faire une démonstration en direct du produit fonctionnel."*

---

## 📍 Slide 2 : Contexte & Objectifs (⏱️ 0m45s - 2m00s)

**[Passer à la Slide 2]**

### 🗣️ Discours Mot à Mot :
> *"Pour aborder le contexte de ce projet, j'ai choisi de développer un réseau social thématique axé sur la rencontre et l'interaction entre utilisateurs.*
> 
> *Mon objectif principal était d'offrir une expérience utilisateur fluide et moderne, en concevant l'application sous la forme d'une **Single Page Application (SPA)**. Cela signifie que l'utilisateur peut naviguer, découvrir des profils et échanger des messages sans subir de rechargements de page intempestifs.*
> 
> *Deux enjeux majeurs ont guidé mon travail :*
> * *Tout d'abord, un **enjeu de performance** : assurer des échanges de données asynchrones et rapides entre le navigateur et le serveur via l'API Fetch.*
> * *Ensuite, un **enjeu de sécurité** : garantir la confidentialité des données, l'authentification avec vérification par code OTP et la protection contre les failles web standards."*

---

## 📍 Slide 3 : Architecture Découplée (MVC) (⏱️ 2m00s - 3m30s)

**[Passer à la Slide 3]**

### 🗣️ Discours Mot à Mot :
> *"Sur le plan architectural, j'ai fait le choix d'une **architecture découplée orientée MVC (Modèle-Vue-Contrôleur)**.*
> 
> *Comme vous pouvez le voir sur ce schéma, le projet est strictement séparé en deux parties :*
> 1. *Côté **Client (Frontend)** : développé en HTML5, CSS3 et JavaScript Vanilla. Il formule des requêtes HTTP asynchrones.*
> 2. *Côté **Serveur (Backend)** : développé en PHP. J'ai mis en place un composant central, le **Front Controller** (`index.php`), qui intercepte l'ensemble des requêtes API REST, vérifie les droits et aiguille le traitement vers le contrôleur métier approprié.*
> 
> *La communication s'effectue exclusivement au format standardisé JSON, tandis que le serveur échange de manière sécurisée avec la base de données MySQL via PDO."*

---

## 📍 Slide 4 : Backend : PHP 8, POO & PDO (⏱️ 3m30s - 5m00s)

**[Passer à la Slide 4]**

### 🗣️ Discours Mot à Mot :
> *"Entrons maintenant dans le détail des choix technologiques, en commençant par le Backend.*
> 
> *J'ai choisi d'utiliser **PHP 8 en Programmation Orientée Objet**.*
> * *La POO m'a permis de structurer proprement mon code avec une séparation nette entre les **Modèles** (qui gèrent l'accès aux données) et les **Contrôleurs** (qui contiennent la logique métier).*
> * *Pour l'interaction avec la base de données, j'utilise l'extension **PDO**. J'ai systématiquement recours à des requêtes préparées avec liaison de paramètres (`bindValue`). Cela permet de neutraliser 100% des risques d'injections SQL.*
> * *Enfin, j'ai opté pour du PHP natif avec autoloading dynamique sans framework lourd, afin de maîtriser parfaitement chaque couche de mon application."*

---

## 📍 Slide 5 : Frontend : JS Vanilla, HTML5 & CSS3 (⏱️ 5m00s - 6m30s)

**[Passer à la Slide 5]**

### 🗣️ Discours Mot à Mot :
> *"Pour le Frontend, j'ai délibérément fait le choix du **JavaScript Vanilla (ES6+)** sans surcouche.*
> 
> *Ce choix me permet d'exploiter la puissance native de l'API `fetch` avec la syntaxe moderne `async/await`. Grâce à cela, le contenu des profils, les cartes de swipe et les messages s'injectent dynamiquement dans le DOM en temps réel.*
> 
> *Côté style et ergonomie, j'ai conçu un design système moderne à l'aide de **Variables CSS**, de **Flexbox** et de **CSS Grid**. L'interface est entièrement réactive (Responsive Design) pour garantir un affichage optimal sur mobile comme sur ordinateur."*

---

## 📍 Slide 6 : Base de Données & Schéma Relationnel (⏱️ 6m30s - 7m45s)

**[Passer à la Slide 6]**

### 🗣️ Discours Mot à Mot :
> *"La gestion des données repose sur une base relationnelle **MySQL** optimisée.*
> 
> *Le schéma se compose de plusieurs tables clés :*
> * *La table `users` pour stocker les informations de profil, les rôles (utilisateur/admin), le statut premium et les mots de passe hachés.*
> * *La table `user_photos` pour la galerie d'images.*
> * *La table `matches` qui enregistre les interactions (likes et dislikes).*
> * *Et la table `messages` pour conserver les échanges de messagerie.*
> 
> *J'ai également intégré des tables de traçabilité et de sécurité, telles que `visits` pour l'historique des consultations de profils, et `reports` pour permettre la modération des utilisateurs inappropriés."*

---

## 📍 Slide 7 : Méthodologie & Organisation (⏱️ 7m45s - 9m00s)

**[Passer à la Slide 7]**

### 🗣️ Discours Mot à Mot :
> *"Concernant la méthodologie de développement, j'ai suivi une démarche itérative structurée en 4 grandes étapes :*
> 1. *La **Modélisation** initiale de la BDD.*
> 2. *Le développement de l'**API Backend** (création des contrôleurs `UserController`, `MatchController`, `MessageController`).*
> 3. *L'intégration du **Client Web**.*
> 4. *Et enfin une phase rigoureuse de **Validation & Diagnostics**.*
> 
> *J'ai privilégié une **approche modulaire** : chaque fonctionnalité possède son propre module dédié aussi bien côté serveur que côté client, ce qui rend le projet très simple à faire évoluer."*

---

## 📍 Slide 8 : Sécurité & Robustesse (⏱️ 9m00s - 10m15s)

**[Passer à la Slide 8]**

### 🗣️ Discours Mot à Mot :
> *"La sécurité a été au cœur de mes préoccupations tout au long du projet.*
> 
> *Voici les mesures concrètes mises en œuvre :*
> * * **Protection des mots de passe** : hachage fort en base de données avec l'algorithme fort BCrypt via `password_hash()`.*
> * * **Authentification par OTP** : lors de l'inscription, un code de vérification à 6 chiffres est généré pour valider le compte utilisateur.*
> * * **Journalisation centralisée** : j'ai développé une classe `Logger` qui enregistre l'ensemble des événements de sécurité et des erreurs système dans un fichier journal `app.log` pour faciliter la maintenance."*

---

## 📍 Slide 9 : Résultats Obtenus (⏱️ 10m15s - 11m30s)

**[Passer à la Slide 9]**

### 🗣️ Discours Mot à Mot :
> *"Arrivé au terme du développement, les résultats sont extrêmement satisfaisants.*
> 
> *Comme le prouvent mes tests automatisés et mes scripts d'audit, les **47 fichiers du projet** (PHP, JS, HTML, CSS, SQL) ont été validés et ne comportent **aucune erreur de syntaxe**.*
> 
> *Toutes les fonctionnalités prévues dans le cahier des charges sont 100% opérationnelles, offrant une navigation fluide et sans aucun rechargement de page."*

---

## 📍 Slide 10 : Démonstration en Direct (⏱️ 11m30s - 12m00s)

**[Passer à la Slide 10]**

### 🗣️ Discours Mot à Mot :
> *"Le produit étant pleinement fonctionnel, je vous propose maintenant d'effectuer une démonstration en direct.*
> 
> *Je vais basculer sur mon navigateur pour vous présenter le parcours utilisateur complet : de la connexion sécurisée, au swipe de profil sur le dashboard, jusqu'à la mise en relation et l'échange de messages."*

*(💡 **Action à faire** : Basculer sur le navigateur web et réaliser la démo live pendant ~3 minutes en suivant le guide).*

---

## 📍 Slide 11 : Bilan & Perspectives (⏱️ 14m00s - 14m45s)

**[Revenir sur le diaporama - Passer à la Slide 11]**

### 🗣️ Discours Mot à Mot :
> *"Pour conclure cette présentation, ce projet m'a permis d'acquérir une solide expérience dans la conception d'architectures web complètes, la manipulation du DOM asynchrone et la sécurisation des données.*
> 
> *Si je devais faire évoluer l'application vers une Version 2.0, j'envisagerais :*
> * *L'intégration des **WebSockets** pour permettre une messagerie instantanée en temps réel sans polling.*
> * *Un algorithme de recommandation avancé basé sur les centres d'intérêt.*
> * *Et la déclinaison du client web sous forme d'application mobile native."*

---

## 📍 Slide 12 : Conclusion & Questions (⏱️ 14m45s - 15m00s)

**[Passer à la Slide 12]**

### 🗣️ Discours Mot à Mot :
> *"Je vous remercie infiniment pour votre attention et votre écoute.*
> 
> *Je suis désormais à votre entière disposition pour répondre à toutes vos questions."*
