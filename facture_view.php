<?php
session_start();

// 🔹 Connexion base de données (PDO)
if (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
}

if (!isset($pdo) || !$pdo instanceof PDO) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=gestion_commerciale;charset=utf8", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Erreur de connexion : " . htmlspecialchars($e->getMessage()));
    }
}

// 🔹 Vérifier session
if (!isset($_SESSION['USER_ID'])) {
    header("Location: login.php");
    exit;
}

// 🔹 Vérifier l'id de la facture
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de facture invalide");
}
$facture_id = intval($_GET['id']);

// 🔹 Récupérer la facture et le client - CORRIGÉ avec la table clients
$stmt = $pdo->prepare("
    SELECT f.*, c.client AS client_nom
    FROM factures f
    LEFT JOIN clients c ON f.client_id = c.id
    WHERE f.id = ?
");
$stmt->execute([$facture_id]);
$facture = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$facture) {
    die("Facture introuvable");
}

// 🔹 Récupérer les paiements liés
$stmt = $pdo->prepare("SELECT * FROM paiements WHERE facture_id = ? ORDER BY date_paiement DESC");
$stmt->execute([$facture_id]);
$paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Calculer total payé et restant
$total_facture = floatval($facture['total']);
$total_paye = 0;
foreach ($paiements as $p) {
    $total_paye += floatval($p['montant']);
}
$reste = $total_facture - $total_paye;

// 🔹 Ajouter paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant = floatval($_POST['montant']);
    $mode = $_POST['mode_paiement'] ?? 'Espèces';
    $date_paiement = $_POST['date_paiement'] ?? date('Y-m-d');
    $note = $_POST['note'] ?? null;

    $pdo->beginTransaction();
    try {
        $ins = $pdo->prepare("INSERT INTO paiements (facture_id, montant, date_paiement, mode_paiement, note) VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$facture_id, $montant, $date_paiement, $mode, $note]);

        // Mettre à jour le statut de la facture
        $stmtSum = $pdo->prepare("SELECT IFNULL(SUM(montant),0) AS total_paye FROM paiements WHERE facture_id = ?");
        $stmtSum->execute([$facture_id]);
        $sum = $stmtSum->fetchColumn();
        $newStatut = 'Non Payée';
        if ($sum >= $facture['total']) {
            $newStatut = 'Payée';
        } elseif ($sum > 0 && $sum < $facture['total']) {
            $newStatut = 'Partiellement Payée';
        }
        $upd = $pdo->prepare("UPDATE factures SET statut = ? WHERE id = ?");
        $upd->execute([$newStatut, $facture_id]);

        $pdo->commit();
        header('Location: facture_view.php?id=' . $facture_id);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur lors de l'enregistrement du paiement : " . htmlspecialchars($e->getMessage()));
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Facture #<?= $facture_id ?></title>
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family:'Inter',sans-serif; background:#f7f8fa; margin:0; padding:2rem; }
.container { max-width:900px; background:white; padding:2rem; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
h3 { color:#667eea; margin-bottom:1.5rem; text-align:center; }
.table th { background:#667eea; color:#fff; }
.badge-paid { background:#28a745; color:white; padding:0.3rem 0.6rem; border-radius:6px; }
.badge-part { background:#ffc107; color:white; padding:0.3rem 0.6rem; border-radius:6px; }
.badge-unpaid { background:#dc3545; color:white; padding:0.3rem 0.6rem; border-radius:6px; }
.info-card { background:#f8f9fa; border-left:4px solid #667eea; padding:1rem; border-radius:4px; margin-bottom:1rem; }
</style>
</head>
<body>
<div class="container">
    <h3><i class="bi bi-receipt"></i> Facture #<?= $facture_id ?></h3>
    
    <div class="info-card">
        <div class="row">
            <div class="col-md-6">
                <p><strong><i class="bi bi-person"></i> Client :</strong> <?= htmlspecialchars($facture['client_nom'] ?? '—') ?></p>
                <p><strong><i class="bi bi-calendar"></i> Date :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($facture['date_facture'] ?? date('Y-m-d')))) ?></p>
            </div>
            <div class="col-md-6">
                <p><strong><i class="bi bi-currency-euro"></i> Total :</strong> <?= number_format($total_facture,2,',',' ') ?> MAD</p>
                <p><strong><i class="bi bi-cash-coin"></i> Total payé :</strong> <?= number_format($total_paye,2,',',' ') ?> MAD</p>
                <p><strong><i class="bi bi-clock"></i> Reste :</strong> <?= number_format($reste,2,',',' ') ?> MAD</p>
                <p><strong><i class="bi bi-info-circle"></i> Statut :</strong> 
                    <?php if ($reste <= 0): ?>
                        <span class="badge-paid">Payée</span>
                    <?php elseif ($total_paye > 0): ?>
                        <span class="badge-part">Partiellement Payée</span>
                    <?php else: ?>
                        <span class="badge-unpaid">Non Payée</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <?php if (!empty($facture['note'])): ?>
            <p><strong><i class="bi bi-chat-text"></i> Note :</strong> <?= htmlspecialchars($facture['note']) ?></p>
        <?php endif; ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5><i class="bi bi-plus-circle"></i> Ajouter Paiement</h5>
        <a href="facture_pdf.php?id=<?= $facture_id ?>" class="btn btn-success" target="_blank">
            <i class="bi bi-download"></i> Télécharger PDF
        </a>
    </div>

    <form method="POST" class="mb-4">
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Montant payé (MAD)</label>
          <input type="number" step="0.01" name="montant" class="form-control" required 
                 max="<?= $reste ?>" placeholder="Max: <?= number_format($reste,2,',',' ') ?>">
          <small class="text-muted">Reste à payer: <?= number_format($reste,2,',',' ') ?> MAD</small>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Date paiement</label>
          <input type="date" name="date_paiement" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Mode paiement</label>
          <select name="mode_paiement" class="form-control">
            <option>Espèces</option>
            <option>Virement</option>
            <option>Carte Bancaire</option>
            <option>Chèque</option>
            <option>PayPal</option>
            <option>Autre</option>
          </select>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Note (optionnel)</label>
        <textarea name="note" class="form-control" rows="2" placeholder="Référence, détails..."></textarea>
      </div>
      <button class="btn btn-primary"><i class="bi bi-check-circle"></i> Enregistrer Paiement</button>
    </form>

    <hr>
    <h5><i class="bi bi-list-check"></i> Historique des Paiements</h5>
    <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
                <th>ID</th>
                <th>Montant (MAD)</th>
                <th>Date</th>
                <th>Mode</th>
                <th>Note</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($paiements): ?>
                <?php foreach ($paiements as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= number_format($p['montant'],2,',',' ') ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($p['date_paiement'] ?? date('Y-m-d')))) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($p['mode_paiement'] ?? '—') ?></span></td>
                    <td><?= htmlspecialchars($p['note'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted">Aucun paiement enregistré</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="facture_list.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Retour aux Factures</a>
        <a href="paiement_list.php" class="btn btn-outline-primary"><i class="bi bi-cash-coin"></i> Voir tous les Paiements</a>
    </div>
</div>
<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>