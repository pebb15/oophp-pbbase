<?php
/**
 * CMovieSearch, utökad CDatabase, en klass för att fixa sökning bland filmer
 *
 */
class CMovieSearch extends CDatabase {

  /**
   * Properties
   *
   */


  /**
   * Constructor
   *
   */
  public function __construct($options) {
	  parent::__construct($options);
  }



  /**
   * Bygg HTML för sökformuläret
   *
   * @param string htmlkod
   */
  public function GetSearchForm($acronym, $title, $year1, $year2, $genre, $hits) {
		if ($acronym) { $createlink = "<br><fieldset><legend>Lägg till ny film</legend><p><a href='movies_create.php'>Skapa ny film</a></p></fieldset>"; }
		else { $createlink = null; }

		if (!$title && !$year1 && !$year2 && !$genre) $allaLink = "<a href='?hits={$hits}&page=1'><strong>visa alla</strong></a>";
		else $allaLink = "<a href='?hits={$hits}&page=1'>visa alla</a>";
		$htmlkod = <<<EOD
		<form>
			<input type='hidden' name='hits' value='{$hits}'/>
    		<input type='hidden' name='page' value='1'/>
			<fieldset><legend>Sök</legend>
			<p><label>Titel (använd *): </label><input size='5' type='search' name='title' value='{$title}' class='fet'> 
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<label>Skapad mellan åren: 
    		<input type='text' name='year1' value='{$year1}' class='fet' size='5'>
			- 
			<input type='text' name='year2' value='{$year2}' class='fet' size='5'>
  			</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<button type='submit'>Sök</button></p>
			<p><strong>eller</strong></p>
			<p>Välj: $allaLink {$this->GetGenreLista($genre, $hits)}</p>
			</fieldset>
			$createlink		
		</form>
EOD;
		return $htmlkod;
  }

  /**
   * Bygg genrelista
   *
   * @param string htmlkod
   */
 public function GetGenreLista($genre, $hits) {
	  	//Bygg genre-lista
		$sql2 = <<<EOD
		SELECT DISTINCT G.name
		FROM Genre AS G
		  INNER JOIN Movie2Genre AS M2G
			ON G.id = M2G.idGenre;
EOD;
		$res2 = $this->ExecuteSelectQueryAndFetchAll($sql2,null,false);
		$genrelista = " ";
		foreach($res2 as $key => $value) {
			//$getParams = getQueryString(array('genre' => $value->name));
			//$genrelista .="<a href='".$getParams."'>{$value->name}</a> ";

			if ($genre == $value->name) $genrelista .="<strong><a href='?genre={$value->name}&amp;hits=$hits'>{$value->name}</a></strong> ";
			else $genrelista .="<a href='?genre={$value->name}&amp;hits=$hits'>{$value->name}</a> ";
			
		}
		$genrelista .="";
		
		return $genrelista;
  }

  /**
   * Bygg genrelista för startsida
   *
   * @param string htmlkod
   */
 public function GetGenreListaSpecial($debug=false) {
		//Bygg genre-lista
		$sql2 = <<<EOD
			SELECT DISTINCT G.name, G.id
			FROM Genre AS G
			  INNER JOIN Movie2Genre AS M2G
				ON G.id = M2G.idGenre;
EOD;
		$res2 = $this->ExecuteSelectQueryAndFetchAll($sql2,null,$debug);
		$genrelista = " ";
		foreach($res2 as $key => $value) {
			$sql3 = "SELECT * FROM Movie2Genre WHERE idGenre={$value->id}";
			//$res3 = $this->ExecuteSelectQueryAndFetchAll($sql3,null,$debug);
			$rows = $this->getRows($sql3,null,$debug);
			
			$genrelista .="<strong><a href='movies.php?genre={$value->name}&amp;hits=8'>{$value->name}($rows)</a></strong> ";
		}
		$genrelista .="";
		
		return $genrelista;
  }
    
  /**
   * Skapa query för MySQL och kör
   *
   * @param array av objjekt
   */
  public function GetQueryResult($title, $year1, $year2, $genre, $orderby, $order, $page, $hits, $debug=false) {
		
		$offset = ($page - 1) * $hits;
		// Om ingen * finns i $title, lägg till sist
		if (!(strpos($title, "*"))) $title .= "*";
		//Byt * mot % inför MySQL sökningen
		$title = str_replace("*", "%", $title);
		// Visa alla filmerna
		$sql_start = "SELECT * FROM VMovie";
		$sql_slut = " ORDER BY $orderby $order  LIMIT $hits OFFSET $offset;";
		//SELECT * FROM VMovie LIMIT 8 OFFSET 0
		
		$params = null;

		//Visa filmer enligt sökkriteriet
		if ($title || $year1 || $year2) $sql_where = " WHERE 1";
		else $sql_where = "";
		if($title) {
			$sql_title = " AND title LIKE '$title'";
		} else $sql_title = "";
		if($year1) {
		  $sql_year1 = " AND year >= $year1"; 
		} else $sql_year1 = "";
		if($year2) {
		  $sql_year2 = " AND year <= $year2"; 
		} else $sql_year2 = "";
		
		if($genre) {
		  $sql = "SELECT M.*, G.name AS genre FROM Movie AS M LEFT JOIN Movie2Genre AS M2G ON M.id = M2G.idMovie INNER JOIN Genre AS G ON M2G.idGenre = G.id WHERE G.name = ? ORDER BY M.$orderby $order  LIMIT $hits OFFSET $offset;";
		  $params = array(
			$genre,
		  ); 
		  $sql2 = "SELECT M.*, G.name AS genre FROM Movie AS M LEFT JOIN Movie2Genre AS M2G ON M.id = M2G.idMovie INNER JOIN Genre AS G ON M2G.idGenre = G.id WHERE G.name = ?;";
		  $params = array(
			$genre,
		  );  
		} else {
			$sql = $sql_start.$sql_where.$sql_title.$sql_year1.$sql_year2.$sql_slut;
			$sql2 = $sql_start.$sql_where.$sql_title.$sql_year1.$sql_year2;	
		}

		$res = $this->ExecuteSelectQueryAndFetchAll($sql,$params,$debug);
		$this->rowsInSearch = $this->getRows($sql2,$params,$debug);
	
		return $res;
 
  }
  
  
    /**
   * Skapa kod för de tre senaste filmerna
   *
   * @param array av objjekt
   */
  public function GetCodeFor3LatestMovies($debug=false) {
	$htmlcode = null;
	$sql = "SELECT * FROM VMovie ORDER BY id desc LIMIT 3;";
	$params = null;

	$res = $this->ExecuteSelectQueryAndFetchAll($sql,$params,$debug);
	
	foreach($res as $key => $value) {
		
		$htmlcode .= <<<EOD
		<a href='movie_info.php?id={$value->id}'>
		<div class='moviebox'><img src='img.php?src={$value->image}&amp;save-as=jpg&amp;width=178&amp;height=125&amp;crop-to-fit' alt=''>{$value->title}</div>
		</a>
EOD;
	}
	return $htmlcode;
 
  }

    /**
   * Form for create new movie
   *
   * @param string htmlkod
   */
  public function createNewMovie() {
		
		$htmlcode = <<<EOD
		<form method='post'>
  <fieldset>
  <legend>Skapa ny film</legend>
  <p><label>Titel:<br/><input type='text' name='title'/></label></p>
  <p><button type='submit' name='create'>Fortsätt</button></p>
  </fieldset>
</form>
EOD;

	return $htmlcode;
 
  }

    /**
   * Form for update movie
   *
   * @param string htmlkod
   */
  public function updateMovie($movie, $output, $admin, $id) {
 	//Bygg formulär
	if ($admin) $btn = "<button name='save' type='submit'>Spara uppdateringar</button>";
	else $btn = "";
	$aimage = "img.php?src=".$movie->image."&save-as=jpg&width=100";

	$htmlcode = <<<EOD
	<form method='post' enctype='multipart/form-data'>
	  <fieldset>
	  <p class='red'>{$output}</p>
	  <legend>Uppdatera info om film</legend>
	  <input type='hidden' name='id' value='{$id}'/>
	  
	  <table class='spec' >
	  <tr><td>Titel:</td><td><input type='text' name='title' value='{$movie->title}'/></td></tr>
	  <tr><td>Regissör:</td><td><input type='text' name='director' value='{$movie->director}'/></td></tr>
	  <tr><td>År:</td><td><input type='text' name='year' value='{$movie->year}'/></td></tr>
	  <tr><td>Längd:</td><td><input type='text' name='length' value='{$movie->length}'/></td></tr>
	  <tr><td>Bild:<br>(Inga mellanslag i filnamnet.)</td><td> 
	    <input type='hidden' name='oldimage' value='{$movie->image}'>
	 	<img src={$aimage}><input type='file' name='image' accept='image/*'>
	  </td></tr>
	  <tr><td>Beskrivning:</td><td><textarea name='plot' cols='25' rows='5'>{$movie->plot}</textarea></td></tr>
	  <tr><td>Text:</td><td><input type='text' name='subtext' value='{$movie->subtext}'/></td></tr>
	  <tr><td>Tal:</td><td><input type='text' name='speech' value='{$movie->speech}'/></td></tr>
	  <tr><td>Genrar:</td><td>{$this->selectGenreLista($id)}</td></tr>
	  <tr><td>Poäng:</td><td><input type='text' name='quality' value='{$movie->quality}'/></td></tr>
	  <tr><td>Format:</td><td><input type='text' name='format' value='{$movie->format}'/></td></tr>
	  <tr><td>Länk imdb:</td><td><input type='text' name='link_imdb' value='{$movie->link_imdb}'/> (med http://)</td></tr>
	  <tr><td>Trailer:</td><td><input type='text' name='trailer' value='{$movie->trailer}'/> (EJ med http://)</td></tr>
	  <tr><td>Hyrpris:</td><td><input type='text' name='price' value='{$movie->price}'/> (kr)</td></tr>
	  <tr><td></td>
	  <td>$btn</td>
	  </tr>
	  </table>
	  
	  <p><a href='movies.php'><strong>Till filmdatabasen</strong></a></p>
	
	  </fieldset>
	</form>
EOD;
	return $htmlcode;
  }
  
  /**
   * Form for create new movie
   *
   * @param string htmlkod
   */
  public function saveUpdateMovieInfo() {
		
		$htmlcode = null;
		
	//Om man sparat ändringar, ta hand om dessa
	if (isset($_POST['save'])) {
		// Get parameters 
		$id     = isset($_POST['id'])    ? strip_tags($_POST['id']) : null;
		$title  = isset($_POST['title']) ? strip_tags($_POST['title']) : null;
		$director  = isset($_POST['director']) ? strip_tags($_POST['director']) : null;
		$length  = isset($_POST['length']) ? strip_tags($_POST['length']) : null;
		$year   = isset($_POST['year'])  ? strip_tags($_POST['year'])  : null;
		$plot  = isset($_POST['plot']) ? strip_tags($_POST['plot']) : null;
		//$image  = isset($_POST['image']) ? strip_tags($_POST['image']) : null;
		$subtext  = isset($_POST['subtext']) ? strip_tags($_POST['subtext']) : null;
		$speech  = isset($_POST['speech']) ? strip_tags($_POST['speech']) : null;
		$quality  = isset($_POST['quality']) ? strip_tags($_POST['quality']) : null;
		$format  = isset($_POST['format']) ? strip_tags($_POST['format']) : null;
		$link_imdb  = isset($_POST['link_imdb']) ? strip_tags($_POST['link_imdb']) : null;
		$trailer  = isset($_POST['trailer']) ? strip_tags($_POST['trailer']) : null;
		$price  = isset($_POST['price']) ? strip_tags($_POST['price']) : null;
		$genre  = isset($_POST['selectgenres']) ? $_POST['selectgenres'] : array();
		$save   = isset($_POST['save'])  ? true : false;
		$oldimage  = isset($_POST['oldimage']) ? strip_tags($_POST['oldimage']) : null;
		
		////////////////////
		$target_dir = 'img/movie/';
		$target_file = $target_dir . $_FILES['image']['name'];
		$message = "";
		if ( $_FILES['image']['name'] != null ) {
			/*echo 'name: ' . $_FILES['image']['name'] . '<br>';
			echo 'type: ' . $_FILES['image']['type'] . '<br>';
			echo 'size: ' . $_FILES['image']['size'] . '<br>';
			echo 'error: ' . $_FILES['image']['error'] . '<br>';
			echo 'tmp_name: ' . $_FILES['image']['tmp_name'] . '<br>';*/
			$upload_sucessful = true;
			
			// Check if file already exists
			if (file_exists($target_file)) {
				$message .= 'Tyvärr filen existerar redan.' . '<br>'; //Sorry, file already exists.
				$upload_sucessful = false;
			}
			
			// Check file size
			if ($_FILES['image']['size'] > 500000) {
				$message .= 'Tyvärr filen är för stor. (Max 500kB)' . '<br>'; //Sorry, your file is too large.
				$upload_sucessful = false;
				
			}//Check if there is an image
			/*if(!getimagesize($_FILES['image']['tmp_name'])){
			   $message .= 'OBS! Ingen bildfil.'; //Please ensure you are uploading an image.
			   $upload_sucessful = false;
			}*/
			
			if (!$upload_sucessful) {
				$message .=  'Din valda fil laddades inte upp.' . '<br>'; //Sorry, your file was not uploaded.
			} 
			// if everything is ok, try to upload file
			else {
				if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
					$message .=   'Filen ' . $_FILES['image']['name'] . ' har laddats upp som ' . $target_file . '<br>'; //The file ....  has been uploaded to
				} 
				else {
					$message .=   'Tyvärr, något blev fel vid uppladdningen.'; //Sorry, there was an error uploading your file.
					}
			}
			$image ='movie/'. $_FILES['image']['name'];
		} else {
			$image = $oldimage;
		}
		////////////////////
		
		// Check that incoming parameters are valid
		is_numeric($id) or die('Check: Id must be numeric.');
		is_array($genre) or die('Check: Genre must be array.');
		
		//plocka bort befintliga kopplingar i Movie2Genre för aktuell film
		$sql = 'DELETE FROM Movie2Genre WHERE idMovie = ?';
		$this->ExecuteQuery($sql, array($id));
		
		//lägg dit de nya
		foreach ($genre as $key => $idg) {
			$sql = 'INSERT INTO Movie2Genre (idMovie, idGenre) VALUES(?,?)';
			$this->ExecuteQuery($sql, array($id,$idg));
		}
		
		//uppdatera all övrig info i Movie
		 $sql = '
			UPDATE Movie SET
			  title = ?,
			  director = ?,
			  length = ?,
			  year = ?,
			  plot = ?,
			  image = ?,
			  subtext = ?,
			  speech = ?,
			  quality = ?,
			  format = ?,
			  link_imdb = ?,
			  trailer = ?,
			  price = ?
			WHERE 
			  id = ?
		  ';
		  $params = array($title, $director, $length, $year, $plot, $image, $subtext, $speech, $quality, $format, $link_imdb, $trailer, $price, $id);
		  $this->ExecuteQuery($sql, $params);
		  $htmlcode = $message.'<br>'.'Informationen sparades.';
	}


	return $htmlcode;
 
  }

    /**
   * Form for get movieinfo
   *
   * @param string movie
   */
  public function getMovieInfo($id, $debug) {
		
	$sql = "SELECT * FROM Movie WHERE id = ?";
	$params = array($id);
	$res = $this->ExecuteSelectQueryAndFetchAll($sql,$params,$debug);
	if(isset($res[0])) {
	  $movie = $res[0];
	  return $movie;
	}
	else {
	  die('Misslyckades: Ingen film med det id:et.');
	}
  }

    /**
   * delete movieinfo
   *
   * @param string 
   */
  public function deleteMovieInfo() {
		// Get parameters 
		$id     = isset($_POST['id'])    ? strip_tags($_POST['id']) : null;
		$delete = isset($_POST['delete']) ? true : false;
		// Check that incoming parameters are valid
		is_numeric($id) or die('Check: Id must be numeric.');
		
		 $sql = 'DELETE FROM Movie2Genre WHERE idMovie = ?';
		  $this->ExecuteQuery($sql, array($id));
		  $this->SaveDebug("Det raderades " . $this->RowCount() . " rader från databasen.");
		 
		  $sql = 'DELETE FROM Movie WHERE id = ? LIMIT 1';
		  $this->ExecuteQuery($sql, array($id));
		  $this->SaveDebug("Det raderades " . $this->RowCount() . " rader från databasen.");
		 
		  header('Location: movies.php');
  }
    /**
   * Form for delete movie
   *
   * @param string movie
   */
  public function deleteMovieForm($movie, $id) {
		
	$htmlcode = <<<EOD
	<form method='post'>
	  <fieldset>
	  <legend>Radera film: <span class='red'>{$movie->title}</span></legend>
	  <input type='hidden' name='id' value='{$id}'/>
	  <p><button type='submit' name='delete'>Radera film</button></p>
	  </fieldset>
	</form>
EOD;
	return $htmlcode;
  }
  
    /**
   * get movie info
   *
   * @param string htmlcode
   */
  public function movieInfo($movie, $id) {
		
	$htmlcode = <<<EOD

  <table class='filminfo'>
  <input type='hidden' name='id' value='{$id}'/>
  <tr><td></td><td><h3>{$movie->title}</h3></td></tr>
  <tr><td>Regissör:</td><td>{$movie->director}</td></tr>
  <tr><td>År:</td><td>{$movie->year}</td></tr>
  <tr><td>Längd:</td><td>{$movie->length}</td></tr>
  <tr><td>Bild:</td><td><img src='img.php?src={$movie->image}&width=800'></td></tr>
  <tr><td>Beskrivning:</td><td>{$movie->plot}</td></tr>
  <tr><td>Text:</td><td>{$movie->subtext}</td></tr>
  <tr><td>Tal:</td><td>{$movie->speech}</td></tr>
  <tr><td>Poäng:</td><td>{$movie->quality}</td></tr>
  <tr><td>Format:</td><td>{$movie->format}</td></tr>
  <tr><td>Länk imdb:</td><td><a href='{$movie->link_imdb}' target='imdb'>Info på imdb</a></td></tr>
  <tr><td>Trailer:</td><td><a href='http://{$movie->trailer}' target='film'>Filmtrailer på youtube</a></td></tr>
  <tr><td>Hyrpris:</td><td>{$movie->price} kr</td></tr>
  <tr><td colspan='2'><a href='movies.php'><strong>Till filmdatabasen</strong></a></td></tr>
  </table>
  
EOD;
	return $htmlcode;
  }
  
    /**
   * Bygg select genres
   *
   * @param string htmlkod
   */
 public function selectGenreLista($id, $genres=null) {
	  	//get actual genres
		$sql2 = <<<EOD
		SELECT DISTINCT G.*
		FROM Genre AS G
		  INNER JOIN Movie2Genre AS M2G
			ON G.id = M2G.idGenre WHERE M2G.idMovie={$id};
EOD;
		$res2 = $this->ExecuteSelectQueryAndFetchAll($sql2,null,false);
		$sql3 = "SELECT * FROM Genre";
		$res3 = $this->ExecuteSelectQueryAndFetchAll($sql3,null,false);
		
		$genrelista = '<select name="selectgenres[]" size="5" multiple="multiple" tabindex="1">';
        
        foreach($res3 as $key => $genre) {
			$sel = '';
			foreach($res2 as $key => $value) {
				//$getParams = getQueryString(array('genre' => $value->name));
				//$genrelista .="<a href='".$getParams."'>{$value->name}</a> ";
	
				if ($genre->id == $value->id) $sel = 'selected';
				
				//if ($genre == $value->name) $genrelista .="<strong><a href='?genre={$value->name}&amp;hits=$hits'>{$value->name}</a></strong> ";
				//else $genrelista .="<a href='?genre={$value->name}&amp;hits=$hits'>{$value->name}</a> ";
				
			}
			$genrelista .= '<option value="'.$genre->id.'" '.$sel.'>'.$genre->name.'</option>';
 		}
		$genrelista .="</select>";
		
		return $genrelista;
  }
}