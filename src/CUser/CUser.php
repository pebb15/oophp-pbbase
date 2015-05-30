<?php
/**
 * Database wrapper, provides a database API for the framework but hides details of implementation.
 *
 */
class CUser {
 
  /**
   * Members
   */	
  private $db = null;               	// The PDO object
  private $acronym = null;              // akronym
  private $firstname = null;     			// namn
  private $lastname = null;     			// namn

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
   *  loggar in användaren om användare och lösenord stämmer och att kontot är aktiverat.
   * 
   * @param string $user
   * @param string password
   */
  public function Login($user, $password){
	  if (!self::IsAuthenticated()) {
	  	$debug = false;
	  	$sql = "SELECT id, acronym, firstname, lastname, password, activated, account_type FROM User WHERE acronym = ?";
		$params = array($user);
		$res = $this->db->ExecuteSelectQueryAndFetchAll($sql,$params,$debug);
		
	  	if(isset($res[0])) {
			//check if password is equal to the bcrypted password from the database
			if(password_verify($password, $res[0]->password) && $res[0]->activated) {
				$_SESSION['user'] = $res[0];
				if($res[0]->account_type == 1) {
					$_SESSION['admin'] = true;
				}
				$this->acronym = $_SESSION['user']->acronym;
				$this->firstname = $_SESSION['user']->firstname;
				$this->lastname = $_SESSION['user']->lastname;
				$this->password = $_SESSION['user']->password;			
			}
	  	}
	  }
  }
  
  /**
   *  loggar ut användaren 
   * 
   */
  public function Logout(){
		unset($_SESSION['user']);
		if (isset($_SESSION['admin'])) unset($_SESSION['admin']);
		$acronym = null;
		$name = null;
  }

  /**
   *  kollar om inloggad 
   * 
   */
  public static function IsAuthenticated(){
		return isset($_SESSION['user']) ? true : false;
  }
  
  /**
   *  check if admin 
   * 
   */
  public static function adminIsAuthenticated(){
		return isset($_SESSION['admin']) ? true : false;
  }

  /**
   *  returnerar text 
   * 
   */
  public static function GetText(){
		if (self::IsAuthenticated()) {
			$output = "Du är inloggad som: {$_SESSION['user']->acronym} ({$_SESSION['user']->firstname} {$_SESSION['user']->lastname})";
		}
		else {
		  	$output = "Du är INTE inloggad.";
		}
		return $output;
  }
  
   /**
   *  returnerar text 
   * 
   */
  public static function GetText2(){
		if (self::IsAuthenticated()) {
			$output = "Välkommen {$_SESSION['user']->firstname} {$_SESSION['user']->lastname}!";
		}
		else {
		  	$output = "";
		}
		return $output;
  } 

  /**
   *  hämtar akronymet 
   * 
   */
  public function GetAcronym(){
		return $acronym;
  }
  
  /**
   *  hämtar namnet
   * 
   */
  public function GetName(){
		return $firstname." ".$lastname;
  }

  
  /**
   *  Hämta rätt formulär
   * 
   */
  public function GetRightForm(){
	//Bygg logout-formulär
	if (self::IsAuthenticated()) {
		$loginoutform = $this->GetLogoutForm();
	} else {
		//Bygg login-formulär
		$loginoutform = $this->GetLoginForm();
	}
	return $loginoutform;
  }
  
  /**
   *  Inloggningsformulär
   * 
   */
  public function GetLoginForm(){
	  $info=self::GetText();
		$loginform = <<<EOD
		<form method='post'><fieldset><legend>Login</legend>
		<p><strong>{$info}</strong></p><p><label>Användare :</label><br>
		<input type='text' name='acronym' value=''></p>
		<p><label>Lösenord:</label><br>
			<input type='password' name='password' value=''></p>
		<p><button type='submit' name='login'>Logga in</button></p>
		<p><button class='new-forget' type='submit' name='newUser'>Inget konto? Skapa ett!</button></p>
		<p><button class='new-forget' type='submit' name='forgotPassword'>Glömt lösenord!</button></p>
		</fieldset></form>
EOD;
	return $loginform;
  }

  /**
   *  Utloggningsformulär
   * 
   */
  public function GetLogoutForm(){
	  	$info=self::GetText();
		$logoutform = <<<EOD
		<form method='post'><fieldset><legend>Logout</legend>
		<p><strong>{$info}</strong></p>
		<p><button type='submit' name='logout'>Logga ut</button></p>
		</fieldset></form>
EOD;
	return $logoutform;
  }

  /**
   *  Utloggning
   * 
   */
  public function GetLogout(){
		$this->Logout();
  }
  
  /**
   *  Create useraccount form
   * 
   */
  public function CreateAccountForm($acronym="", $firstname="", $lastname="", $password="", $email=""){
	  $info=self::GetText();
	  $admin = self::adminIsAuthenticated();
	  $btn = "<button name='createaccount' type='submit'>Skicka ansökan</button>";
		$createaccountform = <<<EOD
		<form method='post'><fieldset><legend>Kontoansökan</legend>
		<p><label>Användarnamn :</label><br>
			<input type='text' name='acronym' value='{$acronym}'></p>
		<p><label>Lösenord:</label><br>
			<input type='password' name='password' value='{$password}'><br>
			<ul>
			<li>Minst en liten bokstav a-z</li>
			<li>Minst en stor bokstav A-Z</li>
			<li>Minst en siffra 0-9</li>
			<li>Minst ett specialtecken, annat de ovan</li>
			</ul>
		</p>
		<p><label>Förnamn:</label><br>
			<input type='text' name='firstname' value='{$firstname}'></p>
		<p><label>Efternamn:</label><br>
			<input type='text' name='lastname' value='{$lastname}'></p>
		<p><label>Mejl:</label><br>
			<input type='text' name='email' value='{$email}'></p>
		<p><em>Alla fält obligatoriska.</em></p>	
		<p>$btn</p>
		</fieldset></form>
EOD;
	return $createaccountform;
  }

  /**
   *  Create forgot password form
   * 
   */
  public function ForgotPasswordForm($email=""){
	  $info=self::GetText();
		$forgotpasswordform = <<<EOD
		<form method='post'><fieldset><legend>Glömt lösenord</legend>
		<p>Fyll i din mejladress och skicka så får du en länk i ett mejl.
		 Länken tar dig till en webbsida där du kan fylla i ett nytt lösenord.</p>
		<p><label>Mejl:</label><br>
			<input type='text' name='email' value='{$email}'></p>	
		<p><button type='submit' name='forgotpassword'>Skicka länk</button></p>
		</fieldset></form>
EOD;
	return $forgotpasswordform;
  }

  /**
   *  Create new password form
   * 
   */
  public function NewPasswordForm($allname, $confirmationCode, $id){
	  $info=self::GetText();
		$forgotpasswordform = <<<EOD
		<form method='post' action='registernewpassword.php'><fieldset><legend>Fyll i nytt lösenord</legend>
		<p>Hej $allname!</p>
		<p><label>Nytt lösenord:</label><br>
			<input type='password' name='newpassword' value=''><br>
			<ul>
			<li>Minst en liten bokstav a-z</li>
			<li>Minst en stor bokstav A-Z</li>
			<li>Minst en siffra 0-9</li>
			<li>Minst ett specialtecken, annat de ovan</li>
			</ul>
			</p>
		<p><button type='submit' name='registernewpassword'>Registrera</button></p>
		</fieldset>
		<input type='hidden' name='code' value='$confirmationCode'>
		<input type='hidden' name='id' value='$id'>
		</form>
EOD;
	return $forgotpasswordform;
  }
 
  /**
   * Skapa query för MySQL och kör
   *
   * @param array av objjekt
   */
  public function GetQueryResult($title, $orderby, $order, $page, $hits, $debug=false) {
		
		$offset = ($page - 1) * $hits;
		// Om ingen * finns i $title, lägg till sist
		if (!(strpos($title, "*"))) $title .= "*";
		//Byt * mot % inför MySQL sökningen
		$title = str_replace("*", "%", $title);
		// Visa alla filmerna
		$sql_start = "SELECT * FROM User";
		$sql_slut = " ORDER BY $orderby $order LIMIT $hits OFFSET $offset;";
		//SELECT * FROM VMovie LIMIT 8 OFFSET 0
		
		$params = null;

		//Visa filmer enligt sökkriteriet
		if ($title) $sql_where = " WHERE 1";
		else $sql_where = "";
		if($title) {
			$sql_title = " AND firstname LIKE '$title'";
		} else $sql_title = "";
		

			$sql = $sql_start.$sql_where.$sql_title.$sql_slut;
			$sql2 = $sql_start.$sql_where.$sql_title;	

 //$sql="SELECT * FROM User WHERE 1 AND firstname LIKE '$title'";
 //$sql2="SELECT * FROM User WHERE 1 AND firstname LIKE '$title'";
		$res = $this->db->ExecuteSelectQueryAndFetchAll($sql,$params,$debug);
		$this->db->rowsInSearch = $this->db->getRows($sql2,$params,$debug);
	
		return $res;
 
  }

  /**
   * Bygg HTML för sökformuläret
   *
   * @param string htmlkod
   */
  public function GetSearchForm($acronym, $title, $hits) {
		if ($acronym) { $createlink = "<br><fieldset><legend>Lägg till ny användare</legend><p><a href='newuser.php'>Skapa ny användare</a></p></fieldset>"; }
		else { $createlink = null; }

		if (!$title) $allaLink = "<a href='?hits={$hits}&page=1'><strong>visa alla</strong></a>";
		else $allaLink = "<a href='?hits={$hits}&page=1'>visa alla</a>";
		$htmlkod = <<<EOD
		<form>
			<input type='hidden' name='hits' value='{$hits}'/>
    		<input type='hidden' name='page' value='1'/>
			<fieldset><legend>Filtrera</legend>
			<p><label>Förnamn (använd *): </label><input size='5' type='search' name='title' value='{$title}' class='fet'> 
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		    <button type='submit'>Sök</button></p>
			</fieldset>
			$createlink		
		</form>
EOD;
		return $htmlkod;
  }

  /**
   * ipdate userinfo
   *
   * @param string 
   */
  public function updateUser($debug) {
	// Get parameters 
	$id     = isset($_POST['id'])    ? strip_tags($_POST['id']) : null;
	$firstname  = isset($_POST['firstname']) ? strip_tags($_POST['firstname']) : null;
	$lastname  = isset($_POST['lastname']) ? strip_tags($_POST['lastname']) : null;
	$email  = isset($_POST['email']) ? strip_tags($_POST['email']) : null;
	
	$save   = isset($_POST['save'])  ? true : false;
	
	// Check that incoming parameters are valid
	is_numeric($id) or die('Check: Id must be numeric.');

	 $sql = '
		UPDATE User SET
		  firstname = ?,
		  lastname = ?,
		  email = ?,
		  last_updated=now()
		WHERE 
		  id = ?
	  ';
	  $params = array($firstname, $lastname, $email, $id);
	  $this->db->ExecuteQuery($sql, $params, $debug);
	  $output = 'Informationen sparades.';
	  
	  return $output;
  }
  
  /**
   * updateform
   *
   * @param string htmlcode
   */
  public function updateForm($user, $output, $id) {
  $htmlcode = <<<EOD
	<form method='post'>
	  <fieldset>
	  <p class='red'>{$output}</p>
	  <legend>Uppdatera info om användare</legend>
	  <input type='hidden' name='id' value='{$id}'/>
	
	  <table class='spec'>
	  <tr><td>Användarnamn:</td><td><input type='text'  disabled value='{$user->acronym}'/></td></tr>
	  <tr><td>Förnamn:</td><td><input type='text' name='firstname' value='{$user->firstname}'/></td></tr>
	  <tr><td>Efternamn:</td><td><input type='text' name='lastname' value='{$user->lastname}'/></td></tr>
	  <tr><td>Mejl:</td><td><input type='text' name='email' value='{$user->email}'/></td></tr>
	  <tr><td></td><td><button name='save' type='submit'>Spara uppdateringar</button></td></tr>
	  </table>
	  
	  <p><a href='users.php'><strong>Till alla användare</strong></a></p>
	
	  </fieldset>
	</form>
EOD;
	return $htmlcode;
  }
  
  /**
   * deleteform
   *
   * @param string htmlcode
   */
  public function deleteForm($user, $id) {
  $htmlcode = <<<EOD
	  <form method='post'>
	  <fieldset>
	  <legend>Radera användare: <span class='red'>{$user->firstname} {$user->lastname}</span></legend>
	  <input type='hidden' name='id' value='{$id}'/>
	  <p><button type='submit' name='delete'>Radera användare</button></p>
	  </fieldset>
	  </form>
EOD;
      return $htmlcode;
  }
  
  /**
   * delete user
   *
   * @param string 
   */
  public function deleteUser() {
	// Get parameters 
	$id     = isset($_POST['id'])    ? strip_tags($_POST['id']) : null;
	$delete = isset($_POST['delete']) ? true : false;
	// Check that incoming parameters are valid
	is_numeric($id) or die('Check: Id must be numeric.');

    $sql = 'DELETE FROM User WHERE id = ? LIMIT 1';
    $this->db->ExecuteQuery($sql, array($id));
    $this->db->SaveDebug("Det raderades " . $this->db->RowCount() . " rader från databasen.");
 
    header('Location: users.php');
  }
  
  /**
   * forgot password, send mail to users email
   *
   * @param string 
   */
  public function forgotPassword($debug) {
	  $html = "";
  	//check values, registrate new user and send mail
	if (isset($_POST['email']))
	{  
		if($_POST['email'] == "")
		{
			echo "Fyll i din mejladress!";
		}
		else 
		{
			$email = isset($_POST['email']) ? strip_tags($_POST['email']) : null;
				
			$sql = "SELECT * FROM User WHERE email = ?";
			$params = array($email);
			$res = $this->db->ExecuteSelectQueryAndFetchAll($sql,$params,$debug);
			$rows_count = $this->db->getRows($sql,$params,$debug);;
			$id = $res[0]->id;
			$acronym = $res[0]->acronym;
			$firstname = $res[0]->firstname;
			$lastname = $res[0]->lastname;
			
			if($rows_count != 1)
			{
				$html .= "<h3 class='red'>Mejladressen hittades inte. Har du fyllt i rätt adress?</h3>";
				//$html .= $cuser->CreateAccountForm($acronym, $firstname, $lastname, $password, $email);
			}
			else
			{
				$confirmationCode = "";
				for($i = 0; $i < 20; $i++)
				{
				  $confirmationCode .= rand(0,9);
				}
				$confirmationCodeCrypted = password_hash($confirmationCode, PASSWORD_DEFAULT);				

				$sql = "UPDATE User SET last_updated = now(), code = '$confirmationCodeCrypted' WHERE id = '$id'";
				$params = null;
				$result = $this->db->ExecuteQuery($sql,$params,$debug); 
				if (!$result) {
					$html .= "Något gick snett vid databashanteringen! Testa igen! $result";
					$html .= $cuser->ForgotPasswordForm($email);
				} else {
					$confirmlink = "http://www.student.bth.se/~pebb15/oophp/kmom710/pbbase/webroot/newpassword.php?id=$id&code=$confirmationCode";
					$to = $email;
					$subject = "Glömt lösenord";
					$message = <<<EOD
					Hej $firstname $lastname!
					Du har glömt lösenordet till ditt konto hos Rental Movies.
					Ditt användarnamn: $acronym
					Du kan skapa nytt lösenord via nedanstående länk: 
					$confirmlink
					(Länken är gilig 15 minuter, sen måste en ny ansökan utföras.)
					
					Vänliga hälsningar RM
EOD;

					$headers = "MIME-Version: 1.0";
					$headers .= "Content-type: text/html; charset=utf-8";
					$headers .= "From: Rental Movies";
					$headers .= "Subject: {$subject}";
					$headers .= "X-Mailer: PHP/".phpversion();
					  
					mail($to, $subject, $message,  $headers);
					
					$html .= "<h3 class='red'>Länk har skickats till din mejladress!</h3>";
					$html .= "<p>Du behöver klicka på länken inom 15 minuter annars får du börja om.</p>";
				}
			}
  		}
 	}
	return $html;
  }
  
  /**
   *  loginout text i header
   * 
   */
  public function loginoutText($loginout_text){
	  $htmlcode = <<<EOD
		<div class="loginout right"><div class="textleft">$loginout_text</div></div>
		<img class='sitelogo' src='img/pbbase.png' alt='RM logo'>
		<span class='sitetitle'>Rental Movies</span>
		<span class='siteslogan'>Roliga filmer till Magiska priser</span>
EOD;
	return $htmlcode;
  }

  /**
   * register new password
   *
   * @param string html
   */
  public function registerNewPassword($debug) {
	$html = "";
	$id = isset($_POST['id']) ? strip_tags($_POST['id']) : null;
	$confirmationCode = isset($_POST['code']) ? strip_tags($_POST['code']) : null;
	$pw = isset($_POST['newpassword']) ? strip_tags($_POST['newpassword']) : null;
	
	
	$sql = "UPDATE User SET last_updated = now(), code = '' WHERE id=$id AND last_updated < ADDDATE(NOW(), INTERVAL -15 MINUTE)";
	$params = null;
	$result = $this->db->ExecuteQuery($sql,$params,$debug);   
		
	$sql = "SELECT * FROM User WHERE id = ? ";
	$params = array($id);
	$res = $this->db->ExecuteSelectQueryAndFetchAll($sql,$params,$debug);
	$rows_count =  count($res);
	
	if($rows_count == 1 && $res[0]->activated)
	{		
		//==================================
		//check password ok enligt kriterier, ejklrt
		//==================================
		$pwcheckok = pwcheckbasic($pw);
		if (!$pwcheckok) {
			// password don't follow rules
			$html .= "<h3 class='red'>Ditt lösenord uppfyller inte alla kriterier.</h3>";
			$html .= "<form action='forgotpassword.php'>";
			$html .= "<button class='new-forget' type='submit' name='forgetPassword'>Fyll i din mejladress igen!</button></form>";
		} else {

		if(password_verify($confirmationCode, $res[0]->code))
		{
			$pw = password_hash($pw, PASSWORD_DEFAULT);
			// Visa formulär
			$sql = "UPDATE User SET `password`='$pw', last_updated=now(), code = '' WHERE id=$id";
			$params = null;
			$result = $this->db->ExecuteQuery($sql,$params,$debug);   

			$html .= "<h3 class='red'>Välkommen, ditt nya lösenord är registrerat.</h3>";
			$html .= "<p>Gå till inloggningssidan. <a href='loginout.php'>Logga in</a></p>";
		} else {
			$html .= "<h3 class='red'>Felaktig konfirmationskod!  <br>
			Troligtvis har tidsgränsen på 15 minuter för aktivering överskridits.</h3>";
			$html .= "<form action='forgotpassword.php'>";
			$html .= "<button class='new-forget' type='submit' name='forgetPassword'>Fyll i din mejladress igen!</button></form>";
		}
		}
	}
	else
	{
		$html .= "<h3 class='red'>Din kontoansökan hittades inte! <br>
			Troligtvis har tidsgränsen på 15 minuter för aktivering överskridits.</h3>";
		$html .= "<form action='forgotpassword.php'>";
		$html .= "<button class='new-forget' type='submit' name='forgetPassword'>Fyll i din mejladress igen!</button></form>";
	}
	return $html;
  }

  /**
   * update new password
   *
   * @param string html
   */
  public function updateNewPassword($debug) {
	$html = "";
	$id = isset($_GET['id']) ? strip_tags($_GET['id']) : null;
	$confirmationCode = isset($_GET['code']) ? strip_tags($_GET['code']) : null;
	
	// nollställ om tiden gått ut
	$sql = "UPDATE User SET last_updated = now(), code = '' WHERE id=$id AND last_updated < ADDDATE(NOW(), INTERVAL -15 MINUTE)";
	$params = null;
	$result = $this->db->ExecuteQuery($sql,$params,$debug);   
		
	$sql = "SELECT * FROM User WHERE id = ? ";
	$params = array($id);
	$res = $this->db->ExecuteSelectQueryAndFetchAll($sql,$params,$debug);
	$rows_count =  count($res);
	
	if($rows_count == 1 && $res[0]->activated)
	{				
		if(password_verify($confirmationCode, $res[0]->code))
		{
			// Visa formulär
			$allname = $res[0]->acronym."(".$res[0]->firstname." ".$res[0]->lastname.")";
			$html .= $this->NewPasswordForm($allname, $confirmationCode, $id);
			/*$sql = "UPDATE User SET activated = true, last_updated = now(), code = '' WHERE id = '{$res[0]->id}'";
			$params = null;
			$result = $db->ExecuteQuery($sql,$params,$debug);   

			$html .= "<h3 class='red'>Välkommen, din registrering är klar.</h3>";
			$html .= "<p>Gå till inloggningssidan. <a href='loginout.php'>Logga in</a></p>";*/
		} else {
			$html .= "<h3 class='red'>Felaktig konfirmationskod!  <br>
			Troligtvis har tidsgränsen på 15 minuter för aktivering överskridits.</h3>";
			$html .= "<form action='forgotpassword.php'>";
			$html .= "<button class='new-forget' type='submit' name='forgetPassword'>Fyll i din mejladress igen!</button></form>";
		}	
	}
	else
	{
		$html .= "<h3 class='red'>Din kontoansökan hittades inte! <br>
			Troligtvis har tidsgränsen på 15 minuter för aktivering överskridits.</h3>";
		$html .= "<form action='forgotpassword.php'>";
		$html .= "<button class='new-forget' type='submit' name='forgetPassword'>Fyll i din mejladress igen!</button></form>";
	}
	return $html;
  }
  
    /**
   * new user, send mail or show application form
   *
   * @param string html
   */
  public function newUser($debug) {
	  $html = "";
	  //Rensa User-tabellen på ansökningar som gått ut i tid
	$sql = "DELETE FROM User WHERE activated=false AND last_updated < ADDDATE(NOW(), INTERVAL -15 MINUTE)";
	$params = null;
	$result = $this->db->ExecuteQuery($sql,$params,$debug);   
	  	//check values, registrate new user and send mail
	if (isset($_POST['acronym']) && isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['password']) && isset($_POST['email']))
	{  
		if($_POST['acronym'] == "" || $_POST['firstname'] == "" || $_POST['lastname'] == "" || $_POST['password'] == "" || $_POST['email'] == "")
		{
			echo "Fyll i alla fält!";
		}
		else 
		{
			$acronym = isset($_POST['acronym']) ? strip_tags($_POST['acronym']) : null;
			$firstname = isset($_POST['firstname']) ? strip_tags($_POST['firstname']) : null;
			$lastname = isset($_POST['lastname']) ? strip_tags($_POST['lastname']) : null;
			$password = isset($_POST['password']) ? strip_tags($_POST['password']) : null;
			$email = isset($_POST['email']) ? strip_tags($_POST['email']) : null;
			$sql = "SELECT * FROM User WHERE acronym = ?";
			$params = array($acronym);
			$res = $this->db->ExecuteSelectQueryAndFetchAll($sql,$params,$debug);
			$rows_count = $this->db->getRows($sql,$params,$debug);;

			if($rows_count == 1)
			{
				$html .= "<h3 class='red'>Användarnamnet upptaget.</h3>";
				$html .= $this->CreateAccountForm($acronym, $firstname, $lastname, $password, $email);
			}
			else
			{
				$sql = "SELECT * FROM User WHERE email = ?";
				$params = array($email);
				$res = $this->db->ExecuteSelectQueryAndFetchAll($sql,$params,$debug);
				$rows_count = $this->db->getRows($sql,$params,$debug);;
	
				if($rows_count == 1)
				{
					$html .= "<h3 class='red'>Mejladressen existerar redan. Välj en ny!</h3>";
					$html .= $this->CreateAccountForm($acronym, $firstname, $lastname, $password, $email);
				}
				else
				{
					//==================================
					//check password ok enligt kriterier, ejklrt
					//==================================	
					$pwcheckok = pwcheckbasic($password);
					if (!$pwcheckok) {
						// password don't follow rules
						$html .= "<h3 class='red'>Ditt lösenord uppfyller inte alla kriterier.</h3>";
						$html .= $this->CreateAccountForm($acronym, $firstname, $lastname, $password, $email);

					} else {
					$passwordCrypted = password_hash($password, PASSWORD_DEFAULT);
					$confirmationCode = "";
					for($i = 0; $i < 20; $i++)
					{
					  $confirmationCode .= rand(0,9);
					}
					$confirmationCodeCrypted = password_hash($confirmationCode, PASSWORD_DEFAULT);				
	
					$sql = "INSERT INTO User (id, acronym, firstname, lastname, password, email, code, account_type, activated, last_updated, created) VALUES ('','{$acronym}', '{$firstname}', '{$lastname}', '{$passwordCrypted}', '{$email}', '{$confirmationCodeCrypted}', 2, false, now(), now())";
					$params = null;
					$result = $this->db->ExecuteQuery($sql,$params,$debug);
					if (!$result) {
						$html .= "Något gick snett vid databashanteringen! Testa igen! $result";
						$html .= $this->CreateAccountForm($acronym, $firstname, $lastname, $password, $email);
					} else {
						$confirmlink = "http://www.student.bth.se/~pebb15/oophp/kmom710/pbbase/webroot/confirmuser.php?user=$acronym&code=$confirmationCode";
						$to = $email;
						$subject = "Bekräfta kontoansökan";
						$message = <<<EOD
						Hej $firstname $lastname!
						Du har ansökt om konto hos Rental Movies.
						Ditt användarnamn: $acronym
						Bekräfta ansökan genom att klicka på följande länk: 
						$confirmlink
						(Länken är gilig 15 minuter, sen måste en ny ansökan utföras.)
						
						Vänliga hälsningar RM
EOD;

						$headers = "MIME-Version: 1.0";
						$headers .= "Content-type: text/html; charset=utf-8";
						$headers .= "From: Rental Movies";
						$headers .= "Subject: {$subject}";
						$headers .= "X-Mailer: PHP/".phpversion();
						  
						mail($to, $subject, $message,  $headers);
						
						$html .= "<h3 class='red'>Ansökan klar, bekräftelselänk har skickats till din mejladress!</h3>";
						$html .= "<p>Du behöver klicka på bekräftelselänken inom 15 minuter annars får du ansöka på nytt.</p>";
					}}
				}
			}
  		}
 	}

	return $html;
  }
  
      /**
   * confirm user
   *
   * @param string html
   */
  public function confirmUser($debug) {
	  $html = "";
	  	//$html .= "<h3 class='red'>{$_GET['user']} {$_GET['code']} </h3>";
	
	$sql = "DELETE FROM User WHERE activated=false AND last_updated < ADDDATE(NOW(), INTERVAL -15 MINUTE)";
	$params = null;
	$result = $this->db->ExecuteQuery($sql,$params,$debug);   
	//"DELETE FROM User WHERE activated='false' AND (TIME_TO_SEC(NOW())-TIME_TO_SEC('last_updated'))>900";
	//TIME_TO_SEC(TIMEDIFF( now(),last_updated))

	$acronym = isset($_GET['user']) ? strip_tags($_GET['user']) : null;
	$confirmationCode = isset($_GET['code']) ? strip_tags($_GET['code']) : null;
		
	$sql = "SELECT * FROM User WHERE acronym = ? ";
	$params = array($acronym);
	$res = $this->db->ExecuteSelectQueryAndFetchAll($sql,$params,$debug);
	$rows_count =  count($res);
	
	if($rows_count == 1 && !$res[0]->activated)
	{				
		if(password_verify($confirmationCode, $res[0]->code))
		{
			$sql = "UPDATE User SET activated = true, last_updated = now(), code = '' WHERE id = '{$res[0]->id}'";
			$params = null;
			$result = $this->db->ExecuteQuery($sql,$params,$debug);   

			$html .= "<h3 class='red'>Välkommen, din registrering är klar.</h3>";
			$html .= "<p>Gå till inloggningssidan. <a href='loginout.php'>Logga in</a></p>";
		} else {
			$html .= "<h3 class='red'>Felaktig konfirmationskod! </h3>";
		}	
	}
	else
	{
		$html .= "<h3 class='red'>Din kontoansökan hittades inte! <br>
			Troligtvis har tidsgränsen på 15 minuter för aktivering överskridits.</h3>";
		$html .= "<form action='newuser.php'><button class='new-forget' type='submit' name='newUser'>Skapa ett nytt konto!</button></form>";
	}
	return $html;
  }
}