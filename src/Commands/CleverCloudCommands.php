<?php


namespace Roger\Commands;


use Robo\Exception\TaskException;
use Robo\Tasks;
use Roger\Services\GithubServices;
use Symfony\Component\Console\Question\Question;

class CleverCloudCommands extends Tasks
{

    private $ccOrganisation;

    public function __construct()
    {
        $this->ccOrganisation = 'orga_36652de4-73cd-4058-8f16-7ec47d8d2816';
    }

    /**
     * Création d'un projet Clever Cloud avec une addon MySQL et FS Bucket
     *
     * @param string|null $githubName Nom du projet Github
     * @param array $opt
     * @option $localProject Emplacement du depot local auquel sera ajouter le .clever.json
     * @option $ccName Nom du nouveau projet Clever Cloud
     *
     * @throws TaskException
     */
    public function createCC( $githubName = null, $opt = [ 'localProject|d' => null, 'ccName' => null ] ): void
    {

        if( ! $githubName ){
            $result = $this
                ->taskExec('hub api -X GET /search/repositories?q=user:matiere-noire+topic:project')
                ->printOutput(false)
                ->printMetadata( false )
                ->run();
            $data = json_decode( $result->getMessage(), false);

            $prjectsNames = array_map( static function ($item) {
                return $item->name;
            }, $data->items );

            $question = new Question('Nom de votre projet sur Github ');
            $question->setAutocompleterValues( $prjectsNames );

            $githubName = $this->doAsk( $question );
        }


        $localProject = $opt['localProject'] ?? $this->ask('path du projet en local ?');
        $ccName = $opt['ccName'] ?? $this->askDefault('Nom du nouveau projet Clever Cloud ?', "{$githubName}-WP");

        $ccDomain = "{$githubName}.cleverapps.io";

        $ccTask = $this->taskExecStack()
            ->stopOnFail( true )
            ->exec("clever create --type php {$ccName} --org {$this->ccOrganisation} --github matiere-noire/{$githubName} --alias {$githubName}" )
            ->exec('clever scale --flavor nano')
            ->exec('clever config set force-https enabled')
            ->exec("clever addon create mysql-addon --plan dev {$githubName}-MySQL --link {$githubName} --org {$this->ccOrganisation}")
            ->exec("clever addon create fs-bucket --plan s {$githubName}-fs --link {$githubName} --org {$this->ccOrganisation}")
            ->exec("clever domain add {$ccDomain}")
            ->exec('clever env set WP_ENV production')
            ->exec("clever env set WP_HOME https://{$ccDomain}")
            ->exec("clever env set WP_SITEURL https://{$ccDomain}/wp")
            ->exec('clever env set CC_WEBROOT /web');

        // TODO ajouter les variable d'environement suivante en récupérer les valeurs avec la commande "clever env"
        // ->exec("clever env set DATABASE_URL $MYSQL_ADDON_URI")
        // ->exec("clever env set CC_FS_BUCKET /web/app/uploads:$BUCKET_HOST")

        if( $localProject ){
            $ccTask->dir( $localProject );
        }

        $ccTask->run();
    }
}