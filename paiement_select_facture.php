<?php
session_start();
include 'check.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=gestion_commerciale;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer les factures impayées ou partiellement payées
$stmt = $pdo->prepare("
    SELECT f.*, c.client AS client_nom,
           (SELECT IFNULL(SUM(p.montant),0) FROM paiements p WHERE p.facture_id=f.id) AS total_paye
    FROM factures f 
    JOIN clients c ON f.client_id=c.id 
    WHERE f.statut IN ('impayee', 'partiellement_payee')
    ORDER BY f.date_facture DESC
");
$stmt->execute();
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélectionner une Facture - Gestion Commerciale</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2><i class="bi bi-cash-coin me-2"></i>Sélectionner une Facture pour Paiement</h2>
        
        <?php if(empty($factures)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Aucune facture en attente de paiement.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach($factures as $facture): 
                    $reste_a_payer = $facture['total'] - $facture['total_paye'];
                ?>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Facture #<?= $facture['id'] ?></h5>
                            <p class="card-text">
                                <strong>Client:</strong> <?= htmlspecialchars($facture['client_nom']) ?><br>
                                <strong>Total:</strong> <?= number_format($facture['total'], 2, ',', ' ') ?> MAD<br>
                                <strong>Déjà payé:</strong> <?= number_format($facture['total_paye'], 2, ',', ' ') ?> MAD<br>
                                <strong>Reste à payer:</strong> <span class="text-success fw-bold"><?= number_format($reste_a_payer, 2, ',', ' ') ?> MAD</span>
                            </p>
                            <a href="paiement_add.php?id=<?= $facture['id'] ?>" class="btn btn-primary">
                                <i class="bi bi-cash-coin me-2"></i>Enregistrer Paiement
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <a href="facture_list.php" class="btn btn-secondary mt-3">
            <i class="bi bi-arrow-left me-2"></i>Retour aux Factures
        </a>
    </div>
</body>
</html>