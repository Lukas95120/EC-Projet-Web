# 🎮 GameStats

GameStats est une application web développée en PHP permettant de consulter, gérer et enrichir un catalogue de jeux vidéo.

Le projet propose une expérience complète avec un système d’utilisateurs, des avis, des favoris, un espace administrateur ainsi qu’une intégration d’API externe pour automatiser l’ajout de données.

---

## 🚀 Fonctionnalités principales

### 👤 Utilisateurs
- Inscription / connexion / déconnexion
- Profil utilisateur (avatar, bio)
- Ajout / suppression de jeux en favoris
- Ajout et suppression d’avis sur les jeux

### 🎮 Catalogue
- Liste de jeux avec filtres :
  - Nom
  - Genre
  - Plateforme
  - Année
- Tri des résultats (titre, ventes, notes, etc.)
- Fiche détaillée pour chaque jeu :
  - Informations complètes
  - Notes utilisateurs et critiques
  - Avis des utilisateurs

### 📝 Système de tickets
- Les utilisateurs peuvent demander l’ajout d’un jeu
- Workflow côté admin :
  - Tickets en attente
  - Tickets acceptés (à ajouter)
  - Tickets archivés (refusés ou déjà traités)
- Ajout automatique d’un jeu depuis un ticket

### 🛠️ Administration
- Dashboard administrateur
- Gestion complète des jeux :
  - Ajouter
  - Modifier
  - Supprimer
- Gestion des tickets utilisateurs

### 🤖 Automatisation (API RAWG)
- Auto-remplissage des informations d’un jeu :
  - Titre
  - Genre
  - Plateforme
  - Année
  - Notes
- Ajout automatique d’images de jeux
- Script pour compléter les jeux existants sans image

---

## 🔐 Sécurité

- Protection CSRF sur les formulaires sensibles
- Utilisation de requêtes préparées (PDO)
- Actions critiques sécurisées en POST

---

## 🧱 Technologies utilisées

- PHP (procédural / PDO)
- MySQL
- HTML / CSS (design responsive)
- JavaScript (interactions UX + API)
- API externe : RAWG

---

## ⚙️ Installation

### 1. Cloner le projet

```bash
git clone https://github.com/ton-utilisateur/gamestats.git
cd gamestats
```

### 2. Configurer la base de données

- Importer le fichier structure.sql
- Créer une base de données nommée gamestats

### 3. Configurer le projet

Copier le fichier de configuration :

```bash
cp config.example.php config.php
```

Modifier config.php avec vos informations :