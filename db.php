<?php


// Vérification session
if (!isset($_SESSION['USER_ID'])) {
    header("Location: login.php");
    exit();
}

// 🔹 Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=gestion_commerciale;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>