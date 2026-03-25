<?php

$password = 'Navi_thiz14';

// Generate hashed password
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Hashed password: " . $hash;
?>