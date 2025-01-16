# RobotLeague

Ce document est un tutoriel pour utiliser **Robot League**.
**Robot League** est une web app permettant de gérer des tournois de football.
Cela inclut la gestion des utilisateurs, des équipes participantes et de l'affichage des équipes qui doivent s'affronter durant un tournoi. Les résultats seront affichés sur la page d'accueil.

## Sommaire

- [RobotLeague](#robotleague)
  - [Sommaire](#sommaire)
    - [Environnement de travail](#environnement-de-travail)
        - [Git](#git)
                - [Récupérer le projet](#récupérer-le-projet)
                - [Workflow de travail adopté](#workflow-de-travail-adopté)
    - [Configuration du projet](#configuration-du-projet)
    - [Interface administrateur](#interface-administrateur)
    - [Convention de codage](#convention-de-codage)
    - [Tester la cohérence de la Base de données](#tester-la-cohérence-de-la-base-de-données)
    - [Prérequis](#prérequis)
      - [Configuration du projet](#configuration-du-projet)
      - [Vérifier que vous avez tous les prérequis pour le projet](#vérifier-que-vous-avez-tous-les-prérequis-pour-le-projet)
      - [Configuration la Base de données](#configuration-de-la-base-de-données)
      - [Installation des prérequis](#installation-des-prérequis)
        - [1. Composer](#1-composer)
        - [2. Tailwind](#2-tailwind)
        - [3. Génération de la base de données](#3-génération-de-la-base-de-données)
        - [4. Installation des librairies python](#4-installation-des-librairies-python)
        - [5. Installation d'un correcteur de code](#5-installation-dun-correcteur-de-code)
          - [PHP](#php)
          - [HTML](#html)
    - [Interface Administrateur](#interface-administrateur-1)
      - [Créer un compte organisateur](#créer-un-compte-organisateur)
      - [Modifier mot de passe](#modifier-mot-de-passe)
    - [Convention de codage](#convention-de-codage-1)
      - [PSR-1 coding (php)](#psr-1-coding-php)
      - [Symfony:](#symfony)
      - [HTML:](#html-1)
      - [CSS:](#css)
    - [Tester la cohérence de la Base de données](#tester-la-cohérence-de-la-base-de-données-1)

### Environnement de travail

#### Git

RobotLeague est un travail collaboratif réalisé sur gitlab

##### Récupérer le projet

Il suffit de cloner le projet dans le répertoire voulu via la commande : `git clone`**`lienSSHduProjet`**

##### Workflow de travail adopté

En partant de la branche principale appelée main, faire d'autres branches par fonctionnalités : `git branch`**`nomDeLaBranche`**

Pour changer de branche : `git switch`**nomDeLaBranche**

Travailler dans la branche de fonctionnalité, puis lorsque l'on veut sauvegarder notre travail :
`git add`**`fichierASauvegarder`** ou `git add .`
`git commit -m "titre" -m "DescriptionDétailléeSiVoulue"`
`git push`

Si la branche main a été mise à jour il faut mettre à jour la branche et le main :
`git switch main`
`git pull`
`git switch brancheVoulue`
S'il n'y a pas beaucoup de commit de retards :`git rebase main`
S'il y a beaucoup de commit de retards : `git merge main`

Régler les éventuels conflits dans l'IDE ou l'éditeur

`git switch main`
`git rebase brancheVoule`
`git push`

### [Interface administrateur](#interface-administrateur)  

- [Créer un compte organisateur](#créer-un-compte-organisateur)
- [Modifier un mot de passe](#modifier-mot-de-passe)

### [Tester la cohérence de la Base de données](#tester-la-cohérence-de-la-base-de-données)

### Prérequis

Afin d'utiliser RobotLeague, il est requis d'avoir des compétences de base en `SQL` (MySQL). Cette application web est développée en `Symfony`, si vous souhaitez apporter des modifications à celle-ci il est préférable d'avoir une connaissance de base du `PHP`.

#### Configuration du projet

Le projet a été créé à l'aide de la commande `symfony new --webapp --version="6.*" symfony`. L'option --webapp a automatiquement créé des dépendances. Voici la liste des prérequis

La version de php utilisée : `PHP 8.3.6 (cli)`
La version de Symfony utilisée est : `6.4`  
La base de donnée est gérée avec : `MySQL`
Le framework CSS utilisé est : `Tailwind`
Convention de codage : `PSR-1`
La version de Composer utilisée est : `Composer version 2.7.1`
La version de python utilisée : `Python 3.12.3`
La librairie python utilisée pour les tests : `sqlalchemy`

#### Vérifier que vous avez tous les prérequis pour le projet

Il est possible de vérifier, à l'aide d'une commande symfony, si votre système possède déjà tous les prérequis et est prêt à lancer le projet.

Lancez la commande : `$ symfony console check:requirements`
> Attention !!! Cela ne change pas le besoin de recharger [Tailwind](tailwind) à chaque fois que vous ouvrez le projet!

#### Configuration de la Base de données

Afin de pouvoir utiliser votre base de donnée, il faudra modifier le lien du fichier .env et de mettre le votre.
Pour des raisons de sécurité car ce fichier se retrouvera dans le git, il est plus judicieux de laisser la valeure par défaut et de créer un fichier .env.local avec exactement le même contenu qui lui, aura le lien vers votre base de donnée.

Ce lien permettra à Symfony de se connecter à votre base.
Assurez vous que le .env.local est bien présent dans le .gitignore, ce fichier restera en local et sera utilisé à la place du .env sur votre machine!
> Même si vous n'utilisez pas MySql, tout fonctionnera.

#### Installation des prérequis

L'installation des prérequis se fait sur chaque machine utilisant le projet, une seule fois au début.
Ces installations sont sous formes de commandes à taper dans le terminal.
> Sachez que `$ symfony console uneCommande` est l'équivalent de `$ php bin/console uneCommande`, l'un ou l'autre fonctionnera.

##### 1. Composer

Composer sera nécessaire pour lancer le projet :
 `$ composer install`

>Les fichiers installés ne devront pas être présent sur le git, assurez vous que composer figure dans le .gitignore

##### 2. Tailwind

Afin de modifier le style du site, vous aurez besoin d'utiliser Tailwind.
Pour le télécharger puis le lancer la 1ère fois vous devez exécuter les commandes suivantes :
`composer require symfonycasts/tailwind-bundle`
`php bin/console tailwind:init`  
`php bin/console tailwind:build`
`symfony serve`
`php bin/console tailwind:build -w`

Pour les prochaines fois, exécutez seuleument les commandes :
`$ symfony serve`
`$ php bin/console tailwind:build -w`

> Ne pas lancer tailwind n'empêche pas le projet de fonctionner, il empêche simplement de voir les mises à jour de styles venant de Tailwind en direct

##### 3. Génération de la base de données

La base de donnée est générée à partir des fichiers entity du projet Symfony. Le projet contient déjà les équivalents de déclencheurs et contraintes d'intégrités de la base de données. La base de données sera prête à l'usage dès création.
Afin de migrer les données de Symfony vers votre base de donnée il faut:

1. Créer un fichier de migrations (le fichier contenant le script de création de la base de donnée en .php)
`$ symfony console make:migration`

1. Migrer les données vers la base de données renseignée, de préférence, dans le .env.local (Créer la base de données à partir du fichier de migration)
`$ symfony console doctrine:migrations:migrate`

##### 4. Installation des librairies python

Les tests sont réalisés en python à l'aide de sqlalchemy qui permet de réaliser diverses actions sur la base de données.
Afin d'installer sqlalchemy utiliser la commande :
`$ pip install sqlalchemy`

##### 5. Installation d'un correcteur de code

Le projet suit les conventions de codage, dont [PSR-1](#psr-1-coding-php) pour php. Afin de les respecter il est possible d'utiliser un correcteur de code.

###### PHP

Installation : `$ composer require --dev squizlabs/php_codesniffer`  
    Vérifier le code : `$ vendor/bin/phpcs --standard=PSR1 src/`  
    Commande à exécuter pour corriger le code : `$ vendor/bin/phpcbf --standard=PSR1 src/`

###### HTML

Installation : `npm install htmlhint`  
Commande à exécuter pour corriger le code :`npx htmlhint chemin/vers/fichier.html`

### Interface Administrateur

#### Créer un compte organisateur

Afin de devenir organisateur, un utilisateur doit demander à un autre organisateur de devenir organisateur en donnant sont e-mail.
L'organisateur pourra alors changer le rôle de l'utilisateur dans l'interface de gestion d'utilisateur.

Si cette première option ne s'avère pas possible (aucun organisateur), alors il faut au préalable savoir qui sont les organisateurs et avec quels mails ils ont créé un compte utilisateur normal dans un premier temps.
Puis, afin de les donner le rôle utilisateur, il faudra le modifier directement dans la base de données et faire passer leur attribut rôle de `"ROLE_USER"` à `"ROLE_ORGANISATEUR"`

#### Modifier mot de passe

Afin de modifier un mot de passe, suite à une demande d'un utilisateur, il faut directement le modifier depuis la base de donnée.
Soit à l'aide d'une requête SQL du type `UPDATE user SET password = nouveauMotDePasse WHERE email = emailFourni`
Soit directement à l'aide du logiciel de base de données (MySql par exemple).

### Convention de codage  

#### PSR-1 coding (php)

`<?php` : Pas d’espace avant.  
classes : Noms de classes en PascalCase.  
méthodes: Noms de méthodes en camelCase.  
Noms de fichiers : Correspondent à la classe (ex : MaClasse.php).  
Pas de code avant `<?php ni après ?>`.
Encodage : UTF-8 sans BOM.  

#### Symfony

Utilise PSR-1  
Variables : Noms en camelCase (ex : getUserName()).  
Fichiers : Noms de fichiers en PascalCase, correspondant aux classes (ex : UserController.php).  
Indentation : tabulations.  
Commentaires : Utiliser PHPDoc pour documenter classes, méthodes et propriétés.  
Namespaces : Utiliser PascalCase (ex : App\Controller).  
Injection de dépendances : Via le constructeur.  
Services : Déclaration dans services.yaml en snake_case.  
  
  Commande à exécuter pour corriger le code : `$ vendor/bin/phpcbf --standard=PSR1 src/`

#### HTML

Commande à exécuter pour corriger le code : `npx htmlhint chemin/vers/fichier.html`

Structure : on commence toujours par `<!DOCTYPE html>`, puis balises `<html>`, `<head>`, `<body>`.  
Balises sémantiques : Utiliser `<header>`, `<footer>`, `<section>`, etc.  
Indentation : tabulation.  
Attributs : En minuscules, valeurs entre guillemets.  
Commentaires : Utilisation de `<!-- commentaire -->` pour expliquer le code.  
Fermeture des balises : Toujours fermer les balises (même les non-fermantes).  
Ne pas utiliser de balises inline (`style`, `onclick`).  

#### CSS

Indentation : tabulation.  
Nommage : Utiliser BEM ou classes simples, en minuscules, avec tirets (my-class).  
Propriétés : Suivre l'ordre logique (position, dimension, typographie, couleur).  
Commentaires : Utiliser pour séparer et expliquer les sections.  
Minification : Minifier le CSS en production.  
Pas d'espace avant et après les : dans les règles CSS.  
HTML : Ne pas utiliser de balises inline (style, onclick).  
Préfére

### Tester la cohérence de la Base de données

Le projet contient un dossier tests contenant des scripts python, il est possible de réaliser les tests de 2 méthodes différentes :

> **Attention!!!**
>> Les tests *videront* les tables concernées
>> Il est **TRÈS recommandé** d'utiliser une base de donnée de tests et non celle pour la production pour les tests 

1. Exécuter tous les tests :`$ python3 run_all_tests.py`

2. Exécuter un test particulier :`$ python3 table_a_tester.py`
