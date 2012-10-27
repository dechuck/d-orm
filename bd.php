<?php
	$host='localhost';
	$user='root';
	$db='silo';
	$pass='root';

	mysql_connect($host, $user,$pass) or die("erreur de connexion au serveur");
	mysql_select_db($db) or die("erreur de connexion a la base de donnees");

	date_default_timezone_set('America/Montreal');
	mysql_query("SET NAMES UTF8");
	setlocale (LC_TIME, 'fr_FR');
?>