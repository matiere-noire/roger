<?php


namespace Roger\Commands;


use Robo\Tasks;
use Symfony\Component\Console\Question\Question;

class GithubCommands extends Tasks
{


    private $githubOrganisation;

    public function __construct()
    {
        $this->githubOrganisation = 'matiere-noire';
    }


    /**
     * Créer un projet Github privé avec le nom demandé
     *
     * @param string|null $projectName Nom de projet Github a créer
     * @param array $opts
     * @option $localDepot Emplacement du depot local auquel le projet github sera ajouté comme git remote
     * @option $topics Topics Github a ajouté au dépots. Séparer les topics par une virgule. ex : project,WordPress
     */
    public function createGithub( $projectName = null, $opts = [ 'localDepot|d' => null, 'topics|t' => '' ]): void
    {
        $collection = $this->collectionBuilder();

        if( ! $projectName ){
            $projectName = $collection->ask( 'Nom de votre nouveau projet sur Github ? ');
        }

        $creatTask = $collection->taskExec( "gh repo create {$this->githubOrganisation}/{$projectName}" );
        if( $opts['localDepot'] ){
            $creatTask->dir( $opts['localDepot'] );
        }

        // Ajout des topics
        $topics = $opts['topics'];
        if( $topics ){

            $param = [
              'names' => explode( ',', $topics)
            ];
            $tmpFilePath = $collection->taskTmpFile()
                ->line( json_encode( $param ))
                ->getPath();

            $collection->taskExec("gh api repos/{$this->githubOrganisation}/{$projectName}/topics -H Accept:application/vnd.github.mercy-preview+json -X PUT --input {$tmpFilePath}");
        }

        // Ajout des droits a la team Production
        $collection->taskExec("gh api orgs/{$this->githubOrganisation}/teams/production/repos/{$this->githubOrganisation}/{$projectName} -H Accept:application/vnd.github.v3+json -X PUT -F permission='admin'");


        $collection->run();
    }
}