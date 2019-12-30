# WP Start

Le script crée un répertoire avec un WordPress configuré automatiquement selon nos habitudes.
Le script utilise une configuration locale (.conf) comprenant, entre autres, l'endroit où installer le dossier.
Lors du lancement du script, une option permet de préciser le nom du projet qui sera aussi le nom du dossier. Ce dossier ne doit pas exister pour que le script fonctionne correctement.

## Pre-requis

Voici la liste de ce qui doit être installé sur votre machine afin que le script marche correctement :

- [composer](https://getcomposer.org)
- [WP Cli](https://wp-cli.org/fr/#installation)

## Utilisation

- Cloner le dossier en local
- Créer un fichier `config.conf` en utilisant le fichier `config.conf.example`
- Renseigner les bonnes informations dans le fichier `config.conf`
- lancer la commande `./wp-start-project.sh {nom-du-projet}`

## Options

### --git-remote

En ajoutant comme paramétre `--git-remote` a la commande de `./wp-start-project.sh` vous pouvez préciser un depot remote pour votre dépôt git. Si ce paramétre est passé on ajoutera un remote a notre dépôt local et on y publiera la branche master avec le commit "init".
L'URL renseigné dois donc pointer sur un dépot git vide.`

exemple :

```bash
./wp-start-project.sh renoval --git-remote git@github.com:matiere-noire/renoval.git
```

## Theme

Le starter theme de ce projet est [Mythic](https://themehybrid.com/themes/mythic) de Justin Tadlock. [Ici la doc](https://github.com/justintadlock/mythic/wiki)

On prévois de forker et personalisé d'avantage ce thème pour correspondre à nos besoins : https://github.com/matiere-noire/mythic

Ce starter theme est basé sur le framework [Hybrid](https://github.com/justintadlock/hybrid-core) toujours de Justin Tadlock. [Sa doc est ici](https://github.com/justintadlock/hybrid-core/wiki)