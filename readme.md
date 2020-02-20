# Roger

Roger est l'assistant de Matiere Noire pour créer les nouveaux projets.

## Pre-requis

Voici la liste de ce qui doit être installé sur votre machine afin que le script marche correctement :

- [composer](https://getcomposer.org)
- [WP Cli](https://wp-cli.org/fr/#installation)
- [WP-CLI Dotenv](https://github.com/aaemnnosttv/wp-cli-dotenv-command#installation)
- [Hub](https://hub.github.com/)
- [Clever-tools](https://www.clever-cloud.com/doc/clever-tools/getting_started/#installing-clever-tools) ( pensez a bien vous logger : `clever login`)

## Installation du Phar

Télécharger le fichier "roger.phar" depuis [la dernière release sur Github](https://github.com/matiere-noire/roger/releases). 

On le rend exécutable et on le déplace dans un dossier accessible par le $PAHT

`chmod +x roger.phar`

`sudo mv roger.phar /usr/local/bin/roger`

## Utilisation

- Configurer l'assistant : `roger config`
- Créer un projet WordPress : `roger create:wp`


## PhpStrom

Le script peut créer un projet PhpStrom et l'ouvrir pour vous. Pour cela activer l'[utilitaire de ligne de commande PhpStorm](https://www.jetbrains.com/help/phpstorm/working-with-the-ide-features-from-command-line.html) et dans votre robo.yml renseigner la commande que vous avez choisi dans l'option **phpstromCmd**

## VSCode

Le script peut créer un projet VSCode et l'ouvrir pour vous. Pour cela activer l'[utilitaire de ligne de commande VSCode](https://code.visualstudio.com/docs/setup/mac) et dans votre robo.yml passer à true l'option **vscode**