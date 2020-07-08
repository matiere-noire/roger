#!/usr/bin/env php
<?php
if (class_exists('Phar')) {
    Phar::mapPhar('roger.phar');
    require 'phar://' . __FILE__ . '/index.php';
}
__HALT_COMPILER(); ?>