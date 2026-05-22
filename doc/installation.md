# Procédure de déploiement AlwaysData — GameStats

Ce document explique comment déployer le projet GameStats sur un hébergement AlwaysData.

---

## 1. Créer un compte AlwaysData

1. Se rendre sur le site AlwaysData.
2. Créer un compte utilisateur.
3. Choisir l’offre gratuite.
4. Créer un espace d’hébergement nommé par exemple :

```txt
gamestats
```

L’adresse finale du site sera de ce type :

```txt
https://gamestats.alwaysdata.net
```

---

## 2. Créer la base de données MySQL

Dans le panneau AlwaysData :

```txt
Bases de données > MySQL
```

Créer une base de données nommée par exemple :

```txt
gamestats_db
```

Vérifier que l’utilisateur MySQL possède bien tous les droits sur cette base.

---

## 3. Importer la structure SQL

Ouvrir phpMyAdmin depuis AlwaysData.

Sélectionner la base :

```txt
gamestats_db
```

Importer uniquement le fichier :

```bash
doc/structure.sql
```

Ne pas importer data.sql en production, car ce fichier sert uniquement aux tests locaux.

---

## 4. Configurer les variables du projet

Créer le fichier suivant sur le serveur :

```bash
www/includes/config.php
```

À partir du modèle :

```bash
www/includes/config.example.php
```

Exemple de configuration pour AlwaysData :

```php
<?php

define('DB_HOST', 'mysql-gamestats.alwaysdata.net');
define('DB_NAME', 'gamestats_db');
define('DB_USER', 'gamestats');
define('DB_PASS', 'mot_de_passe_mysql');

define('RAWG_API_KEY', 'votre_cle_api_rawg');

define('SITE_URL', 'https://gamestats.alwaysdata.net');
define('MAIL_FROM', 'no-reply@gamestats.alwaysdata.net');
?>
```

Variables à adapter :

Variable / Description
DB_HOST	/ Adresse du serveur MySQL AlwaysData
DB_NAME	/ Nom de la base de données
DB_USER	/ Nom de l’utilisateur MySQL
DB_PASS	/ Mot de passe MySQL
RAWG_API_KEY	/ Clé API RAWG
SITE_URL	/ URL publique du site
MAIL_FROM	Adresse utilisée comme expéditeur des emails

---

## 5. Envoyer les fichiers avec FileZilla

Installer FileZilla Client.

Dans AlwaysData, récupérer les informations FTP :

```txt
Accès distant > FTP
```

Connexion FileZilla :

```txt
Hôte : ftp-gamestats.alwaysdata.net
Utilisateur : gamestats
Mot de passe : mot_de_passe_ftp
Port : 21
```

Envoyer le contenu du dossier local :

```bash
www/
```

dans le dossier distant :

```bash
/www/www/
```

Le serveur doit contenir directement :

```bash
/www/www/index.php
/www/www/includes/
/www/www/admin/
/www/www/assets/
```

Il ne faut pas obtenir une structure du type :

```bash
/www/www/EC-Projet-Web/www/index.php
```

---

## 6. Supprimer la page par défaut AlwaysData

Dans le dossier distant :

```bash
/www/www/
```

supprimer le fichier :

```bash
index.html
```

Sinon AlwaysData peut afficher la page par défaut au lieu du site PHP.

---

## 7. Créer le premier compte administrateur

Après l’import de la base, créer un compte administrateur depuis phpMyAdmin.

Exemple :

```sql
INSERT INTO users (
    username,
    email,
    password,
    role,
    email_verified,
    email_verified_at,
    created_at
)
VALUES (
    'admin',
    'admin@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.ogKn4n0v0JzK6Y9K',
    'admin',
    1,
    NOW(),
    NOW()
);
```

Identifiants de test pour cet exemple :

```txt
Email : admin@example.com
Mot de passe : password
```

Il est recommandé de modifier ensuite le mot de passe depuis la base de données ou de créer un administrateur réel avec un mot de passe sécurisé.

---

## 8. Vérifier le fonctionnement du site

Accéder au site :

```txt
https://gamestats.alwaysdata.net
```

Tester les pages principales :

- Accueil
- Catalogue
- Inscription
- Connexion
- Vérification email
- Profil
- Favoris
- Avis
- Forum
- Amis
- Messages privés
- Notifications
- Administration

---

## 9. Tester l’import RAWG

Se connecter avec un compte administrateur.

Tester :

```txt
Admin > Import RAWG
```

Puis :

```txt
Admin > Import massif aléatoire
```

Vérifier que les jeux sont bien ajoutés au catalogue.

---

## 10. Vérifier l’envoi d’emails

Créer un nouveau compte utilisateur.

Vérifier que l’utilisateur reçoit un code de validation par email.

Si l’email n’arrive pas :

- vérifier MAIL_FROM
- vérifier que SITE_URL correspond bien au domaine AlwaysData
- vérifier que la fonction mail() est autorisée
- vérifier le dossier spam

---

## 11. Sécurité avant mise en public

Avant de rendre le dépôt GitHub public, vérifier que les fichiers suivants ne contiennent pas de secrets :

```txt
includes/config.php
.env
exports SQL
backups
```

Le fichier suivant doit rester ignoré par Git :

```txt
www/includes/config.php
```

Le fichier .gitignore doit contenir :

```txt
/www/includes/config.php
/www/assets/uploads/*
!/www/assets/uploads/.gitkeep
.env
.env.local
```

---

## 12. Fichiers SQL

- doc/structure.sql : structure vide de la base de données
- doc/data.sql : données de test locales uniquement

En production, importer uniquement :

```bash
doc/structure.sql
```

---

## 13. Résultat attendu

Une fois le déploiement terminé :

- le site est accessible publiquement ;
- la base de données distante fonctionne ;
- les utilisateurs peuvent s’inscrire ;
- la vérification email fonctionne ;
- l’administration est accessible ;
- l’import RAWG fonctionne ;
- les fonctionnalités AJAX fonctionnent ;
- les fichiers sensibles ne sont pas exposés publiquement.

Tu peux copier-coller directement ce contenu dans `doc/installation.md`.