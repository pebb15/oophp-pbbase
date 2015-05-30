<?php
/**
 * Hanterar utskrift av bloggposter från tabellen Content
 *
 */
class CBlog {
 
  /**
   * Members
   */	

 /**
   * Konstruktor för att skapa ett blog-objekt
   *
   * @param objekt referens till CDatabase-objekt.
   *
   */
  public function __construct() {

  }
  
    
  /**
   *  skapar en bloggpostlista av en post eller allt från tabellen Content som har publicerats
   * 
   */
  public function createBlogListFromContent($res){
		// Checka att indata, id, är ok
		$slug = isset($_GET['slug']) ? strip_tags($_GET['slug']) : null;
		
		$htmlcode = null;
		
		$loggedInAdmin = CUser::adminIsAuthenticated();
		if ($loggedInAdmin && !isset($_GET['slug']) ) { $htmlcode .= "<br><fieldset><legend>Nytt blogginlägg</legend><p>
			<a href='bloggnew.php'>Skapa meddelande</a></p></fieldset><br>"; }
		
		$filter = new CTextFilter();
		if(isset($res[0])) {
			if (!isset($_GET['slug'])) $htmlcode .= "<div class='blogcontent'>";
		  foreach($res as $c) {
			$title  = htmlentities($c->title, null, 'UTF-8');
			$data   = $filter->doFilter(htmlentities($c->DATA, null, 'UTF-8'), $c->FILTER);
			if (!isset($_GET['slug'])) $data = (strlen($data) >100) ? substr($data,0,100)."...<br><a href='blog.php?slug={$c->slug}'><span class='smaller'>Läs mer</span></a>" : $data;
			$category = $c->category;
			$loggedIn = CUser::adminIsAuthenticated();
			$editLink = $loggedIn ? "<p><a href='edit.php?id={$c->id}'>Uppdatera posten</a></p>" : null;
			$deleteLink = $loggedIn ? " <a href='"."bloggdelete.php?id={$c->id}&title={$c->title}"."'>Ta bort</a> " : null;
			$htmlcode .= <<<EOD
			  <h1><a href='blog.php?slug={$c->slug}'>{$title}</a></h1>
			  <div class='right'>(<em class='smaller'>$category</em>)</div>
			  <article>
			  {$data}
			  <div class='smallest'>$c->published</div>
			  <footer class="blogg">
			  {$editLink}
			  {$deleteLink}
			  </footer>
			  </article>
EOD;
		  }
		  if (!isset($_GET['slug'])) {
			  $htmlcode .= <<<EOD
			  	</div><aside class='blogcategories'>
				<p></p><p></p><p></p>
				<h3 class='categorymenu'>Välj nyheter</h3>
				<div class='categorylinks'>
				<a href='blog.php?category=Allmänt'>Allmänt</a><br>
				<a href='blog.php?category=Filmer'>Filmer</a><br>
				<a href='blog.php?category=Medlem'>Medlem</a><br>
				<br>
				</div>
				</aside>
				<div class='clearAll'></div>
EOD;
		  }
		}
		else if($slug) {
		  $htmlcode .= "Det fanns inte en sådan bloggpost.";
		}
		else {
		  $htmlcode .= "Det fanns inga bloggposter.";
		}
		//$htmlcode .= "<p><br><a href='blogg.php'>Visa allt innehåll</a></p>";
		if (isset($_GET['slug'])) $htmlcode .= "<p><br><a href='blog.php'>Nyhetsbloggen</a></p>";
		return $htmlcode;
  }

}