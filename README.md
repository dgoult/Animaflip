# Projet de Cartes Animées - Mode d'emploi

## Introduction

Bienvenue dans l'application "Cartes Animées". Cette application aide les enfants malentendants à apprendre de manière interactive en associant des images animées avec des sons. Ce document vous guide dans l'utilisation et la gestion de l'application.

# Laragon
Une fois le repo cloné, ouvrir le '00-default.conf' d'Apache pour y créer le virtual host :
![alt text](image.png)

```
<VirtualHost _default_:80>
    DocumentRoot "yourdirectory\Animaflip\AnimaflipApi\public"
    <Directory "yourdirectory\Animaflip\AnimaflipApi\public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Puis créer la base de donnée ``animaflip``


## Table des Matières

1. [Mode Administrateur Web](#mode-administrateur-web)
    - [Ajouter un utilisateur](#ajouter-un-utilisateur)
    - [Supprimer un utilisateur](#supprimer-un-utilisateur)
    - [Gérer les Thèmes d'un utilisateur](#gérer-les-thèmes-dun-utilisateur)
    - [Gérer les droits utilisateurs](#gérer-les-droits-utilisateurs)
    - [Gérer les Thèmes](#gérer-les-thèmes)
    - [Ajouter un thème](#ajouter-un-thème)
    - [Gérer les animations](#gérer-les-animations)
    - [Ajouter une animation](#ajouter-une-animation)
    - [Supprimer une animation](#supprimer-une-animation)
2. [Mode Administrateur Mobile](#mode-administrateur-mobile)
3. [Mode Standard](#mode-standard)

## Mode Administrateur Web

### Accès au panneau d'administration
- URL : [Administration](https://revue-handisport.fr/admin/login)
- Identifiants : 
  - **Email** : admin@admin.com
  - **Mot de passe** : admin

Depuis cette interface, vous pouvez gérer les utilisateurs, les thèmes, et les animations.

### Ajouter un utilisateur
- Cliquer sur "Ajouter un utilisateur".
- Remplir l'email, le mot de passe et définir le rôle (user ou admin).
- Cliquer sur "Créer".

### Supprimer un utilisateur
- Cliquer sur le bouton "Supprimer" à côté de l'utilisateur à supprimer.

### Gérer les Thèmes d'un utilisateur
- Cliquer sur "Modifier" d'un utilisateur pour affecter ou désaffecter un thème.

### Gérer les droits utilisateurs
- Dans "Gestion des utilisateurs", cliquer sur "Modifier" d'un utilisateur.
- Changer les informations et les droits (user ou admin), puis cliquer sur "Mettre à jour".

### Gérer les Thèmes
- Dans "Gestion des thèmes", cliquer sur "Modifier" pour changer le nom du thème ou affecter des animations.

### Ajouter un thème
- Dans "Gestion des thèmes", cliquer sur "Ajouter un nouveau thème", entrer le nom et cliquer sur "Créer".

### Gérer les animations
- Dans "Gestion des thèmes", modifier ou ajouter des animations.

### Ajouter une animation
- Dans "Gestion des animations", choisir le nom, uploader un fichier MP4, et affecter l'animation à des thèmes.

### Supprimer une animation
- Cliquer sur "Supprimer" à côté de l'animation à supprimer.

## Mode Administrateur Mobile

### Se connecter
- Lancer l'application, entrer les identifiants administrateurs pour accéder au panneau d'administration.

### Ajouter/Supprimer un utilisateur
- Depuis le panneau d'administration, ajouter ou supprimer un utilisateur comme dans le mode web.

### Gérer les Thèmes
- Modifier, affecter ou désaffecter les thèmes d'un utilisateur.

## Mode Standard

### Se connecter
- Lancer l'application et entrer les identifiants pour accéder.

### Naviguer dans les animations
- Depuis la page d'accueil, choisir un thème puis cliquer pour voir les animations.
- Utiliser les boutons "Suivant" et "Précédent" pour naviguer entre les animations.

### Se déconnecter
- Cliquer sur "Se déconnecter" pour quitter l'application.