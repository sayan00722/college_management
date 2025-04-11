<?php
$plainPassword = 'sayan123';
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

echo "Hashed password for 'sayan123': " . $hashedPassword;
?>
