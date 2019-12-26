# WP Start

## Pre-requis

Voici la liste de ce qui doit être instalé sur votre machine afin que le script marche correctement :

* [composer](https://getcomposer.org) 
* [WP Cli](https://wp-cli.org/fr/#installation)

## Utilisation

* Cloner le dossier en local
* Créer un fichier `config.conf` en utilisant le fichier `config.conf.example` 
* Renseigner les bonnes informations dans le fichier `config.conf`
* lancer la commande `./wp-start-project.sh {nom-du-projet}` 

## Options 

### --git-remote

En ajoutant comme paramétre `--git-remote` a la commande de `./wp-start-project.sh` vous pouvez préciser un depot remote pour votre dépôt git. Si ce paramétre est passé on ajoutera un remote a notre dépôt local et on y publiera la branche master avec le commit "init". 
L'URL renseigné dois donc pointer sur un dépot git vide.`

exemple :
```bash
./wp-start-project.sh renoval --git-remote git@github.com:matiere-noire/renoval.git
```

## Theme

Le starter utilise Hybrid de Justin Tadlock. Sa doc est [ici](https://github.com/justintadlock/hybrid-core/wiki)
