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
     * @param array $opt
     * @option $localDepot Emplacement du depot local auquel le projet github sera ajouté comme git remote
     */
    public function createGithub( $projectName = null, $opt = [ 'localDepot|d' => null ]): void
    {
        if( ! $projectName ){
            $projectName = $this->ask( 'Nom de votre nouveau projet sur Github ? ');
        }

        $creatTask = $this->taskExec( "hub create {$this->githubOrganisation}/{$projectName} -o -p" );
        if( $opt['localDepot'] ){
            $creatTask->dir( $opt['localDepot'] );
        }
        $creatTask->run();
    }
}