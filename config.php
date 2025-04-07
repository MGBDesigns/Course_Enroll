<?php
ini_set('session.use_only_cookies', 1);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true, 
]);

session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'courseenrollment';

$dbconnect = mysqli_connect($host, $user, $pass, $db);

if (!$dbconnect) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
