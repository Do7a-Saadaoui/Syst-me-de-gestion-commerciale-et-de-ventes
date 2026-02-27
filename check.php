<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION['USER_ID'])){ 
    header("location:login.php"); 
    exit; 
}

// Vérifier les permissions admin si nécessaire
if(isset($adminPage) && $adminPage === true) {
    if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("location:unauthorized.php");
        exit;
    }
}
?>