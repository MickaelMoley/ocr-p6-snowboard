
# Guide d'installation du projet Snowboard
## Les pré-réquis
Avant d'installer ce projet, assurez-vous les outils nécessaires qui est listé :
- PHP 7.4
- Composer

## Récupérer le code du projet
Pour pouvoir installer le projet Symfony convenablement, il faut cloner le projet sur votre machine grâce à cette commande :

`
git clone https://github.com/MickaelMoley/ocr-p6-snowboard.git
`

## Installation des dépendances
Une fois que le projet cloné, vous devez lancer cette commande à la racine du dossier cloné pour pouvoir télécharger les dépendances du projet.
PS : cette commande va installer Symfony et tout autres librairies nécessaire afin de faire fonctionner le projet

`
composer install
`

## Configuration du projet
Pour pouvoir utiliser l'application, vous devez modifier le fichier `.env`, qui est à la racine du projet et changer les valeurs suivantes : 



 - Si les identifiants de votre base de données ne nécessitent pas un mot de passe 
 `DATABASE_URL="mysql://nomdutilisateur@127.0.0.1:3306/nomdelabasededonnee"`

 - Si les identifiants de votre base de données nécessitent un mot de passe 
 `DATABASE_URL="mysql://nomdutilisateur:motdepasse@127.0.0.1:3306/nomdelabasededonnee"`

Ensuite, lancer cette commande :

`php bin/console doctrine:database:create`

pour que Symfony crée la base de données pour nous.

Si une erreur se produit, n'hésitez pas à synchroniser la base de données avec le schéma des entités que contient le projet, avec la commande suivante :

`php bin/console doctrine:schema:update -f`

## Installation des jeu de données

Un jeu de donnée contenant des figures est présent dans le projet. Il suffit de lancer la commande suivante pour pouvoir charger des données dans la base de données.

`
 php bin/console doctrine:fixtures:load
`

Ensuite, entrez : `yes` ou `y` pour continuer la procédure.
En acceptant, cela aura pour effet de supprimer les entrées déjà présentes dans la base de données.

## Analyse Codacy
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/ba6f3ee051b34594afdc559277c8de9a)](https://www.codacy.com/gh/MickaelMoley/ocr-p6-snowboard/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=MickaelMoley/ocr-p6-snowboard&amp;utm_campaign=Badge_Grade)

