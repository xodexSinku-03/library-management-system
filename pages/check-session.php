<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    echo 'authorized';
} else {
    echo 'unauthorized';
}
?>