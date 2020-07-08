<?php
namespace Roger\Commands;

use Robo\Exception\TaskException;
use Robo\Tasks;
use Symfony\Component\Yaml\Yaml;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class ProjectCommands extends Tasks
{

    private $projectName;

    private $projectDir;

    private $themeDir;

    private $siteUrl;

    private $headless;

    private $ecommerce;

    private $theme;

    private $configPath;

    public function __construct()
    {

        $this->configPath = "{$_SERVER['HOME']}/.roger/robo.yml";
    }

    private function getPluginsListToInstall(): array
    {
        return [
            'wpackagist-plugin/favicon-by-realfavicongenerator',
            'wpackagist-plugin/clean-image-filenames',
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
     * Créée un projet WordPress
     *
     * A demonstration of a command in a Robo script.
     *
     * @param array $opt
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

        if( $this->getConfig() === null ){
            $this->config();
        }

        $this->projectName  = $this->askDefault('Nom du nouveau projet ?: ', 'super');
        $this->siteUrl      = $this->askDefault('Domaine local ( sans http ) ? ', "{$this->projectName}.test");
        $dbname             = $this->askDefault('Nom la base de donnée a créer ? ', $this->projectName);
        $this->projectDir   = $this->askDefault('Dossier d‘installation ?', $opt['WORK_DIR'] . $this->projectName);

        $this->headless         = $this->confirm('Instation pour une API ( headless ) ? ', false);
        $this->ecommerce        = $this->confirm('Projet e-commerce ? ', false);
        if( $this->ecommerce ){
            $this->theme   = $this->io()->choice('Starter theme pour projet e-commerce ? ', ['storefront-child', 'berry', 'rien' ] );
        }
        if( $this->headless ){
            $this->theme    = false;
        } else if ( ! $this->theme ) {
            $addStarterTheme = $this->confirm('Installer le starter theme Berry ( recommandé ) ?', true);
            $this->theme = $addStarterTheme ? 'berry' : false;
        }
        $addPlugins         = $this->confirm('Installer les plugins ( recommandé ) ?', true);
        $multisite          = $this->confirm('WordPress en multisite ? ', false);
        $createGithub       = $this->confirm("Créer le projet sur Github ( matiere-noire/{$this->projectName} ) ? ", true);

        $createCleverCloudApp = $createGithub ? $this->confirm('Créer la preprod sur Clever Cloud ? ', true) : false;

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

        $this->taskComposerRequire()
            ->dependency( 'wp-cli/wp-cli' )
            ->dependency( 'wp-cli/language-command' )
            ->dependency( 'wp-cli/rewrite-command' )
            ->dir( $this->projectDir )
            ->run();

        // On rajoute nos scripts composer
        $composerJsonString = file_get_contents("{$this->projectDir}/composer.json");
        $composerJson = json_decode($composerJsonString, true);

        if( ! $this->headless ){
            $postInstallCmd[] = "cd web/app/themes/{$this->projectName} && composer install && yarn install && yarn prod";
        }
        $postInstallCmd[] = 'wp language core install fr_FR --activate && wp language plugin update --all';
        $composerJson['scripts']['post-install-cmd'] = $postInstallCmd;

        $newJsonString = json_encode($composerJson, JSON_PRETTY_PRINT);
        file_put_contents("{$this->projectDir}/composer.json", $newJsonString);

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


        // Suppression du dossier uploads
        $this->_exec("rm -r{$this->projectDir}/web/app/uploads");

        $this->taskReplaceInFile( "{$this->projectDir}/.gitignore")
            ->from('web/app/uploads/*')
            ->to('web/app/uploads')
            ->run();

        $this->taskReplaceInFile( "{$this->projectDir}/.gitignore")
            ->from('!web/app/uploads/.gitkeep')
            ->to('')
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

Config::apply();")
                ->run();

            $this->taskComposerRequire()
                ->dependency( 'roots/multisite-url-fixer' )
                ->dir( $this->projectDir )
                ->run();
        }

        if( $this->headless ){
            $this->taskReplaceInFile( "{$this->projectDir}/web/index.php")
                ->from('define(\'WP_USE_THEMES\', true);')
                ->to('define(\'WP_USE_THEMES\', false);')
                ->run();
        }

        // On install le starter theme
        if( $this->theme === 'berry' ){

            $this->addStarterTheme();

        } elseif ( $this->theme === 'storefront-child' ){
            $this->taskComposerRequire()
                ->dependency( 'wpackagist-theme/storefront' )
                ->dir( $this->projectDir )
                ->run();

            $this->taskExec( "wp scaffold child-theme {$this->projectName} --parent_theme=storefront --theme_name={$this->projectName} --author=MatiereNoire --activate" )
                ->dir( $this->projectDir )
                ->run();
        }

        // On install les plugins
        if( $addPlugins ){
            $this->addPlugins();
        }

        // Ecommerce
        if ( $this->ecommerce ){

            $this->taskComposerRequire()
                ->dependency( 'wpackagist-plugin/woocommerce' )
                ->dir( $this->projectDir )
                ->run();
        }

        // Readme
        $this->taskWriteToFile("{$this->projectDir}/README.md")
            ->textFromFile('./files-to-copy/readme.md')
            ->replace('##NAME##', $this->projectName)
            ->run();

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
            ->exec('git branch ')
            ->checkout('-b develop')
            ->dir( $this->projectDir )
            ->run();

        // github
        if( $createGithub ){
            $this->_exec("roger create:github {$this->projectName} -d $this->projectDir -t project,WordPress");

            $this->taskGitStack()
                ->push('origin','master')
                ->push('origin','develop')
                ->dir( $this->projectDir )
                ->run();
        }

        if( $createCleverCloudApp ){

            $this->_exec( "roger create:cc {$this->projectName} -d $this->projectDir --ccName {$this->projectName}-WP");
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
        $this->io()->success('Votre site est prêt !');
        $this->taskExec( "open http://{$this->siteUrl}" )->dir( $this->projectDir )->run();
    }

    private function addStarterTheme(){

        $this->taskComposerCreateProject()
            ->source('matiere-noire/berry')
            ->target($this->themeDir )
            ->run();

        if ( $this->ecommerce ){
            // https://github.com/justintadlock/mythic/wiki/WooCommerce
            $this->taskWriteToFile("{$this->themeDir}/app/functions-woocommerce.php")
                ->textFromFile('./files-to-copy/functions-woocommerce.php')
                ->run();

            $this->taskWriteToFile("{$this->themeDir}/resources/views/content/woocommerce.php")
                ->textFromFile('./files-to-copy/woocommerce.php')
                ->run();

            $this->taskWriteToFile("{$this->themeDir}/app/bootstrap-autoload.php")
                ->replace('\'functions-template\'', "'functions-template',\n\t'functions-woocommerce'")
                ->append()
                ->run();

        }

        $this->taskExecStack()
            ->stopOnFail( true )
            ->exec('yarn install')
            ->exec('yarn run rename' )
            ->dir( $this->themeDir )
            ->run();

        $this->taskExec( "wp theme activate {$this->projectName}" )
            ->dir( $this->projectDir )->run();

        $this->taskComposerDumpAutoload()->dir( $this->themeDir )->run();
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



    /**
     * Configurer l'assistant
     */
    public function config(){

        $config = $this->getConfig();

        if( ! $config ){
            $config = ['command' => [ 'create' => [ 'options' => [
                'WORK_DIR'      => "{$_SERVER['HOME']}/Sites/",
                'vscode'        => true,
                'phpstromCmd'   => 'pstrom',
                'dbuser'        => 'root',
                'dbpass'        => 'root',
                'dbhost'        => 'localhost:3306',
                'wpuser'        => 'admin',
                'wppass'        => 'admin',
                'wpemail'       => 'dev@matierenoire.io'
            ]]]];
        }

        $createOptions = $config['command']['create'][ 'options'];

        $createOptions['WORK_DIR']      = $this->askDefault('Dossier par defaut d‘instalation: ', $createOptions['WORK_DIR']);
        $createOptions['vscode']        = $this->confirm('Utilisateur VSCode', $createOptions['vscode'] );
        $createOptions['phpstromCmd']   = $createOptions['vscode'] ? false : $this->askDefault('Commande phpStrom', $createOptions['phpstromCmd']);
        $createOptions['dbuser']        = $this->askDefault('DB User :', $createOptions['dbuser']);
        $createOptions['dbpass']        = $this->askDefault('DB Password :', $createOptions['dbpass']);
        $createOptions['dbhost']        = $this->askDefault('DB host :', $createOptions['dbhost']);
        $createOptions['wpuser']        = $this->askDefault('Utilisateur WordPress a créer par defaut :', $createOptions['wpuser']);
        $createOptions['wppass']        = $this->askDefault('Mot de passe utilisateur WordPress :',  $createOptions['wppass']);
        $createOptions['wpemail']       = $this->askDefault('Email utilisateur WordPress :',  $createOptions['wpemail']);

        $config['command']['create'][ 'options'] = $createOptions;
        $yaml = Yaml::dump($config);

        $this->taskWriteToFile( $this->configPath)
            ->text($yaml)
            ->run();
    }

    private function getConfig()
    {
        $config = null;

        if( file_exists( $this->configPath ) ){
            $config = Yaml::parseFile($this->configPath);
        }

        return $config;

    }

}