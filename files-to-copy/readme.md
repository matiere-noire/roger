# ##NAME##

## Information technique

Ce projet utilise le CMS [WordPress](https://wordpress.org)

La structure du projet est celle de [Bedrock](https://roots.io/bedrock/) pour nous permettre d'utiliser composer

Le starter theme [Berry](https://github.com/matiere-noire/berry) basé sur [Mythic](https://github.com/justintadlock/mythic) qui utilise le framework [Hybrid Core](https://themehybrid.com/hybrid-core)

## Installation pour dev local

1. Cloner le depot
1. Créer un fichier `.env` a partir du `.env.example
  * `DB_NAME` - {Database name}
  * `DB_USER` - {Database user}
  * `DB_PASSWORD` - {Database password}
  * `WP_ENV=development
  * `WP_HOME=http://utopiales.test`
  * `WP_SITEURL=${WP_HOME}/wp
  * `AUTH_KEY`, `SECURE_AUTH_KEY`, `LOGGED_IN_KEY`, `NONCE_KEY`, `AUTH_SALT`, `SECURE_AUTH_SALT`, `LOGGED_IN_SALT`, `NONCE_SALT`
    * Generate with [wp-cli-dotenv-command](https://github.com/aaemnnosttv/wp-cli-dotenv-command)
    * Generate with [our WordPress salts generator](https://roots.io/salts.html)
1. Dans le dossier du theme `web/app/themes/##NAME##/` lancer les commandes suivante :
    1. `composer install`
    1. `yarn install`    
    1. `yarn run watch:sync`    

## Requirements

* PHP >= 7.1
* Composer - [Install](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)
* [Node 8+](https://nodejs.org).
* [Yarn](https://yarnpkg.com/en/) for managing JS dependencies.


## Documentation

* [Bedrock](https://roots.io/bedrock/docs/).
* [Mythic](https://github.com/justintadlock/mythic/wiki)
* [Hybrid Core](https://themehybrid.com/hybrid-core)