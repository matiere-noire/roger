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

    private $headless;

    private function getPluginsListToInstall(): array
    {
        return [
            'wpackagist-plugin/favicon-by-realfavicongenerator',
            'wpackagist-plugin/worker',
            'arnaudban/custom-image-sizes',
            'arnaudban/wp-doc-viewer',
            'wpackagist-plugin/wordpress-seo'
        ];
    }


    private function getPluginsDevListToInstall(): array
    {
        return [ 'wpackagist-plugin/query-monitor' ];
    }

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

        $this->projectName  = $this->askDefault('Nom du nouveau projet ?: ', 'super');
        $this->siteUrl      = $this->askDefault('Domaine local ( sans http ) ? ', "{$this->projectName}.test");
        $dbname             = $this->askDefault('Nom la base de donnée a créer ? ', $this->projectName);
        $this->projectDir   = $this->askDefault('Dossier d‘installation ?', $opt['WORK_DIR'] . $this->projectName);

        $this->headless     = $this->confirm('Instation pour une API ( headless ) ? ', false);
        $addTheme           = $this->headless ? false : $this->confirm('Installer le starter theme Berry ( recommandé ) ?', true);
        $addPlugins         = $this->confirm('Installer les plugins ( recommandé ) ?', true);
        $multisite          = $this->confirm('WordPress en multisite ? ', false);
        $createGithub       = $this->confirm("Créer le projet sur Github ( matiere-noire/{$this->projectName} ) ? ", true);

        $this->themeDir     = "{$this->projectDir}/web/app/themes/$this->projectName";


        // On créer le projet en partant de bedrock
        $this->taskComposerCreateProject()
            ->source('roots/bedrock')
            ->target($this->projectDir )
            ->run();

        // On configure le projet
        $this->taskComposerConfig()
            ->set('name', "matierenoire/{$this->projectName}" )
            ->dir( $this->projectDir )
            ->run();

        $this->taskComposerConfig()
            ->set('name', "matierenoire/{$this->projectName}" )
            ->dir( $this->projectDir )
            ->run();

        $this->taskComposerConfig()
            ->repository('wp-composer.matnoire.com', 'https://wp-composer.matnoire.com/', 'composer' )
            ->dir( $this->projectDir )
            ->run();

        // On configure WordPress, on créer la base
        $install = $multisite ? 'multisite-install --skip-config' : 'install';
        $this->taskExecStack()
            ->stopOnFail( true )
            ->exec("wp dotenv set DATABASE_URL mysql://{$opt['dbuser']}:{$opt['dbpass']}@{$opt['dbhost']}/{$dbname}")
            ->exec("wp dotenv set WP_HOME http://{$this->siteUrl}")
            ->exec("wp dotenv set WP_SITEURL http://{$this->siteUrl}/wp")
            ->exec('wp db create')
            ->exec("wp core {$install} --url={$this->siteUrl} --title={$this->projectName} --admin_user={$opt['wpuser']} --admin_password={$opt['wppass']} --admin_email={$opt['wpemail']} --skip-email")
            ->exec('wp dotenv salts generate')
            ->exec('wp option update default_comment_status closed')
            ->exec('wp option update close_comments_for_old_posts 1')
            ->exec('wp option update close_comments_days_old 0')
            ->exec('wp option delete home')
            ->exec("wp option add home http://{$this->siteUrl}")
            ->exec('wp rewrite structure \'/%postname%/\'')
            ->exec('wp language core install fr_FR --activate')
            ->dir( $this->projectDir )
            ->run();

        if( $multisite ){
            $this->taskReplaceInFile( "{$this->projectDir}/config/application.php")
                ->from('Config::apply();')
                ->to("
/**
 * Multisite
 */
Config::define('WP_ALLOW_MULTISITE', true);
Config::define('MULTISITE', true);
Config::define('SUBDOMAIN_INSTALL', true);
Config::define('PATH_CURRENT_SITE', '/');
Config::define('SITE_ID_CURRENT_SITE', 1);
Config::define('BLOG_ID_CURRENT_SITE', 1);
Config::define('DOMAIN_CURRENT_SITE', '{$this->siteUrl}' );

Config::apply();")
                ->run();
        }

        if( $this->headless ){
            $this->taskReplaceInFile( "{$this->projectDir}/web/index.php")
                ->from('define(\'WP_USE_THEMES\', true);')
                ->to('define(\'WP_USE_THEMES\', false);')
                ->run();
        }

        // On install la theme par defaut
        if( $addTheme ){
            $this->addStarterTheme();
        }

        // On install les plugins
        if( $addPlugins ){
            $this->addPlugins();
        }

        // On crée le dépôt git
        $this->taskWriteToFile("{$this->projectDir}/.gitignore")
            ->line('web/app/languages')
            ->append()
            ->run();

        $this->taskGitStack()
            ->stopOnFail()
            ->exec( 'init' )
            ->add('-A')
            ->commit('init')
            ->dir( $this->projectDir )
            ->run();

        // github
        if( $createGithub ){
            $this->taskExec( "hub create matiere-noire/{$this->projectName} -o -p" )->dir( $this->projectDir )->run();

            $this->taskGitStack()
                ->push('origin','master')
                ->dir( $this->projectDir )
                ->run();
        }


        // On ouvre le projet dans PhpStorm
        if( $opt['phpstromCmd']){
            $this->taskExec( "{$opt['phpstromCmd']} {$this->projectDir}"  )->run();
        }

        // On ouvre le projet dans vscode
        if( $opt['vscode']){
            $this->taskExec( "code {$this->projectDir}"  )->run();
        }

        // Fin
        $this->yell('Votre site est prêt !', 21);
        $this->taskExec( "open http://{$this->siteUrl}" )->dir( $this->projectDir )->run();
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

    private function addPlugins(){

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

        $this->taskExecStack()
            ->stopOnFail( true )
            ->exec( 'wp plugin activate --all' )
            ->exec('wp language plugin update --all')
            ->dir( $this->projectDir )
            ->run();
    }


}