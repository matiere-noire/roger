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
class LaravelCommands extends Tasks
{
    private $projectName;

    private $projectDir;

    private $siteUrl;

    private $configPath;

    private $frontUrl;
    
    private $mFolder;

    public function __construct()
    {

        $this->configPath = "{$_SERVER['HOME']}/.roger/robo.yml";
        $this->mFolder = "{$_SERVER['HOME']}/.roger/files-to-copy";
    }

    /**
     * Créée un projet WordPress
     *
     * A demonstration of a command in a Robo script.
     *
     * @param array $opt
     */
    public function createLaravel( $opt = [
        'WORK_DIR'      => '~/Sites/',
        'phpstromCmd'   => false,
        'vscode'        => false,
        'dbuser'        => 'root',
        'dbpass'        => 'root',
        'dbhost'        => 'localhost:3306',
        'user'        => 'admin',
        'pass'        => 'password',
        'email'       => 'dev@matierenoire.io'])
    {

        if( $this->getConfig() === null ){
            $this->config();
        }

        $this->projectName  = $this->askDefault('Nom du nouveau projet ?: ', 'laravel');
        $this->siteUrl      = $this->askDefault('Domaine local ( sans http ) ? ', "{$this->projectName}.test");
        $dbname             = $this->askDefault('Nom la base de donnée a créer ? ', $this->projectName);
        $this->projectDir   = $this->askDefault('Dossier d‘installation ?', $opt['WORK_DIR'] . $this->projectName);
        $this->frontUrl     = $this->askDefault('Url du front?', 'localhost:3000');

        if ($this->taskExec( "mysql -u {$opt['dbuser']} -p{$opt['dbpass']} -e \"use $dbname\" ")->run()->wasSuccessful()) {
            exit("La base $dbname existe déjà, fin du script");
        }else { 
            $this->taskExecStack()
            ->stopOnFail( true )
            ->exec("mysql -u {$opt['dbuser']} -p{$opt['dbpass']} -e \"CREATE DATABASE IF NOT EXISTS $dbname\" ")
            ->run();
        }

        //Laravel 
        $this->taskExecStack()
        ->stopOnFail()
        ->exec("laravel new {$this->projectName}")
        ->dir( $opt['WORK_DIR'] )
        ->run();

        //Fichier .env
        $this->taskReplaceInFile("$this->projectDir/.env")
        ->from([
            'http://localhost',
            'DB_DATABASE=laravel',
            'DB_PASSWORD=',
            'DB_USERNAME=root'
        ])
        ->to([
            "http://$this->siteUrl",
            "DB_DATABASE=$dbname",
            "DB_PASSWORD={$opt['dbpass']}",
            "DB_USERNAME={$opt['dbuser']}"
        ])
        ->run();

        $this->_copyDir("$this->mFolder/laravel/app/php/Providers/", "$this->projectDir/app/Providers/");

        $this->taskComposerRequire()
            ->dependency( 'brackets/craftable' )
            ->dependency( 'brackets/admin-generator' )
            ->dependency( 'sentry/sentry-laravel' )
            ->dependency( 'laravel/passport' )
            ->dependency( 'mpociot/laravel-apidoc-generator' )
            ->dir( $this->projectDir )
            ->run();
       
        $this->taskExecStack()
        ->stopOnFail()
        ->exec("php artisan craftable:install")
        ->dir( $this->projectDir )
        ->run();

        $this->taskReplaceInFile("$this->projectDir/config/app.php")
        ->from([
            "App\Providers\RouteServiceProvider::class,",
            "View::class,"
        ])
        ->to([
            "App\Providers\RouteServiceProvider::class, Sentry\Laravel\ServiceProvider::class,",
            "View::class, 'Sentry' => Sentry\Laravel\Facade::class,"
        ])
        ->run();

        $this->_copyDir("$this->mFolder/laravel/app/php/Exceptions/", "$this->projectDir/app/Exceptions/");

        $this->taskExecStack()
            ->stopOnFail()
            ->exec("php artisan vendor:publish --provider=\"Sentry\Laravel\ServiceProvider\"")
            ->dir( $this->projectDir )
            ->run();
        
        # Import des models customs
        $this->_copyDir("$this->mFolder/laravel/app/php/Models/", "$this->projectDir/app/Models/");
        $this->_copyDir("$this->mFolder/laravel/app/php/config/", "$this->projectDir/config/");
        $this->_copyDir("$this->mFolder/laravel/app/php/database/migrations/", "$this->projectDir/database/migrations/");
        
        $this->taskExecStack()
            ->stopOnFail()
            ->exec("php artisan migrate")
            ->exec("php artisan passport:install")
            ->exec("npm install")
            ->exec("php artisan admin:generate users --no-interaction")
            ->exec("php artisan admin:generate roles --no-interaction")
            ->exec("php artisan admin:generate permissions --no-interaction")
            ->exec("npm run dev")
            ->dir( $this->projectDir )
            ->run();
        
        $this->_copyDir("$this->mFolder/laravel/app/php/Http/Controllers/", "$this->projectDir/app/Http/Controllers/");
        $this->_copyDir("$this->mFolder/laravel/app/php/Http/Resources/", "$this->projectDir/app/Http/Resources/");
        $this->_copyDir("$this->mFolder/laravel/app/php/routes/", "$this->projectDir/routes/");
        $this->_copyDir("$this->mFolder/laravel/app/php/database/seeds/", "$this->projectDir/database/seeds/");
        
        $this->taskExecStack()
            ->stopOnFail()
            ->exec("php artisan migrate")
            ->exec("php artisan vendor:publish --provider=\"Mpociot\ApiDoc\ApiDocGeneratorServiceProvider\" --tag=apidoc-config")
            ->exec("php artisan apidoc:generate")
            ->dir( $this->projectDir )
            ->run();
            
        
        $this->taskReplaceInFile("$this->projectDir/database/seeds/UsersTableSeeder.php")
            ->from('matnoire_email')
            ->to("{$opt['email']}")
            ->run();
        
        $this->taskExecStack()
        ->stopOnFail()
        ->exec("composer dump-autoload")
        ->exec("php artisan db:seed --class=UsersTableSeeder")
        ->dir( $this->projectDir )
        ->run();
        
        $this->say('Lancement du site');
        $this->taskExec("open http://$this->siteUrl/admin/login")->run();
        $this->say("Administateur infos email : {$opt['email']} // Pass : matnoire44");
    }

    private function getConfig()
    {
        $config = null;

        if( file_exists( $this->configPath ) ){
            $config = Yaml::parseFile($this->configPath);
        }

        return $config;

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
                'user'        => 'admin',
                'pass'        => 'admin',
                'email'       => 'dev@matierenoire.io'
            ]]]];
        }

        $createOptions = $config['command']['create'][ 'options'];

        $createOptions['WORK_DIR']      = $this->askDefault('Dossier par defaut d‘instalation: ', $createOptions['WORK_DIR']);
        $createOptions['vscode']        = $this->confirm('Utilisateur VSCode', $createOptions['vscode'] );
        $createOptions['phpstromCmd']   = $createOptions['vscode'] ? false : $this->askDefault('Commande phpStrom', $createOptions['phpstromCmd']);
        $createOptions['dbuser']        = $this->askDefault('DB User :', $createOptions['dbuser']);
        $createOptions['dbpass']        = $this->askDefault('DB Password :', $createOptions['dbpass']);
        $createOptions['dbhost']        = $this->askDefault('DB host :', $createOptions['dbhost']);
        $createOptions['user']        = $this->askDefault('Utilisateur admin a créer par defaut :', $createOptions['user']);
        $createOptions['pass']        = $this->askDefault('Mot de passe admin :',  $createOptions['pass']);
        $createOptions['email']       = $this->askDefault('Email admin :',  $createOptions['email']);

        $config['command']['create']['options'] = $createOptions;
        $yaml = Yaml::dump($config);

        $this->taskWriteToFile( $this->configPath)
            ->text($yaml)
            ->run();
    }
}



