#!/usr/bin/env bash

## Les fonctions ###############################################################

# Affichage de l'aide
function help(){
  # On ne peut rien faire sans le nom du projet
  echo "WordPress Start - Installer ou créer en une ligne votre projet WordPress en local"
  echo ""
  echo "Pour l'utiliser il faut entrer comme paramétre le nom du projet"
  echo "    $0 {nom-du-projet}"
}

function dotenv(){
  wp dotenv init --template=.env.example --with-salts
  wp dotenv delete DB_NAME DB_USER DB_PASSWORD
  wp dotenv set DATABASE_URL "mysql://$DBUSER:$DBPASS@localhost:3306/$PROJECT_NAME"
  wp dotenv set WP_HOME $WP_URL
}

function addMythic(){
  cd web/app/themes
  composer create-project justintadlock/mythic $PROJECT_NAME
  cd $PROJECT_NAME
  npm install
  npm run rename
  composer dump-autoload
}


## Le script ###################################################################

# On test si le seul argument est présent
if [ $1 ]; then

  # On récupére notre fichier de config
  BASEDIR=$(dirname $0)
  CONFIG_FILE=$BASEDIR/config.conf

  if [[ -f $CONFIG_FILE ]]; then
          . $CONFIG_FILE
  fi

  # On crée nos variables
  PROJECT_NAME="$1"
  WP_URL="http://$PROJECT_NAME.test"
  WP_DIR="$WORK_DIR$PROJECT_NAME"
  WP_WEB_DIR="$WORK_DIR$PROJECT_NAME/web"


  # On crée notre dossier
  cd $WORK_DIR
  composer create-project roots/bedrock $PROJECT_NAME --no-scripts
  cd $PROJECT_NAME
  composer config repo.wp-composer.matnoire.com composer https://wp-composer.matnoire.com/
  dotenv

  # Instalation des plugins
  composer require wpackagist-plugin/query-monitor --dev

  # Instalation du starter theme
  addMythic

  echo "$PROJECT_NAME crée à l'adresse : $WP_URL"
  exit 0
else
  # Si il n'y a pas de nom de projet on affiche notre petite aide
  help
  exit 1
fi