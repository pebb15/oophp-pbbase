<?php
/**
 * Sidkontroller
 *
 */
// inkludera config-filen som också skapar $pbbase med sina default-värden
include(__DIR__.'/config.php');

// sätt alla variabler i pbbase
$pbbase['title']="404";
$pbbase['header'] = "";
$pbbase['main'] = "Det här är PBbase. Sidan finns inte.";
$pbbase['footer'] = "";

//Till sist, skapa sidan
include(PBBASE_THEME_PATH);
