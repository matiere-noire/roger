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
class LaravelBoilerplateCommands extends Tasks
{
    private $projectName;

    private $projectDir;

    private $siteUrl;

    private $configPath;

    private $frontUrl;
    
    public function __construct()
    {

        $this->configPath = "{$_SERVER['HOME']}/.roger/robo.yml";
    }

    /**
     * Créée un projet Laravel
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
        $this->frontUrl     = $this->askDefault('Url du front ?', 'localhost:3000');
        $this->isValet      = $this->askDefault('Utilisation de Valet ?', false);

        if ($this->taskExec( "mysql -u {$opt['dbuser']} -p{$opt['dbpass']} -e \"use $dbname\" ")->run()->wasSuccessful()) {
            exit("La base $dbname existe déjà, fin du script");
        }
        else {
            $this->taskExecStack()
            ->stopOnFail( true )
            ->exec("mysql -u {$opt['dbuser']} -p{$opt['dbpass']} -e \"CREATE DATABASE IF NOT EXISTS $dbname\" ")
            ->run();
        }

        // Clone du boilerplate 
        $this->taskExecStack()
        ->stopOnFail( true )
        ->exec('git clone https://github.com/matiere-noire/Laravel-MN-boilerplate.git '.$this->projectName)
        ->exec('rm -rf .git')
        ->_copy('env.example', '.env')
        ->exec('composer install')
        ->exec('yarn')
        ->exec('php artisan postman:collection:export '.$this->projectName.' --api')
        ->dir($this->projectDir)
        ->run();
        
        // Modif .env
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

        $this->taskExecStack()
        ->stopOnFail()
        ->exec($this->isValet ? "valet link mon-projet" : "")
        ->exec("php artisan migrate:fresh --seed")
        ->exec("php artisan passport:keys")
        ->exec('php artisan passport:client --password --name="'.$this->projectName.'" --provider="user" -n')
        ->dir( $this->projectDir )
        ->run();

        $this->say('Lancement du site...');
        $this->taskExec("open http://$this->siteUrl/admin/login")->run();
        $this->say("Accès admin : developer@matierenoire.io | matnoire44");
        $this->say("Postman : une collection Postman a été crée automatiquement dans storage/app/");
        $this->say("Les clés d'Oauth et d'API ont été généré automatiquement.");
        $this->say(">> Ouvrir le README.md pour en savoir plus sur le fonctionnement du boilerplate");
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



