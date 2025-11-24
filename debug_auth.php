<?php
// debug_auth.php
require_once 'app/Config/Paths.php';
require_once SYSTEMPATH . 'Boot.php';

$session = \Config\Services::session();
echo "<h3>Session Data:</h3>";
print_r($session->get());
echo "<hr>";

echo "<h3>POST Data:</h3>";
print_r($_POST);
echo "<hr>";

echo "<h3>Database Users:</h3>";
$db = \Config\Database::connect();
$users = $db->table('users')->get()->getResult();
print_r($users);
?>