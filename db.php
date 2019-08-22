<?php
$servername = "localhost";
$username = "root";
$password = "root";
$db = "zmteh_gulu28_top";
global $dbh;
try {
    $dbh = new PDO('mysql:host='.$servername.';dbname='.$db, $username, $password);
    $dbh->exec('set names utf8');
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
