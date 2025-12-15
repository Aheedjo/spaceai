<?php
$host = "localhost";       // Your DB host
$dbname = "spaceai";       // Your DB name
$dbuser = "root";          // Your DB username
$dbpass = "";              // Your DB password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
