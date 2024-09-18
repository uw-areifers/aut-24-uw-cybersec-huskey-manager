<?php

// Expire the authentication cookie
unset($_COOKIE['authenticated']); 
setcookie('authenticated', '', time() - 3600, '/');

// Expire the Administrator cookie
unset($_COOKIE['isSiteAdministrator']); 
setcookie('isSiteAdministrator', '', -1, '/'); 

// Redirect to the login page
header('Location: /login.php');
exit();

?>