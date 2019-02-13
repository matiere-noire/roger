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

AUTH_KEY=$(cat /dev/urandom | env LC_CTYPE=C tr -dc 'a-zA-Z0-9!@#$%^&*()-_[]{}<>~`+=,.;:/?|' | fold -w 64 | head -n 1)
SECURE_AUTH_KEY=$(cat /dev/urandom | env LC_CTYPE=C tr -dc 'a-zA-Z0-9!@#$%^&*()-_[]{}<>~`+=,.;:/?|' | fold -w 64 | head -n 1)
LOGGED_IN_KEY=$(cat /dev/urandom | env LC_CTYPE=C tr -dc 'a-zA-Z0-9!@#$%^&*()-_[]{}<>~`+=,.;:/?|' | fold -w 64 | head -n 1)
NONCE_KEY=$(cat /dev/urandom | env LC_CTYPE=C tr -dc 'a-zA-Z0-9!@#$%^&*()-_[]{}<>~`+=,.;:/?|' | fold -w 64 | head -n 1)
AUTH_SALT=$(cat /dev/urandom | env LC_CTYPE=C tr -dc 'a-zA-Z0-9!@#$%^&*()-_[]{}<>~`+=,.;:/?|' | fold -w 64 | head -n 1)
SECURE_AUTH_SALT=$(cat /dev/urandom | env LC_CTYPE=C tr -dc 'a-zA-Z0-9!@#$%^&*()-_[]{}<>~`+=,.;:/?|' | fold -w 64 | head -n 1)
LOGGED_IN_SALT=$(cat /dev/urandom | env LC_CTYPE=C tr -dc 'a-zA-Z0-9!@#$%^&*()-_[]{}<>~`+=,.;:/?|' | fold -w 64 | head -n 1)
NONCE_SALT=$(cat /dev/urandom | env LC_CTYPE=C tr -dc 'a-zA-Z0-9!@#$%^&*()-_[]{}<>~`+=,.;:/?|' | fold -w 64 | head -n 1)

cat << _EOF_ > .env
DATABASE_URL=mysql://$DBUSER:$DBPASS@localhost:3306/$PROJECT_NAME

WP_HOME=$WP_URL
WP_SITEURL=\${WP_HOME}/wp

AUTH_KEY='$AUTH_KEY'
SECURE_AUTH_KEY='$SECURE_AUTH_KEY'
LOGGED_IN_KEY='$LOGGED_IN_KEY'
NONCE_KEY='$NONCE_KEY'
AUTH_SALT='$AUTH_SALT'
SECURE_AUTH_SALT='$SECURE_AUTH_SALT'
LOGGED_IN_SALT='$LOGGED_IN_SALT'
NONCE_SALT='$NONCE_SALT'
_EOF_

}

function addMythic(){
  cd web/app/themes
  composer create-project justintadlock/mythic $PROJECT_NAME
  cd $PROJECT_NAME
  yarn install
  yarn run rename
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
  GITREMOTE=''

  PARAMS=""
  while (( "$#" )); do
    case "$1" in
      -g|--git-remote)
        GITREMOTE=$2
        shift 2
        ;;
      --) # end argument parsing
        shift
        break
        ;;
      -*|--*=) # unsupported flags
        echo "Error: Unsupported flag $1" >&2
        exit 1
        ;;
      *) # preserve positional arguments
        PARAMS="$PARAMS $1"
        shift
        ;;
    esac
  done
  # set positional arguments in their proper place
  eval set -- "$PARAMS"

  # On crée notre dossier
  cd $WORK_DIR
  composer create-project roots/bedrock $PROJECT_NAME --no-scripts
  cd $PROJECT_NAME
  composer config repo.wp-composer.matnoire.com composer https://wp-composer.matnoire.com/
  dotenv

  # Instalation des plugins
  composer require wpackagist-plugin/query-monitor --dev
  composer require wpackagist-plugin/favicon-by-realfavicongenerator

  # Instalation du starter theme
  addMythic

  # On gére un nouveau depot git
  cd $WP_DIR
  git init
  git add -A
  git commit -m init
  if [ -n "$GITREMOTE" ]; then
    git remote add origin $GITREMOTE
    git push -u origin master
  fi

  echo "'$PROJECT_NAME' à bien été créer. A vous de faire pointer '$WP_URL' vers le dossier '$WP_WEB_DIR' et de créer une base de donnée avec comme nom '$PROJECT_NAME' accessible en 'localhost'"
  exit 0
else
  # Si il n'y a pas de nom de projet on affiche notre petite aide
  help
  exit 1
fi