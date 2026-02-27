<?php
session_start();
if(!isset($_SESSION['USER_ID'])){
    header("Location: login.php");
    exit;
}

// جلب العملاء من قاعدة البيانات
$pdo = new PDO("mysql:host=localhost;dbname=gestion_commerciale;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$clients = $pdo->query("SELECT id, nom FROM clients ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<form action="devis.php" method="POST">
    <label>Choisir le client:</label>
    <select name="client_id">
        <?php foreach($clients as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Générer Devis</button>
</form>
