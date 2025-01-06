# RobotLeague

Ce document est un tutoriel pour utiliser **Robot League**.
**Robot League** est une web app permettant de gérer des tournois de football.
Cela inclut la gestion des utilisateurs, des équipes participantes et de l'affichage des équipes qui doivent s'affronter durant un tournoi. Les résultats seront affichés sur la page d'accueil.

## Sommaire
### [Prérequis](#prérequis)  
### [Interface administrateur](#interface-administrateur)  
- [Ajout de membres](#ajout-de-membres)
- [titre](#sous-titre-2)

### [Autres informations](#autres-informations)

### [Convention de codage](#convention-de-codage)

### Prérequis

Afin d'utiliser RobotLeague, il est requis d'avoir des compétences de base en `SQL` (MySQL). Cette application est développée en `Symfony`, si vous souhaitez apporter des modifications à celle-ci il est préférable d'avoir une connaissance de base du `PHP`.


### Interface Administrateur

#### Ajout de membres
#### Sous-titre 2



#### Autres informations
La version de Symfony utilisée est : `___`  
La base de donnée est gérée avec : `___`  
Le framework CSS utilisé est : `SimpleCSS` / `Tailwind`

### Convention de codage 

PSR-1 coding (php): 

<?php : Pas d’espace avant.
classes : Noms de classes en PascalCase.
méthodes: Noms de méthodes en camelCase.
Noms de fichiers : Correspondent à la classe (ex : MaClasse.php).
Pas de code avant <?php ni après ?>.
Encodage : UTF-8 sans BOM.

Symfony : 

Utilise PSR-1
Variables : Noms en camelCase (ex : getUserName()).
Fichiers : Noms de fichiers en PascalCase, correspondant aux classes (ex : UserController.php).
Indentation : tabulations.
Commentaires : Utiliser PHPDoc pour documenter classes, méthodes et propriétés.
Namespaces : Utiliser PascalCase (ex : App\Controller).
Injection de dépendances : Via le constructeur.
Services : Déclaration dans services.yaml en snake_case.

HTML :

Structure : on commence toujours par `<!DOCTYPE html>`, puis balises `<html>`, `<head>`, `<body>`.
Balises sémantiques : Utiliser `<header>`, `<footer>`, `<section>`, etc.
Indentation : tabulation.
Attributs : En minuscules, valeurs entre guillemets.
Commentaires : Utilisation de `<!-- commentaire -->` pour expliquer le code.
Fermeture des balises : Toujours fermer les balises (même les non-fermantes).
Ne pas utiliser de balises inline (`style`, `onclick`).

CSS :

Indentation : tabulation.
Nommage : Utiliser BEM ou classes simples, en minuscules, avec tirets (my-class).
Propriétés : Suivre l'ordre logique (position, dimension, typographie, couleur).
Commentaires : Utiliser pour séparer et expliquer les sections.
Minification : Minifier le CSS en production.
Pas d'espace avant et après les : dans les règles CSS.
HTML : Ne pas utiliser de balises inline (style, onclick).
Préférences fichiers externes,éviter le code inline.


