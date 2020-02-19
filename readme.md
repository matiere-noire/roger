# WP Start

Le script crée un répertoire avec un WordPress configuré automatiquement selon nos habitudes.
Le script utilise une configuration locale (.conf) comprenant, entre autres, l'endroit où installer le dossier.
Lors du lancement du script, une option permet de préciser le nom du projet qui sera aussi le nom du dossier. Ce dossier ne doit pas exister pour que le script fonctionne correctement.

## Pre-requis

Voici la liste de ce qui doit être installé sur votre machine afin que le script marche correctement :

- [composer](https://getcomposer.org)
- [WP Cli](https://wp-cli.org/fr/#installation)
- [WP-CLI Dotenv](https://github.com/aaemnnosttv/wp-cli-dotenv-command#installation)
- [Hub](https://hub.github.com/)
- [Clever-tools](https://www.clever-cloud.com/doc/clever-tools/getting_started/#installing-clever-tools) ( pensez a bien vous logger : `clever login`)

## Installation du Phar

On télécharge Phar 

`curl -O https://raw.githubusercontent.com/matiere-noire/roger/master/roger.phar`

On le rend exécutable et on le déplace dans un dossier accessible par le $PAHT

`chmod +x roger.phar`

`sudo mv roger.phar /usr/local/bin/roger`

## Utilisation

- Cloner le depot en local
- Lancer la commande `composer install`
- Renseigner les bonnes informations dans le fichier `robo.yml`
- lancer la commande `robo create:wp`


## Theme

Le starter theme de ce projet est [berry](https://github.com/matiere-noire/berry) basé sur [Mythic](https://themehybrid.com/themes/mythic) de Justin Tadlock. [Ici la doc](https://github.com/justintadlock/mythic/wiki)

Ce starter theme est basé sur le framework [Hybrid](https://github.com/justintadlock/hybrid-core) toujours de Justin Tadlock. [Sa doc est ici](https://github.com/justintadlock/hybrid-core/wiki)

## PhpStrom

Le script peut créer un projet PhpStrom et l'ouvrir pour vous. Pour cela activer l'[utilitaire de ligne de commande PhpStorm](https://www.jetbrains.com/help/phpstorm/working-with-the-ide-features-from-command-line.html) et dans votre robo.yml renseigner la commande que vous avez choisi dans l'option **phpstromCmd**

## VSCode`

Le script peut créer un projet VSCode et l'ouvrir pour vous. Pour cela activer l'[utilitaire de ligne de commande VSCode](https://code.visualstudio.com/docs/setup/mac) et dans votre robo.yml passer à true l'option **vscode**