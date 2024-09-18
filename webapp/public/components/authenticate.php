<?php

session_start();

if (!isset($_COOKIE['authenticated'])) {
    header('Location: /login.php');
    exit;
}
?>