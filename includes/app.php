<?php 
use Model\ActiveRecord;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv::createImmutable(__DIR__); // Acá ya podemos hacer uso de las variable de entorno
$dotenv->safeLoad();

require 'funciones.php';
require 'database.php';

// Conectarnos a la base de datos
ActiveRecord::setDB($db);