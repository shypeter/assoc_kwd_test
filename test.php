<?php
ini_set('memory_limit', '1024M');
$filename = "./src/dict/dict.big.txt";
$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
$arr = preg_split("/[ \n]+/", $contents);
$servername = "localhost";
$username = "root";
$password = "1qaz2wsx3edc";
$db = "keyword";
// Create connection
$conn = new mysqli($servername, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
	$conn->query("SET NAMES utf8");
}

$query = "";
for($i=0 ; $i<count($arr) ; $i++) {
	if (($i % 1500) == 0 && $i!=0) {
		$idx1 = $i;
		++$i;
		$idx2 = ++$i;
		$query_head = "INSERT INTO `speech` (`idx`, `keyword`, `speech`, `updated_at`, `created_at`) VALUES ";
		$query .= "(NULL, '".$arr[$idx1]."', '".$arr[$idx2]."', NOW(), NOW());";
		$query = $query_head.$query;
		echo $conn->query($query)."\n";
		$query = "";
	} else {
		$idx1 = $i;
		++$i;
		$idx2 = ++$i;
		if (isset($arr[$idx1]) && isset($arr[$idx2])) {
			$query .= "(NULL, '".$arr[$idx1]."', '".$arr[$idx2]."', NOW(), NOW()),";
		} else {
			$query_head = "INSERT INTO `speech` (`idx`, `keyword`, `speech`, `updated_at`, `created_at`) VALUES ";
			$query = rtrim($query, ",");
			$query = $query_head . $query . ";";
			echo $conn->query($query)."\n";
		}
	}
}
