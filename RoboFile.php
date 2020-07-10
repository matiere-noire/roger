<?php


use Symfony\Component\Finder\Finder;

class RoboFile extends \Robo\Tasks
{

    public function build()
    {

        $pharTask = $this->taskPackPhar('roger.phar')
            ->compress()
            ->stub('stub.php');

        $pharTask->addFile('index.php', 'index.php');

        $finder = Finder::create()
            ->name('*.php')
            ->in('src');

        foreach ($finder as $file) {
            $pharTask->addFile('src/'.$file->getRelativePathname(), $file->getRealPath());
        }

        $this->_copyDir('files-to-copy/', "{$_SERVER['HOME']}/.roger/files-to-copy/" );

        $finder = Finder::create()->files()
            ->name('*.php')
            ->in('vendor');

        foreach ($finder as $file) {
            $pharTask->addStripped('vendor/'.$file->getRelativePathname(), $file->getRealPath());
        }

        $finder = Finder::create()->files()
            ->name('*.php')
            ->name('*.md')
            ->in('files-to-copy');

        foreach ($finder as $file) {
            $pharTask->addStripped('files-to-copy/'.$file->getRelativePathname(), $file->getRealPath());
        }
        $pharTask->run();

        // verify Phar is packed correctly
        $code = $this->_exec('php roger.phar');
    }


}