<?php
/**
 * Hanterar det mesta runt tabellen Content för hantering av blogg
 *
 */
class CContent {
 
  /**
   * Members
   */	
  private $db = null;               	// The PDO object

 /**
   * Konstruktor för att skapa ett User-objekt
   *
   * @param objekt referens till CDatabase-objekt.
   *
   */
  public function __construct($db) {
 		$this->db = $db;
  }
  
  /**
   *  hämtar allt från tabellen Content som har publicerats
   * 
   */
  public function getAllFromContent($debug){
		$htmlcode="<ul>";
		$sql = "SELECT *, (published <= NOW()) AS available FROM Content;";
		$params = array();
		$res = $this->db->ExecuteSelectQueryAndFetchAll($sql,$params,$debug);
		return $res;
  }

  /**
   *  skapar lista av allt från tabellen Content som har publicerats
   * 
   */
  public function createListFromAllContent($res){
		$htmlcode="<ul>";
		if(isset($res[0])) {
			foreach ($res as $c) {
				$id   = htmlentities($c->id, null, 'UTF-8');
				$type   = htmlentities($c->TYPE, null, 'UTF-8');
				$title  = htmlentities($c->title, null, 'UTF-8');
		
				$loggedIn = CUser::IsAuthenticated();
				$htmlcode .= "<li>{$type}: ".$title." (";			
				$htmlcode .= $loggedIn ? " <a href='"."edit.php?id={$id}"."'>editera</a>" : " ";
				$htmlcode .= " <a href='".getUrlToContent($c)."'>visa</a>";
				$htmlcode .= $loggedIn ? " <a href='"."bloggdelete.php?id={$id}&title={$title}"."'>ta bort</a> " : " ";
				$htmlcode .= ")</li>";
			}
		}
		$htmlcode .= "</ul>";
		$htmlcode .= "<p><br><a href='blog.php'>Visa alla bloggposter</a></p>";
		$loggedIn = CUser::IsAuthenticated();
		if ($loggedIn) {	 
			$htmlcode .= "<p><a href='bloggnew.php'>Skapa en ny bloggdel</a></p>";
			$htmlcode .= "<p><a href='?resetTable=1'>Återställ tabellen</a></p>";
		}
		return $htmlcode;
  }
  
  /**
   *  hämtar "bogg-post" från tabellen Content som har publicerats
   * 
   */
  public function getBlogsFromContent($debug){
		// Checka att indata, id, är ok
		$slug = isset($_GET['slug']) ? strip_tags($_GET['slug']) : null;
		$slugSql = $slug ? "slug = '".$slug."'" : '1';
		$category = isset($_GET['category']) ? strip_tags($_GET['category']) : null;
		$categorySql = $category ? "category = '".$category."'" : '1';
		
		$sql = "
		SELECT *
		FROM Content
		WHERE
		  type = 'post' AND
		  $slugSql AND
		  $categorySql AND
		  published <= NOW()
		ORDER BY published DESC
		;
		";
		$params = array();
		$res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params, $debug);
		return $res;
  }

  /**
   *  hämtar "page" från tabellen Content som har publicerats
   * 
   */
  public function getPageFromContent($url, $debug){
		$sql = "SELECT * FROM Content WHERE  type = 'page' AND  url = ? AND  published <= NOW();";
		$params = array($url);
		$res = $this->db->ExecuteSelectQueryAndFetchAll($sql,$params,$debug);
		return $res;
  }
  
  /**
   *  uppdatera tabellen Content
   * 
   */
  public function updateContent(){
		$title = strip_tags($_POST['title']);
		$slug = strip_tags($_POST['slug']);
		$url = strip_tags($_POST['url']);
		$data = $_POST['data'];
		$type = $_POST['type'];
		$filter = strip_tags($_POST['filter']);
		$category = strip_tags($_POST['category']);
		$published = $_POST['published'];
		$idedit = $_POST['idedit'];
		
		$url = empty($url) ? null : $url;
		$slug = empty($slug) ? null : $slug;
		$type = empty($type) ? null : $type;
		$filter = empty($filter) ? 'nl2br' : $filter;
		$category = empty($category) ? null : $category;
		
		$sql = '
		  UPDATE Content SET
			title   = ?,
			slug    = ?,
			url     = ?,
			data    = ?,
			type    = ?,
			filter  = ?,
			category  = ?,
			published = ?,
			updated = NOW()
		  WHERE 
			id = ?
		';
		$params = array($title, $slug, $url, $data, $type, $filter, $category, $published, $idedit);
		$res = $this->db->ExecuteQuery($sql, $params);
		
		$output = null;
		if($res) {
			$date = date('Y-m-d H:i:s');        
			$output = "Informationen sparades. Uppdaterat: ".$date;
		}
		else {
		  $output = 'Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
		}
		return $output;
  }    
  
  /**
   *  hämtar en post från tabellen Content
   * 
   */
  public function selectPostFromContent($id, $debug){
		$sql = "SELECT * FROM Content WHERE id=?;";
		$params = array($id);
		$res = $this->db->ExecuteSelectQueryAndFetchAll($sql,$params,$debug);
		return $res;
  }
  
  /**
   *  hämtar en post från tabellen Content
   * 
   */
  public function createEditForm($c, $output){
	$htmlcode=null;
	$id     = $c->id;
	$url    = htmlentities($c->url, null, 'UTF-8');
	$slug   = htmlentities($c->slug, null, 'UTF-8');
	$type   = htmlentities($c->TYPE, null, 'UTF-8');
	$title  = htmlentities($c->title, null, 'UTF-8');
	$data   = htmlentities($c->DATA, null, 'UTF-8');
	$category = htmlentities($c->category, null, 'UTF-8');
	$filter = htmlentities($c->FILTER, null, 'UTF-8');
	$published = htmlentities($c->published, null, 'UTF-8');
	
	$opt1 = $category == "Allmänt" ? "selected" : "";
	$opt2 = $category == "Filmer" ? "selected" : "";
	$opt3 = $category == "Medlem" ? "selected" : "";
	
	$admin = CUSer::adminIsAuthenticated();
	if ($admin) $btn = '<button type="submit" name="spara">Spara</button><button type="submit" name="reset">Återställ</button>';
	else $btn = "";
	
	$htmlcode .= <<<EOD
	<form method="post">
	<fieldset>
	<legend>Uppdatera innehåll</legend>
	<div class="bg_red">{$output}</div>
	<label>Titel</label><br>
	<input type="text" name="title" size="45" value="{$title}"><br><br>
	<label>Slug</label><br>
	<input type="text" name="slug" size="45" value="{$slug}"><br><br>
	<label>Url</label><br>
	<input type="text" name="url" size="45" value="{$url}"><br><br>
	<textarea cols="40" rows="10" name="data">{$data}</textarea><br><br>
	<!--
	<label>Type</label><br>
	<input type="text" name="type" size="45" value="{$type}"><br><br>
	-->
	<input type="hidden" name="type" value="{$type}">
	<label>Filter</label><br>
	<input type="text" name="filter" size="45" value="{$filter}"><br><br>
	<label>Kategori</label><br>
	<select name="category" value="{$category}">
		<option value='Allmänt' $opt1>Allmänt</option>
		<option value='Filmer' $opt2>Filmer</option>
		<option value='Medlem' $opt3>Medlem</option>
	</select>
	<br><br>
	<label>Publiceringsdatum</label><br>
	<input type="text" name="published" size="45" value="{$published}"><br><br>
	<input type="hidden" name="idedit" value="{$id}">
	$btn
	</fieldset>
	</form>
EOD;
	$htmlcode .= "<p><br><a href='".getUrlToContent($c)."'>Visa detta nyhetsmeddelande</a> <a href='blog.php'>Visa alla</a></p>";
	return $htmlcode;
  }     
 
  /**
   *  hämtar en post från tabellen Content
   * 
   */
  public function createContent(){
	$htmlcode=null;
		$title = strip_tags($_POST['title']);
		$slug = strip_tags($_POST['slug']);
		$url = strip_tags($_POST['url']);
		$data = $_POST['data'];
		$type = $_POST['type'];
		$filter = strip_tags($_POST['filter']);
		$category = strip_tags($_POST['category']);
		$published = $_POST['published'];
		
		$url = empty($url) ? null : $url;
		$slug = empty($slug) ? null : $slug;
		$type = empty($type) ? 'post' : $type;
		$filter = empty($filter) ? 'nl2br' : $filter;
		$category = empty($category) ? null : $category;
		$published = empty($published) ? date("y-m-d H:i:s") : $published;
	
	$sql = "INSERT INTO Content (title, slug, url, DATA, TYPE, FILTER, category, published, created) VALUES('$title', '$slug','$url','$data','$type', '$filter','$category','$published', NOW())";
	$params = array();
	$res = $this->db->ExecuteQuery($sql, $params);
	
	$output = null;
	if($res) {
		$date = date('Y-m-d H:i:s');        
	  	$output = "Informationen sparades. Uppdaterat: ".$date;
	}
	else {
	  $output = 'Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
	}
	return $output;

  }     

  /**
   *  skapa en post i tabellen Content
   * 
   */
  public function createForm($output){
	$htmlcode=null;
	$htmlcode .= <<<EOD
	<form method="post">
	<fieldset>
	<legend>Skapa nytt innehåll</legend>
	<div class="bg_red">{$output}</div>
	<label>Titel</label><br>
	<input type="text" name="title" size="45" value=""><br><br>
	<label>Slug</label><br>
	<input type="text" name="slug" size="45" value=""><br><br>
	<label>Url</label><br>
	<input type="text" name="url" size="45" value=""><br><br>
	<textarea cols="40" rows="10" name="data"></textarea><br><br>
	<!--
	<label>Type</label><br>
	<input type="text" name="type" size="45" value=""><br><br>
	-->
	<input type="hidden" name="type" value="post">
	<label>Filter</label><br>
	<input type="text" name="filter" size="45" value=""><br><br>
	<label>Kategori</label><br>
	<select name="category">
		<option value='Allmänt'>Allmänt</option>
		<option value='Filmer'>Filmer</option>
		<option value='Medlem'>Medlem</option>
	</select>
	<br><br>
	<label>Publiceringsdatum</label><br>
	<input type="text" name="published" size="45" value=""><br><br>

	<button type="submit" name="spara">Spara</button><button type="submit" name="reset">Återställ</button>
	</fieldset>
	</form>
EOD;
	$htmlcode .= "<p><br><a href='".getUrlToContent($c)."'>Visa detta nyhetsmeddelande</a> <a href='blog.php'>Visa alla</a></p>";
	return $htmlcode;
  }
  
    /**
   *  skapa en post i tabellen Content
   * 
   */
  public function confirmDeleteForm(){
	$loggedIn = CUser::adminIsAuthenticated();
	$htmlcode=null;
	if ($loggedIn) {
		// Checka att indata, id, är ok
		$id = isset($_GET['id']) ? strip_tags($_GET['id']) : null;
		is_numeric($id) or die('Check: Id must be numeric.');
		$title = isset($_GET['title']) ? strip_tags($_GET['title']) : null;
		
		$htmlcode .= <<<EOD
		<form method="post">
		<fieldset>
		<legend>Ta bort innehåll</legend>
		<label>Titel: {$title}</label><br>
		<input type="hidden" name="id" size="45" value="{$id}">
		<button type="submit" name="tabort">Ta bort</button>
		</fieldset>
		</form>
EOD;
		$htmlcode .= "<p><br><a href='blog.php'>Visa alla</a></p>";
	} else {
	$htmlcode = "<b>Du måste logga in!</b>";
	}
	return $htmlcode;
  }

  /**
   *  hämtar en post från tabellen Content
   * 
   */
   public function deletePostFromContent($debug){
		$loggedIn = CUser::adminIsAuthenticated();
		$htmlcode=null;
		if ($loggedIn) {
			$id = isset($_POST['id']) ? strip_tags($_POST['id']) : null;
			is_numeric($id) or die('Check: Id must be numeric.');
			$sql = "DELETE FROM Content WHERE id=?;";
			$params = array($id);
			$res = $this->db->ExecuteQuery($sql,$params,$debug);
			$output = null;
			if($res) {
				$date = date('Y-m-d H:i:s');        
				$output = "Posten borttagen: ".$date;
			}
			else {
			  $output = 'Något gick fel.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
			}
			$output .= "<p><br><a href='blogg.php'>Visa allt innehåll</a></p>";
		} else {
			$output = "<b>Du måste logga in!</b>";
	    }
		return $output;
  }


   /**
	*  skapar tabellen Content om den inte finns
	* 
	*/

	public function createTableContent() { 
	
		$output = null;
		
/*		$sql="DROP TABLE IF EXISTS Content ;";  
		$res = $this->db->ExecuteQuery($sql); 
		if($res) {
			$date = date('Y-m-d H:i:s');        
			$output .= "Tabell borttagen: ".$date;
		}
		else {
		  $output .= 'Något gick fel.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
		}
*/
		$sql=" 
			CREATE TABLE IF NOT EXISTS Content 
			( 
			id INT AUTO_INCREMENT PRIMARY KEY NOT NULL, 
			slug CHAR(80) UNIQUE, 
			url CHAR(80) UNIQUE, 
						  
			TYPE CHAR(80), 
			title VARCHAR(80), 
			DATA TEXT, 
			FILTER CHAR(80), 
						  
			published DATETIME, 
			created DATETIME, 
			updated DATETIME, 
			deleted DATETIME 
						  
			) ENGINE INNODB CHARACTER SET utf8; 
		"; 
		 
		$res = $this->db->ExecuteQuery($sql); 
		if($res) {
			$date = date('Y-m-d H:i:s');        
			$output .= "Tabell skapad: ".$date;
		}
		else {
		  $output .= 'Något gick fel.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
		}
		
		$sql="TRUNCATE TABLE Content;";  
		$res = $this->db->ExecuteQuery($sql); 
		if($res) {
			$date = date('Y-m-d H:i:s');        
			$output .= "Tabell rensad: ".$date;
		}
		else {
		  $output .= 'Något gick fel.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
		}

		return $output;
	} 

   /**
	*  rensa tabellen Content
	* 
	*/
	public function resetTableContent() { 

		$loggedIn = CUser::IsAuthenticated();
		$htmlcode=null;
		if ($loggedIn) {	 
			$this->createTableContent(); 		 
			$sql=" 
				INSERT INTO Content (slug, url, TYPE, title, DATA, FILTER, published, created) VALUES 
				('hem', 'hem', 'page', 'Hem', 'Detta är min hemsida. Den är skriven i [url=http://en.wikipedia.org/wiki/BBCode]bbcode[/url] vilket innebär att man kan formattera texten till [b]bold[/b] och [i]kursiv stil[/i] samt hantera länkar.\n\nDessutom finns ett filter (nl2br) som lägger in <br>-element istället för \\n, det är smidigt, man kan skriva texten precis som man tänker sig att den skall visas, med radbrytningar.', 'bbcode,nl2br', NOW(), NOW()), 
				('om', 'om', 'page', 'Om', 'Detta är en sida om mig och min webbplats. Den är skriven i [Markdown](http://en.wikipedia.org/wiki/Markdown). Markdown innebär att du får bra kontroll över innehållet i din sida, du kan formattera och sätta rubriker, men du behöver inte bry dig om HTML.\n\nRubrik nivå 2\n-------------\n\nDu skriver enkla styrtecken för att formattera texten som **fetstil** och *kursiv*. Det finns ett speciellt sätt att länka, skapa tabeller och så vidare.\n\n###Rubrik nivå 3\n\nNär man skriver i markdown så blir det läsbart även som textfil och det är lite av tanken med markdown.', 'markdown', NOW(), NOW()), 
				('blogpost-1', NULL, 'post', 'Välkommen till min blogg!', 'Detta är en bloggpost.\n\nNär det finns länkar till andra webbplatser så kommer de länkarna att bli klickbara.\n\nhttp://dbwebb.se är ett exempel på en länk som blir klickbar.', 'clickable,nl2br', NOW(), NOW()), 
				('blogpost-2', NULL, 'post', 'Nu har sommaren kommit', 'Detta är en bloggpost som berättar att sommaren har kommit, ett budskap som kräver en bloggpost.', 'nl2br', NOW(), NOW()), 
				('blogpost-3', NULL, 'post', 'Nu har hösten kommit', 'Detta är en bloggpost som berättar att sommaren har kommit, ett budskap som kräver en bloggpost', 'nl2br', NOW(), NOW()); 
			";  
			$res = $this->db->ExecuteQuery($sql); 
			if($res) {
				$date = date('Y-m-d H:i:s');        
				$htmlcode .= "Innehåll i tabell instoppat: ".$date;
			}
			else {
			  $htmlcode .= 'Något gick fel.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
			}
		} else {
		$htmlcode .= "<b>Du måste logga in!</b>";
		}
		return $htmlcode;

	} 
	
  /**
   *  hämtar de 3 senaste nyheterna som har publicerats och bygger ihop html-kod
   * 
   */
  public function get3LatestPostsFromContent($debug){
		$htmlcode = null;
		$sql = "SELECT * FROM Content WHERE  type = 'post' AND published <= NOW() ORDER BY published desc LIMIT 3;";
		$params = array();
		$res = $this->db->ExecuteSelectQueryAndFetchAll($sql,$params,$debug);

		foreach($res as $key => $value) {
			$description = $value->DATA;
			$description = (strlen($description) >100) ? substr($description,0,100)."..." : $description;
			$htmlcode .= <<<EOD
			<a href='blog.php?slug={$value->slug}'>
				<div class='bloggbox'>
					<h4>{$value->title}</h4>
					<p>$description</p>
					<p class='smallest'>$value->published</p>
				</div>
			</a>
EOD;
		}
		return $htmlcode;
  }

}