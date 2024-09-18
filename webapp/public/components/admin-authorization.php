<?php

if (!isset($_COOKIE['isSiteAdministrator']) || $_COOKIE['isSiteAdministrator'] != true) {
    header('Location: /index.php');
    exit;
}

?>