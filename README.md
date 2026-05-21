# 🎮 GameStats

GameStats est une plateforme web communautaire dédiée aux jeux vidéo développée en PHP/MySQL.  
Le projet permet aux utilisateurs de découvrir des jeux, publier des avis, gérer des favoris, interagir avec d’autres membres et participer à une véritable communauté autour du gaming.

Le site intègre également un espace administrateur avancé, un système de modération complet, des fonctionnalités dynamiques en AJAX ainsi qu’une intégration de l’API RAWG pour automatiser l’importation des jeux vidéo.

Le projet est entièrement déployé en ligne sur AlwaysData.

---

# 🚀 Fonctionnalités principales

## 👤 Gestion des utilisateurs

- Inscription et connexion sécurisées
- Vérification des comptes par email
- Gestion des sessions utilisateurs
- Modification du profil utilisateur
- Upload d’avatar
- Biographie personnalisée
- Bannissement utilisateur côté administration

---

## 🎮 Catalogue de jeux

- Catalogue dynamique de jeux vidéo
- Recherche par nom
- Filtres :
  - Genre
  - Plateforme
  - Année
- Tri des résultats :
  - Nom
  - Notes critiques
  - Notes utilisateurs
  - Date de sortie
- Pagination du catalogue
- Pages détaillées pour chaque jeu

Chaque fiche jeu contient :
- Image du jeu
- Informations principales
- Notes critiques
- Notes utilisateurs
- Avis de la communauté
- Ajout aux favoris
- Partage du jeu à des amis

---

## ⭐ Favoris & avis

- Ajout de jeux en favoris
- Suppression de favoris
- Publication d’avis utilisateurs
- Notes sur 5 étoiles
- Suppression de ses propres avis
- Mise à jour dynamique des notes

---

# 👥 Fonctionnalités communautaires

## 🤝 Système d’amis

- Envoi de demandes d’amis
- Acceptation / refus
- Liste d’amis
- Découverte de membres
- Recherche dynamique de membres en AJAX

---

## 💬 Messagerie privée

- Conversations privées entre amis
- Envoi de messages privés
- Mise à jour automatique des messages
- Suppression automatique des messages après 24h
- Notifications de nouveaux messages

---

## 🌍 Forum communautaire

- Publication de messages publics
- Actualisation dynamique
- Modération automatique
- Nettoyage automatique des anciens messages

---

# 🔔 Notifications en temps réel

Le système de notifications fonctionne entièrement en AJAX :

- Notifications instantanées
- Compteur dynamique dans le header
- Marquage comme lu sans rechargement
- Suppression individuelle
- Suppression globale
- Notifications pour :
  - demandes d’amis
  - messages privés
  - modération
  - tickets
  - badges
  - recommandations de jeux

---

# 🛠️ Administration

## Dashboard administrateur

- Statistiques globales
- Gestion des utilisateurs
- Gestion des jeux
- Gestion des tickets
- Gestion des avis
- Gestion du forum

---

## 🔨 Modération avancée

- Bannissement / débannissement
- Raisons de modération obligatoires
- Historique des actions administrateur
- Notifications automatiques des sanctions
- Déconnexion automatique des comptes bannis

---

# 🎫 Système de tickets

Les utilisateurs peuvent demander l’ajout de nouveaux jeux.

Fonctionnalités :
- Création de tickets
- Suivi des demandes
- Validation côté administration
- Refus avec raison
- Import automatique du jeu depuis RAWG

---

# 🤖 Intégration API RAWG

Le projet utilise l’API RAWG.io pour récupérer automatiquement :

- Nom du jeu
- Genre
- Plateforme
- Année de sortie
- Image du jeu
- Description
- Score critique
- Éditeur

Fonctionnalités disponibles :
- Recherche de jeux RAWG
- Import manuel
- Import massif aléatoire
- Protection anti-doublons via `rawg_id`

---

# ⚡ Fonctionnalités AJAX

Plusieurs parties du site fonctionnent sans rechargement :

- Notifications
- Recherche communauté
- Favoris
- Messages privés
- Actualisation des discussions
- Suppressions dynamiques
- Actions de modération

---

# 🔐 Sécurité

Le projet intègre plusieurs protections :

- Requêtes préparées PDO
- Protection CSRF
- Hashage sécurisé des mots de passe
- Vérification email
- Gestion des permissions
- Validation des formulaires
- Protection contre les accès non autorisés
- Système anti-contenu abusif

---

# 🧱 Technologies utilisées

## Front-end
- HTML5
- CSS3
- JavaScript
- AJAX / Fetch API

## Back-end
- PHP 8
- PDO
- Sessions PHP

## Base de données
- MySQL
- phpMyAdmin

## API externe
- RAWG.io API

## Hébergement & outils
- AlwaysData
- FileZilla
- Git / GitHub
- Visual Studio Code

---

# ⚙️ Installation du projet

## 1. Cloner le projet

```bash
git clone https://github.com/ton-utilisateur/gamestats.git
cd gamestats
```

---

## 2. Configurer la base de données

Importer le fichier :

```bash
structure.sql
```

dans phpMyAdmin.

---

## 3. Configurer le projet

Créer le fichier :

```bash
includes/config.php
```

Puis ajouter :

```bash
<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'gamestats');
define('DB_USER', 'root');
define('DB_PASS', '');

define('RAWG_API_KEY', 'your_rawg_api_key');
```

---

## 4. Lancer le projet

Placer le projet dans :

- XAMPP
- WAMP
- MAMP
- ou un hébergement web

Puis accéder au site :

```bash
http://localhost/gamestats
```

---

## 🌐 Mise en ligne

Le projet a été déployé sur AlwaysData avec :

- Hébergement PHP/MySQL
- Base de données distante
- FTP via FileZilla
- Vérification email fonctionnelle
- Déploiement complet en production

---

## 🌐 📊 État du projet

Le projet est entièrement fonctionnel avec :

- Système communautaire complet
- Messagerie privée
- Notifications temps réel
- Administration avancée
- Modération
- Import automatique RAWG
- Déploiement en ligne

---

## 🔮 Améliorations possibles

- Système de commentaires sous les avis
- Ajout de trailers vidéo
- Notifications WebSocket temps réel
- Recherche avancée
- Recommandations intelligentes
- Application mobile
- Optimisation SEO
- Dark mode complet

---

## 👨‍💻 Auteur

Projet réalisé par Lukas Estragnat dans le cadre d’un apprentissage du développement web et d’un projet de fin d’année.

---