<?php
/**
 * Utseende relaterade funktioner.
 *
 */
 
/**
 * Hämta titeln på sidan genom att konkatenera sidtitel med en extra text.
 *
 * @param string $title, för den här sidan
 * @return string/null, beroende på om det finns extra text eller inte
 */
function get_title($title) {
	global $pbbase;
	return $title.(isset($pbbase['title_append'])?$pbbase['title_append']:null);
}

/**
 * Skapa huvudmenyn som är gemensam på sidorna.
 * Länkarna finns i en array som skickas som argument.
 * Markerar även aktuell sida som visas.
 *
 */
function GenerateMenu($items) {
	$html = "<nav>\n";
	foreach($items as $item) {
	  $selected = ($item['url'] == basename($_SERVER['SCRIPT_FILENAME'])) ? 'selected' : null;  
	  $html .= "<a href='{$item['url']}' class='{$selected}'>{$item['text']}</a>\n";
	}
	$html .= '<form method="get" class="searchmovie" action="movies.php">
	<input type="text" name="title" value="Sök film">
	<button type="submit">Sök</button>
	</form>';
	$html .= "</nav>";
	$html .= "\n";
	return $html;
}
