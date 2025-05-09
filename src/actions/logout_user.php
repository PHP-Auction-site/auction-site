<?php
// Handles user logout
// Placeholder for logout logic 
session_start();
// Unset all session variables
$_SESSION = array();
// Destroy the session
session_destroy();
header('Location: /src/pages/login.php?success=Logged+out+successfully');
exit; 