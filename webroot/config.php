<?php
/**
 * Config-fil med inställningar som påverkar installationen.
 *
 */
 
/**
 * Sätt på felrapportering.
 *
 */
error_reporting(-1);			//Rapporterar alla typer av fel
ini_set('display_errors', 1);	//Visa alla fel
ini_set('output_buffering',0);	//Buffra inte utskrifter utan skriv direkt

/**
 * Definiera PBbase sökvägar
 *
 */
define('PBBASE_INSTALL_PATH', __DIR__.'/..');
define('PBBASE_THEME_PATH', PBBASE_INSTALL_PATH.'/theme/render.php');

/**
 * Inkludera "bootstrapping" funktioner.
 *
 */
include(PBBASE_INSTALL_PATH.'/src/bootstrap.php');
 
/**
 * Starta sessions
 *
 */
session_name(preg_replace('/[^a-z\d]/i','',__DIR__));
session_start();
 
/**
 * Skapa pbbase-variabeln som en array
 *
 */
$pbbase = array();
 
/**
 * Inställningar för hela sajten
 *
 */
$pbbase['lang'] = 'sv';
$pbbase['title_append'] = ' | RM Rental Movies';

$pbbase['byline'] = <<<EOD
<footer class='byline clearerfix'>
	<div class='textbyline'></div>
</footer>
EOD;

if(!CUser::IsAuthenticated()) {
  	$loginout_text = "<a href='loginout.php'>Logga in</a>";
}
else $loginout_text = "<a href='user_update.php?id=".$_SESSION['user']->id."'>".CUser::GetText2()."</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='loginout.php'>Logga ut</a>";

$pbbase['header'] = <<<EOD
<div class="loginout right"><div class="textleft">$loginout_text</div></div>
<img class='sitelogo' src='img/pbbase.png' alt='RM logo'>
<span class='sitetitle'>Rental Movies</span>
<span class='siteslogan'>Roliga filmer till Magiska priser</span>
EOD;

$pbbase['footer'] = <<<EOD
<footer class='center'><span class='sitefooter'>&copy;RM Rental Movies</span></footer>
EOD;
//<footer class='center'><span class='sitefooter'>&copy;RM Rental Movies || <a href='http://validator.w3.org/unicorn/check?ucn_uri=referer&amp;ucn_task=conformance'>Unicorn</a></span></footer>

/**
 * Utseenderelaterade inställningar
 *
 */
$pbbase['stylesheets'] = array('css/style.css');
//$pbbase['stylesheet'] = 'css/style.css';
$pbbase['favicon'] = 'favicon.ico';

/**
 * Huvudmenyn
 *
 */

$pbbase['nav'] = array(
  'start'  => array('text'=>'Start',  'url'=>'index.php'),
  'filmer'  => array('text'=>'Filmer',  'url'=>'movies.php'),
  'nyheter'  => array('text'=>'Nyheter',  'url'=>'blog.php'),
  //'loginout'  => array('text'=>'Loginout',  'url'=>'loginout.php'),
  'medlemmar'  => array('text'=>'Medlemmar',  'url'=>'users.php'),
  'om'  => array('text'=>'Om',  'url'=>'about.php'),
  //'source' => array('text'=>'Källkod', 'url'=>'source.php'),
);


/**
 * Inställningar för javascript
 *
 */
$pbbase['modernizr'] = 'js/modernizr.js';

$pbbase['jquery'] = '//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js';
// Vill man inte ha jQuery så sätts den till null
//$pbbase['jquery'] = null; 

// Egna javscriptfiler, byggs på i en array
$pbbase['javascript_include'] = array();
//$pbbase['javascript_include'] = array('js/main.js');



/**
 * Settings for the database.
 *
 */
 

//localhost
/*define('DB_USER', 'something'); // The database username
define('DB_PASSWORD', 'something'); // The database password
define('DB_DSN', 'mysql:host=localhost;dbname=something;'); //Databas

$pbbase['database']['dsn'] = DB_DSN;
$pbbase['database']['username'] = DB_USER;
$pbbase['database']['password'] = DB_PASSWORD;
$pbbase['database']['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'");
*/

//bth-server
define('DB_USER', 'something'); // The database username
define('DB_PASSWORD', 'something'); // The database password
define('DB_DSN', 'mysql:host=something;'); //Databas

$pbbase['database']['dsn'] = DB_DSN;
$pbbase['database']['username'] = DB_USER;
$pbbase['database']['password'] = DB_PASSWORD;
$pbbase['database']['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'");


 
/**
 * Google analytics.
 *
 */
// Unik nyckel(typ 'UA-22093351-1') för webbplatsen, sätt till null för att stänga av
$pbbase['google_analytics'] = null; 