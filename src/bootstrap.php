<?php
/**
 * Nödvändiga funktioner för att PBbase ska fungera med några vanliga delar.
 *
 */
 
/**
 * "Default" felhanterare.
 *
 */
/*function myExceptionHandler($exeption) {
	echo "PBbase: Ofångat 'exception': <p>". $exception->getMessage()."</p><pre>".$exception->getTraceAsString()."</pre>";
}
set_exception_handler('myExceptionHandler');
*/
/**
 * "Autoloader" för klasser.
 *
 */
function myAutoloader($class) {
	$path = PBBASE_INSTALL_PATH."/src/{$class}/{$class}.php";
	if (is_file($path)) {
		include($path);
	} else {
		throw new Exception("Classfile '{$class}' existerar inte.");
	}
}
spl_autoload_register('myAutoloader');

/**
 * Dump-funktion för att skriva ut en array snyggt.
 *
 */
 function dump($array) {
  echo "<pre>" . htmlentities(print_r($array, 1)) . "</pre>";
}

/**
 * Function to create links for sorting
 *
 * @param string $column the name of the database column to sort by
 * @return string with links to order by column.
 */
function orderby($column) {
  return "<span class='orderby'><a href='" . getQueryString(array('orderby' => $column,'order'=>'asc','page'=>1,)) . "'> &darr; </i></a><a href='" . getQueryString(array('orderby' => $column,'order'=>'desc','page'=>1,)) . "'> &uarr; </a></span>";
  //return "<span class='orderby'><a href='?orderby={$column}&order=asc&hits={$hits}&page=1'> &darr; </i></a><a href='?orderby={$column}&order=desc&hits={$hits}&page=1'> &uarr; </a></span>";
}

/**
 * Use the current querystring as base, modify it according to $options and return the modified query string.
 *
 * @param array $options to set/change.
 * @param string $prepend this to the resulting query string
 * @return string with an updated query string.
 */
function getQueryString($options, $prepend='?') {
  // parse query string into array
  $query = array();
  parse_str($_SERVER['QUERY_STRING'], $query);
 
  // Modify the existing query string with new options
  $query = array_merge($query, $options);
 
  // Return the modified querystring
  return $prepend . http_build_query($query);
}

/**
 * Create links for hits per page.
 *
 * @param array $hits a list of hits-options to display.
 * @return string as a link to this page.
 */
function getHitsPerPage($hits, $actual) {
  $nav = "Träffar per sida: ";
  foreach($hits AS $val) {
     $del1 = $del2 = "";
     if ($val == $actual) {
      $del1 = "<strong>";
      $del2 = "</strong>";
    }
    $nav .= "$del1<a href='" . getQueryString(array('hits' => $val)) . "'>$val</a>$del2 ";
  }  
  return $nav;
}

/**
 * Create navigation among pages.
 *
 * @param integer $hits per page.
 * @param integer $page current page.
 * @param integer $max number of pages. 
 * @param integer $min is the first page number, usually 0 or 1. 
 * @return string as a link to this page.
 */
function getPageNavigation($hits, $page, $max, $min=1) {
  $nav  = "<a href='" . getQueryString(array('page' => $min)) . "'>&lt;&lt;</a> ";
  $nav .= "<a href='" . getQueryString(array('page' => ($page > $min ? $page - 1 : $min) )) . "'>&lt;</a> ";
 
  for($i=$min; $i<=$max; $i++) {
    if ($page == $i) $x="<strong>$i</strong>";
    else $x=$i;
    $nav .= "<a href='" . getQueryString(array('page' => $i)) . "'>$x</a> ";
  }
 
  $nav .= "<a href='" . getQueryString(array('page' => ($page < $max ? $page + 1 : $max) )) . "'>&gt;</a> ";
  $nav .= "<a href='" . getQueryString(array('page' => $max)) . "'>&gt;&gt;</a> ";
  return $nav;
}

/**
 * Create a link to the content, based on its type.
 *
 * @param object $content to link to.
 * @return string with url to display content.
 */
function getUrlToContent($content) {
	$url  = htmlentities($content->url, null, 'UTF-8');
	$slug  = htmlentities($content->slug, null, 'UTF-8');
	switch($content->TYPE) {
		case 'page': return "page.php?url={$url}"; break;
		case 'post': return "blog.php?slug={$slug}"; break;
		default: return null; break;
  }
}

/**
 * Call each filter.
 *
 * @param string $text the text to filter.
 * @param string $filter as comma separated list of filter.
 * @return string the formatted text.
 */
function doFilter($text, $filter) {
  // Define all valid filters with their callback function.
  $valid = array(
    'bbcode'   => 'bbcode2html',
    'link'     => 'make_clickable',
    'markdown' => 'markdown',
    'nl2br'    => 'nl2br',  
  );
 
  // Make an array of the comma separated string $filter
  $filters = preg_replace('/\s/', '', explode(',', $filter));
 
  // For each filter, call its function with the $text as parameter.
  foreach($filters as $func) {
    if(isset($valid[$func])) {
      $text = $valid[$func]($text);
    } 
    else {
      throw new Exception("The filter '$filter' is not a valid filter string.");
    }
  }
 
  return $text;
}

/**
 * Helper, BBCode formatting converting to HTML.
 *
 * @param string text The text to be converted.
 * @return string the formatted text.
 * @link http://dbwebb.se/coachen/reguljara-uttryck-i-php-ger-bbcode-formattering
 */
function bbcode2html($text) {
  $search = array( 
    '/\[b\](.*?)\[\/b\]/is', 
    '/\[i\](.*?)\[\/i\]/is', 
    '/\[u\](.*?)\[\/u\]/is', 
    '/\[img\](https?.*?)\[\/img\]/is', 
    '/\[url\](https?.*?)\[\/url\]/is', 
    '/\[url=(https?.*?)\](.*?)\[\/url\]/is' 
    );   
  $replace = array( 
    '<strong>$1</strong>', 
    '<em>$1</em>', 
    '<u>$1</u>', 
    '<img src="$1" />', 
    '<a href="$1">$1</a>', 
    '<a href="$1">$2</a>' 
    );     
  return preg_replace($search, $replace, $text);
}



/**
 * Make clickable links from URLs in text.
 *
 * @param string $text the text that should be formatted.
 * @return string with formatted anchors.
 * @link http://dbwebb.se/coachen/lat-php-funktion-make-clickable-automatiskt-skapa-klickbara-lankar
 */
function make_clickable($text) {
  return preg_replace_callback(
    '#\b(?<![href|src]=[\'"])https?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#',
    create_function(
      '$matches',
      'return "<a href=\'{$matches[0]}\'>{$matches[0]}</a>";'
    ),
    $text
  );
}



//use \Michelf\MarkdownExtra;
/**
 * Format text according to Markdown syntax.
 *
 * @link http://dbwebb.se/coachen/skriv-for-webben-med-markdown-och-formattera-till-html-med-php
 * @param string $text the text that should be formatted.
 * @return string as the formatted html-text.
 */
function markdown($text) {
  require_once(__DIR__ . '/php-markdown/Michelf/Markdown.php');
  require_once(__DIR__ . '/php-markdown/Michelf/MarkdownExtra.php');
  return \Michelf\MarkdownExtra::defaultTransform($text);
}

/**
 * Check taht password basics is correct
 *
 * @link http://dbwebb.se/coachen/skriv-for-webben-med-markdown-och-formattera-till-html-med-php
 * @param string $password that should be checked
 * @return boolean
 */
function pwcheckbasic($password) {
	$check = true;
/*	$m='';
	//check small letter
	$m .= (preg_match( '/[a-z]/', $password)) ? "liten bokstav<br>" : "";
	//check large letter
	$m .= (preg_match( '/[A-Z]/', $password)) ? "stor bokstav<br>" : "";
	//check number
	$m .= (preg_match( '/\d/', $password)) ? "siffra<br>" : "";
	//kolla special character
	$m .= (preg_match( '/[^[:alnum:] ]/', $password)) ? "specialtecken<br>" : "";
	//length
	$m .= (strlen( $password) > 7) ? "längd ok<br>" : "";
	echo $m;
*/
if (preg_match("/^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^[:alnum:] ]).*$/", $password)) {
    $check = true;
} else {
    $check = false;
}
	

    return $check;
}
