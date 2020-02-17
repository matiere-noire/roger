<?php

use Robo\Exception\TaskException;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends Robo\Tasks
{

    private $projectName;

    private $projectDir;

    private $themeDir;

    private $siteUrl;

    /**
     * Hello
     *
     * A demonstration of a command in a Robo script.
     *
     * @param array $opt
     * @throws TaskException
     */
    public function createWP( $opt = [
        'WORK_DIR'      => '~/Sites/',
        'phpstromCmd'   => false,
        'vscode'        => false,
        'dbuser'        => 'root',
        'dbpass'        => 'root',
        'dbhost'        => 'localhost:3306',
        'wpuser'        => 'admin',
        'wppass'        => 'password',
        'wpemail'       => 'dev@matierenoire.io'])
    {

        $this->projectName = $this->askDefault('Nom du nouveau projet ?: ', 'super');
        $this->siteUrl = "http://{$this->projectName}.test";
        $this->projectDir = $opt['WORK_DIR'] . $this->projectName;
        $this->themeDir = "{$this->projectDir}/web/app/themes/$this->projectName";

        $dbname = $this->askDefault('Nom la base de donnée a créer ?: ', $this->projectName);

        // On créer le projet en partant de bedrock
        $this->taskComposerCreateProject()
            ->source('roots/bedrock')
            ->target($this->projectDir )
            ->run();

        // On configure le projet
        $this->taskComposerConfig()
            ->set('name', "matierenoire/{$this->projectName}" )
            ->set('description', "Projet {$this->projectName}" )
            ->repository('wp-composer.matnoire.com', 'https://wp-composer.matnoire.com/', 'composer' )
            ->dir( $this->projectDir )
            ->run();


        // On configure WordPress, on créer la base
        $this->taskExecStack()
            ->stopOnFail( true )
            ->exec("wp dotenv set DATABASE_URL mysql://{$opt['dbuser']}:{$opt['dbpass']}@{$opt['dbhost']}/{$dbname}")
            ->exec("wp dotenv set WP_HOME {$this->siteUrl}")
            ->exec('wp db create')
            ->exec("wp core install --url={$this->projectName}.test --title={$this->projectName} --admin_user={$opt['wpuser']} --admin_password={$opt['wppass']} --admin_email={$opt['wpemail']}")
            ->exec('wp dotenv salts generate')
            ->dir( $this->projectDir )
            ->run();

        // On install la theme par defaut
        $this->addStarterTheme();


        // On install les plugins
        $cr = $this->taskComposerRequire();
        foreach ( $this->getPluginsListToInstall() as $plugin ){
            $cr->dependency( $plugin );
        }
        $cr->dir( $this->projectDir )
            ->run();


        foreach ( $this->getPluginsDevListToInstall() as $plugin ){
            $this->taskComposerRequire()
                ->dependency( $plugin )
                ->dev()
                ->dir( $this->projectDir )
                ->run();
        }

        $this->taskExec( 'wp plugin activate --all' )->dir( $this->projectDir )->run();

        // On crée le dépôt git
        $this->taskGitStack()
            ->stopOnFail()
            ->exec( 'init' )
            ->add('-A')
            ->commit('init')
            ->dir( $this->projectDir )
            ->run();
        $this->taskExec( "hub create matiere-noire/{$this->projectName} -o" )->dir( $this->projectDir )->run();

        $this->taskGitStack()
            ->push('origin','master')
            ->dir( $this->projectDir )
            ->run();

        // On ouvre le projet dans PhpStorm
        if( $opt['phpstromCmd']){
            $this->taskExec( "{$opt['phpstromCmd']} {$this->projectDir}"  )->run();
        }

        // On ouvre le projet dans vscode
        if( $opt['vscode']){
            $this->taskExec( "code {$this->projectDir}"  )->run();
        }

        // Fin
        $this->yell('Votre site est prêt !');
        $this->taskExec( "open {$this->siteUrl}" )->dir( $this->projectDir )->run();
    }

    private function addStarterTheme(){

        $this->taskComposerCreateProject()
            ->source('matiere-noire/berry')
            ->target($this->themeDir )
            ->run();

        $this->taskExecStack()
            ->stopOnFail( true )
            ->exec('yarn install')
            ->exec('yarn run rename' )
            ->dir( $this->themeDir )
            ->run();

        $this->taskComposerDumpAutoload()->dir( $this->themeDir )->run();

        $this->taskExec( "wp theme activate {$this->projectName}" )->dir( $this->projectDir )->run();
    }

    private function getPluginsListToInstall(){
        return [
            'wpackagist-plugin/favicon-by-realfavicongenerator',
            'wpackagist-plugin/worker',
            'arnaudban/custom-image-sizes',
            'arnaudban/wp-doc-viewer'
        ];
    }
    private function getPluginsDevListToInstall(){
        return [ 'wpackagist-plugin/query-monitor' ];
    }
}