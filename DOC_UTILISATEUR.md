# 📖 Documentation Utilisateur - Application Loove

Bienvenue dans la documentation utilisateur officielle de l'application **Loove**, la plate-forme moderne de rencontre en ligne. Ce document a pour objectif de vous guider à travers toutes les fonctionnalités de l'application, que vous soyez un nouvel utilisateur, un membre Premium ou un administrateur.

---

## 📋 Table des Matières
1. [Présentation Générale](#1-présentation-générale)
2. [Inscription et Connexion](#2-inscription-et-connexion)
3. [Gestion du Profil et Photos](#3-gestion-du-profil-et-photos)
4. [Découverte et Système de Matchs](#4-découverte-et-système-de-matchs)
5. [Messagerie Instantanée](#5-messagerie-instantanée)
6. [Espace et Avantages Premium](#6-espace-et-avantages-premium)
7. [Modération et Espace Administration](#7-modération-et-espace-administration)

---

## 1. Présentation Générale

**Loove** est une application web de rencontre axée sur la simplicité, la convivialité et la sécurité. Elle permet aux célibataires de découvrir des profils compatibles, d'échanger des likes, d'obtenir des matchs et de discuter en temps réel dans un environnement sécurisé et modéré.

---

## 2. Inscription et Connexion

### 🔑 Inscription
1. Rendez-vous sur la page d'accueil ou d'inscription.
2. Remplissez le formulaire avec vos informations personnelles :
   - Nom et Prénom
   - Adresse E-mail (servira d'identifiant)
   - Mot de passe sécurisé
   - Genre et Préférence d'orientation
   - Date de naissance et Ville
3. Validez l'inscription. Votre compte est immédiatement actif !

### 🔐 Connexion
1. Entrez votre email et mot de passe sur la page de connexion ([login.html](file:///c:/xampp/htdocs/app-loove/frontend/public/login.html)).
2. Une fois authentifié, vous êtes automatiquement redirigé vers votre tableau de bord.

---

## 3. Gestion du Profil et Photos

Votre profil est votre vitrine. Pour maximiser vos chances de match :
- **Édition des informations** : Vous pouvez modifier votre biographie, vos centres d'intérêt et votre localisation depuis l'onglet **Profil**.
- **Gestion des photos** :
  - Importez plusieurs photos dans votre galerie.
  - Définissez votre **Photo Principale** qui sera visible lors du défilement des profils.
  - Supprimez les photos que vous ne souhaitez plus afficher.

---

## 4. Découverte et Système de Matchs

### 💘 Le Matchmaking
- Rendez-vous dans la section **Découverte** ([dashboard.html](file:///c:/xampp/htdocs/app-loove/frontend/public/dashboard.html)).
- Les profils suggérés s'affichent sous forme de cartes élégantes avec photo, prénom, âge et biographie.
- **Interactions** :
  - **Like (Cœur)** : Exprimez votre intérêt pour le profil.
  - **Dislike (Croix)** : Passez au profil suivant.
- **C'est un Match !** : Si la personne que vous avez likée vous a également liké(e), un écran de Match apparaît instantanément ! Vous pouvez maintenant démarrer une conversation.

---

## 5. Messagerie Instantanée

- Accédez à vos conversations depuis l'onglet **Messages** ([messages.html](file:///c:/xampp/htdocs/app-loove/frontend/public/messages.html)).
- Retrouvez la liste de tous vos matchs.
- Sélectionnez un match pour ouvrir le tchat privé.
- Échangez des messages texte en direct pour apprendre à vous connaître.

---

## 6. Espace et Avantages Premium

Passez au niveau supérieur avec le statut **Loove Premium** ([premium.html](file:///c:/xampp/htdocs/app-loove/frontend/public/premium.html)) :
- 👁️ **Voir qui vous a visité** : Consultez la liste des utilisateurs qui ont consulté votre profil.
- ⭐ **Filtres Avancés** : Affinez vos recherches par critères spécifiques.
- 🚀 **Boost de Visibilité** : Mettez votre profil en avant auprès des autres célibataires de votre région.

---

## 7. Modération et Espace Administration

La sécurité et le respect de la communauté sont au cœur de nos priorités.

### 🛡️ Signalement d'un profil
Si un utilisateur adopte un comportement inapproprié ou publie du contenu non conforme :
1. Cliquez sur le bouton **Signaler** présent sur son profil.
2. Choisissez le motif du signalement et ajoutez des détails si nécessaire.

### 👑 Espace Administration ([admin.html](file:///c:/xampp/htdocs/app-loove/frontend/public/admin.html))
*(Accessible uniquement aux comptes dotés du rôle Administrateur)*
- **Tableau de bord** : Statistiques globales du site (nombre de membres, matchs, messages échangés).
- **Gestion des utilisateurs** : Suspension ou bannissement des comptes problématiques.
- **Traitement des signalements** : Examen des rapports envoyés par les membres et prise de mesures disciplinaires.
