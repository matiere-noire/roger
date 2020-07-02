---
layout: default
title: Roger
---

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

Télécharger le fichier [roger.phar](https://github.com/matiere-noire/roger/releases/latest/download/roger.phar). 

On le rend exécutable et on le déplace dans un dossier accessible par le $PATH

`chmod +x roger.phar`

`mv roger.phar /usr/local/bin/roger`

## PhpStrom

Le script peut créer un projet PhpStrom et l'ouvrir pour vous. Pour cela activer l'[utilitaire de ligne de commande PhpStorm](https://www.jetbrains.com/help/phpstorm/working-with-the-ide-features-from-command-line.html) et renseigner la commande que vous avez choisi quand vous configurer votre instance de roget avec `roger config`

## VSCode

Le script peut créer un projet VSCode et l'ouvrir pour vous. Pour cela activer l'[utilitaire de ligne de commande VSCode](https://code.visualstudio.com/docs/setup/mac).
