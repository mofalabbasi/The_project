<?php 
// Database configuration 
$host = 'localhost'; // Hostname 
$dbname = 'project'; // Database name 
$username = 'root'; // Username 
$password = ''; // Password 
try { 
// Creating a new PDO instance 
$db = new PDO("mysql:host=$host;dbname=$dbname",$username); 
// Setting PDO error mode to exception 
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
//echo "Connected to the database successfully."; 
} 
catch (PDOException $e) 
{ 
echo "Connection failed: " . $e->getMessage(); 
} 
?> 
