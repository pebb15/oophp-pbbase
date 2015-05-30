<?php
/**
 * CHTMLTable, en klass för att skapa en tabell
 *
 */
class CHTMLTable {

  /**
   * Properties
   *
   */
 


  /**
   * Constructor
   *
   */
  public function __construct() {
  }



  /**
   * Bygg HTML för tabellen filmer
   *
   * @param string htmlkod
   */
  public function GetHTMLTable($acronym, $res, $hits, $page, $orderby, $order, $rows, $db) {
		// Check that incoming is valid
		in_array($orderby, array('id', 'title', 'year','quality','price')) or die('Check: Not valid column.');
		in_array($order, array('asc', 'desc')) or die('Check: Not valid sort order.');
		
		// Max pages in the table: SELECT COUNT(id) AS rows FROM VMovie
		//$sql3 = "SELECT COUNT(id) AS rows FROM VMovie";
		//$res3 = $db->ExecuteSelectQueryAndFetchAll($sql3,null);
		//$max = ceil($res3[0]->rows / $hits);
		$max = ceil($rows / $hits);

		// Startpage, usually 0 or 1, what you feel is convienient
		$min = 1;
		$hitPerPage = getHitsPerPage(array(2, 4, 8), $hits);
		$htmltable ="<p class='halva'>Sökresultat: {$rows} filmer i listan</p><p class='halva right'>{$hitPerPage}</p>";
		$htmltable .= "<table>";
		$htmltable .="<tr><th>ID&nbsp;&nbsp; <br>" . orderby('id') . "</th><th>Bild</th><th>Titel<br>" . orderby('title') . "</th><th>Beskrivning</th><th>År <br>" . orderby('year') . "</th><th>Kategori</th><th>Poäng<br>" . orderby('quality') . "</th><th>Pris<br>" . orderby('price') . "</th>";
		if ($acronym) { $htmltable .= "<th></th><th></th>"; }
		$htmltable .= "</tr>";
		foreach($res as $key => $value) {
			$aimage = "img.php?src=".$value->image."&save-as=jpg&width=100";
			$genreLista = explode(',', $value->genre);
			$genreLista = implode(', ', $genreLista);
			$description = $value->plot;
			$description = (strlen($description) >200) ? substr($description,0,200)."...<br><a href='movie_info.php?id={$value->id}'><span class='smaller'>Läs mer och se trailer</span></a>" : $description;
			$htmltable .="<tr><td>{$value->id}</td><td><a href='movie_info.php?id={$value->id}'><img src='{$aimage}'></a></td><td><a href='movie_info.php?id={$value->id}'>{$value->title}</a></td><td>{$description}</td><td>{$value->year}</td><td>{$genreLista}</td><td>{$value->quality}</td><td>{$value->price}</td>";
		if ($acronym) { $htmltable .= "<td><a href='movies_update.php?id={$value->id}' class='iconlinks'><i class='fa fa-pencil-square-o'></i></td><td><a href='movies_delete.php?id={$value->id}' class='iconlinks'><i class='fa fa-trash-o'></i></td>"; }
		$htmltable .= "</tr>";
		}
		$pageNavigation = getPageNavigation($hits, $page, $max);
		$htmltable .="</table><p class='centrera'>{$pageNavigation}</p>";
		return $htmltable;
  }


  /**
   * Kolla om sortering eller paginering
   *
   * @param string htmlkod
   */
  public function CheckOrderPageHits($bo, $bp, $bh) {
		
		if ( $bo || $bp || $bh ) {
			return true;
		} 
		else null;
  }
  
    /**
   * Om sortering eller paginering
   *
   * @param string htmlkod
   */
  public function GetSqlResultOrderPageHits($orderby, $order, $page, $hits, $db ) {
		

			$sql = "SELECT * FROM VMovie ORDER BY $orderby $order LIMIT $hits OFFSET " . (($page - 1) * $hits);
			$params = null;
			return $db->ExecuteSelectQueryAndFetchAll($sql,$params,null);
  }
  
  /**
   * Bygg HTML för tabellen users
   *
   * @param string htmlkod
   */
  public function GetUserHTMLTable($acronym, $res, $hits, $page, $orderby, $order, $rows, $db) {
		// Check that incoming is valid
		in_array($orderby, array('id', 'firstname', 'account_type','last_updated','created')) or die('Check: Not valid column.');
		in_array($order, array('asc', 'desc')) or die('Check: Not valid sort order.');
		
		// Max pages in the table: SELECT COUNT(id) AS rows FROM VMovie
		//$sql3 = "SELECT COUNT(id) AS rows FROM VMovie";
		//$res3 = $db->ExecuteSelectQueryAndFetchAll($sql3,null);
		//$max = ceil($res3[0]->rows / $hits);
		$max = ceil($rows / $hits);

		// Startpage, usually 0 or 1, what you feel is convienient
		$min = 1;
		$hitPerPage = getHitsPerPage(array(2, 4, 8), $hits);
		$htmltable ="<p class='halva'>Sökresultat: {$rows} användare i listan</p><p class='halva right'>{$hitPerPage}</p>";
		$htmltable .= "<table>";
		$htmltable .="<tr><th>ID&nbsp;&nbsp; <br>" . orderby('id') . "</th><th>Användarnamn</th><th>Förnamn<br>" . orderby('firstname') . "</th><th>Efternamn</th><th>Typ <br>" . orderby('account_type') . "</th><th>Aktiverad</th><th>Senast uppdaterad<br>" . orderby('last_updated') . "</th><th>Skapad<br>" . orderby('created') . "</th>";
		if ($acronym) { $htmltable .= "<th></th><th></th>"; }
		$htmltable .= "</tr>";
		foreach($res as $key => $value) {
			$htmltable .="<tr><td>{$value->id}</td><td>{$value->acronym}</td><td>{$value->firstname}</td><td>{$value->lastname}</td><td>{$value->account_type}</td><td>{$value->activated}</td><td>{$value->last_updated}</td><td>{$value->created}</td>";
			if ($acronym) { $htmltable .= "<td><a href='user_update.php?id={$value->id}' class='iconlinks'><i class='fa fa-pencil-square-o'></i></td><td><a href='user_delete.php?id={$value->id}' class='iconlinks'><i class='fa fa-trash-o'></i></td>"; }
			$htmltable .= "</tr>";
		}
		$pageNavigation = getPageNavigation($hits, $page, $max);
		$htmltable .="</table><p class='centrera'>{$pageNavigation}</p>";
		return $htmltable;
  }

  /**
   * Bygg HTML för tabellen users, open for all
   *
   * @param string htmlkod
   */
  public function GetUserHTMLTableOpen($acronym, $res, $hits, $page, $rows, $db) {
		
		$max = ceil($rows / $hits);

		// Startpage, usually 0 or 1, what you feel is convienient
		$min = 1;
		$hitPerPage = getHitsPerPage(array(2, 4, 8), $hits);
		$htmltable ="<p class='halva'>Sökresultat: {$rows} användare i listan</p><p class='halva right'>{$hitPerPage}</p>";
		$htmltable .= "<table>";
		$htmltable .="<tr><th>Användarnamn</th><th>Förnamn</th><th>Efternamn</th><th>Skapad</th>";
		$htmltable .= "</tr>";
		foreach($res as $key => $value) {
			$htmltable .="<tr><td>{$value->acronym}</td><td>{$value->firstname}</td><td>{$value->lastname}</td><td>{$value->created}</td>";
			$htmltable .= "</tr>";
		}
		$pageNavigation = getPageNavigation($hits, $page, $max);
		$htmltable .="</table><p class='centrera'>{$pageNavigation}</p>";
		return $htmltable;
  }

}
