<?php
/**
 * Sätt på felrapportering.
 *
 */
error_reporting(-1);			//Rapporterar alla typer av fel
ini_set('display_errors', 1);	//Visa alla fel
ini_set('output_buffering',0);	//Buffra inte utskrifter utan skriv direkt

/** 
 * Kan inte inkludera config som använder sessions för då fungerar tydligen inte cachningen av bilder.
 * Måste inkludera klass-filen själv.
 */
// inkludera config-filen som också skapar $pbbase med sina default-värden
//include(__DIR__.'/config.php');
require_once ('../src/CImage/CImage.php');

// Define some constant values, append slash
// Use DIRECTORY_SEPARATOR to make it work on both windows and unix.
define('IMG_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR);
define('CACHE_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR);

// Skapa en instans av CImage
$imageobject = new CImage();

$imageobject->fixItAll($_GET);

