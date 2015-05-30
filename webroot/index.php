<?php
/** 
 * Sidkontroller
 *
 */
// inkludera config-filen som också skapar $pbbase med sina default-värden
include(__DIR__.'/config.php');

// ställ in variabel för debug-läge
$debug=false;
$_SESSION['page'] = 'index.php';

// utseendeinställningar
$pbbase['stylesheets'][] = 'css/index.css';

// eventuellt lokala js-filer
/* 
$pbbase['javascript_include'][] = 'js/main.js';
*/

// spara alla variabler i pbbase
$pbbase['title']="Start";

$pbbase['main'] = <<<EOD
<h1>Välkommen!</h1>

EOD;

//Till sist, skapa sidan
include(PBBASE_THEME_PATH);