<?PHP
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Installation automatis�e : 2nde �tape</title>
<style type="text/css">
body {
font-family:Tahoma, Arial, Serif;
font-size:14px;
}
.note {
font-size:1.1em;
font-style:italic;
}
.ok {
color:green;
font-weight:bold;
}
.echec {
color:red;
font-weight:bold;
}
</style>
</head>
<body>
<p>
<?php
if(isset($_POST['etape']) AND $_POST['etape'] == 1) { // si nous venons du formulaire alors

// on cr�e des constantes dont on se servira plus tard
define('RETOUR', '<br /><br /><input type="button" value="Retour" onclick="history.back()">');
define('OK', '<span class="ok">OK</span><br />');
define('ECHEC', '<span class="echec">ECHEC</span>');

$fichier = '../config.php';
if(file_exists($fichier) AND filesize($fichier ) > 0) { // si le fichier existe et qu'il n'est pas vide alors
exit('Fichier de configuration d�j� existant. Installation interrompue.'. RETOUR);
}	

// on cr�e nos variables, et au passage on retire les �ventuels espaces	
$hote = trim($_POST['hote']);
$port = trim($_POST['port']);
$login = trim($_POST['login']);
$mdp = trim($_POST['mdp']);
$base = trim($_POST['base']);
$key = trim($_POST['key']);

// on v�rifie la connectivit� avec le serveur avant d'aller plus loin
if(!mysql_connect($hote, $login, $mdp)) {
exit('Mauvais param�tres de connexion.'. RETOUR);
}

// on v�rifie la connectivit� avec la base avant d'aller plus loin	
if(!mysql_select_db($base)) {
exit('Mauvais nom de base.'. RETOUR);
}	

// le texte que l'on va mettre dans le config.php
$texte = '<?PHP
$privateKey = "'.$key.'";

$MySQL_HOST = "'.$hote.'";
$MySQL_PORT = '.$port.';
$MySQL_USER = "'.$login.'";
$MySQL_PASS = "'.$mdp.'";
$MySQL_DB = "'.$base.'";



//
// WARNING :
//
// EXPERIMENTAL !
// They are functions, scripts, etc.
//

$MySQL_CONNECT = mysqli_connect($MySQL_HOST, $MySQL_USER, $MySQL_PASS, $MySQL_DB, $MySQL_PORT);

function HashPassword($password) {
	return md5(sha1($privateKey) . sha1($password));
}

function GenUUID() {
	return md5(sha1($privateKey) . sha1(time()) . sha1($privateKey));
}

function GenClientToken() {
	return md5(sha1($privateKey) . sha1(time()));
}

function GenAccessToken() {
	return md5(sha1(time()) . sha1($privateKey));
}
?>';

// on v�rifie s'il est possible d'ouvrir le fichier
if(!$ouvrir = fopen($fichier, 'w')) {
exit('Impossible d\'ouvrir le fichier : <strong>'. $fichier .'</strong>.'. RETOUR);
}

// s'il est possible d'�crire dans le fichier alors on ne se g�ne pas
if(fwrite($ouvrir, $texte) == FALSE) {
exit('Impossible d\'�crire dans le fichier : <strong>'. $fichier .'</strong>.'. RETOUR);
}

// tout s'est bien pass�
echo 'Fichier de configuration : '. OK;
fclose($ouvrir); // on ferme le fichier
	

$requetes = ''; // on cr�e une variable vide car on va s'en servir apr�s
 
$sql = file('./base.sql'); // on charge le fichier SQL qui contient des requ�tes
foreach($sql as $lecture) { // on le lit
	if(substr(trim($lecture), 0, 2) != '--') { // suppression des commentaires et des espaces
	$requetes .= $lecture; // nous avons nos requ�tes dans la variable
	}
}
 
$reqs = split(';', $requetes); // on s�pare les requ�tes
foreach($reqs as $req){	// et on les ex�cute
	if(!mysql_query($req) AND trim($req) != '') { // si la requ�te fonctionne bien et qu'elle n'est pas vide
		exit('ERREUR : '. $req); // message d'erreur
	}
}
echo 'Installation : '. OK;	
echo '<br /><span class="note">Note : si le site est en ligne, veuillez supprimer le r�pertoire <strong>/install</strong> du ftp.</span>';

} // si on passe sur ce fichier sans �tre pass� par la premi�re �tape alors on redirige
else
exit('Vous devez d\'abord �tre pass� par <a href="./index.php">le formulaire</a>.');	
?>
</p>
</body>
</html>