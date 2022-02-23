<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/GeneratePassword.php';
require 'global_variables.php';
require 'utility.php';

get_base_files();

$dotenv = Dotenv\Dotenv::createImmutable(getcwd());
$dotenv->load();

shell_exec('docker-compose up -d');

copy_wordpress_files();

install_wordpress();

install_noiza_custom_theme();