<?php 
session_start();

// 🔹 Connexion base de données (PDO)
try {
    $pdo = new PDO("mysql:host=localhost;dbname=gestion_commerciale;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// 🔹 Vérification session
if(!isset($_SESSION['USER_ID'])){ 
    header("location:login.php"); 
    exit; 
} 

// 🔹 Si pas d'ID, rediriger vers la sélection
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: paiement_select_facture.php");
    exit;
}

$facture_id = intval($_GET['id']);

// 🔹 Récupération de la facture
$stmt = $pdo->prepare("
    SELECT f.*, c.client AS client_nom,
           (SELECT IFNULL(SUM(p.montant),0) FROM paiements p WHERE p.facture_id=f.id) AS total_paye
    FROM factures f 
    JOIN clients c ON f.client_id=c.id 
    WHERE f.id = ?
");
$stmt->execute([$facture_id]);
$facture = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$facture) {
    die("
        <div class='container mt-5'>
            <div class='alert alert-danger'>
                <h4><i class='bi bi-exclamation-triangle'></i> Facture introuvable</h4>
                <p>La facture #$facture_id n'existe pas.</p>
                <a href='paiement_select_facture.php' class='btn btn-primary'>Choisir une facture</a>
                <a href='facture_list.php' class='btn btn-secondary'>Retour aux factures</a>
            </div>
        </div>
    ");
}



// 🔹 Traitement du formulaire de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant = floatval($_POST['montant']);
    $mode = $_POST['mode_paiement'] ?? 'especes';
    $date_paiement = $_POST['date_paiement'] ?? date('Y-m-d');
    $note = $_POST['note'] ?? null;

    // Validation du montant
    $reste_a_payer = $facture['total'] - $facture['total_paye'];
    $error = null;
    
    if ($montant <= 0) {
        $error = "Le montant doit être supérieur à 0";
    } elseif ($montant > $reste_a_payer) {
        $error = "Le montant ne peut pas dépasser le reste à payer (" . number_format($reste_a_payer, 2, ',', ' ') . " MAD)";
    } else {
        $pdo->beginTransaction();
        try {
            $ins = $pdo->prepare("INSERT INTO paiements (facture_id, montant, date_paiement, mode_paiement, note) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$facture_id, $montant, $date_paiement, $mode, $note]);

            // Mise à jour du statut de la facture
            $stmtSum = $pdo->prepare("SELECT IFNULL(SUM(montant),0) AS total_paye FROM paiements WHERE facture_id = ?");
            $stmtSum->execute([$facture_id]);
            $sum = $stmtSum->fetchColumn();
            
            $newStatut = 'impayee';
            if ($sum >= $facture['total']) {
                $newStatut = 'payee';
            } elseif ($sum > 0 && $sum < $facture['total']) {
                $newStatut = 'partiellement_payee';
            }
            
            $upd = $pdo->prepare("UPDATE factures SET statut = ? WHERE id = ?");
            $upd->execute([$newStatut, $facture_id]);

            $pdo->commit();
            header('Location: facture_view.php?id=' . $facture_id . '&success=Paiement+enregistré+avec+succès');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur lors de l'enregistrement du paiement : " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enregistrer Paiement - Gestion Commerciale</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .container {
        max-width: 800px;
        margin-top: 2rem;
    }
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .facture-info {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
        border-radius: 8px;
        padding: 0.75rem 2rem;
        font-weight: 600;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #5a6fd8, #6a4190);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    .progress {
        height: 8px;
        margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Enregistrer un Paiement</h2>
            <a href="facture_view.php?id=<?= $facture_id ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Retour à la facture
            </a>
        </div>

        <?php if(isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Informations de la facture -->
        <div class="facture-info">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h5 class="mb-1">Facture #<?= $facture['id'] ?></h5>
                    <small>Date: <?= date('d/m/Y', strtotime($facture['date_facture'])) ?></small>
                </div>
                <div class="col-md-4">
                    <strong>Client:</strong><br>
                    <?= htmlspecialchars($facture['client_nom']) ?>
                </div>
                <div class="col-md-4 text-end">
                    <h4 class="mb-1"><?= number_format($facture['total'], 2, ',', ' ') ?> MAD</h4>
                    <?php 
                    $reste_a_payer = $facture['total'] - $facture['total_paye'];
                    $pourcentage_paye = $facture['total'] > 0 ? ($facture['total_paye'] / $facture['total']) * 100 : 0;
                    ?>
                    <small class="badge bg-light text-dark">
                        <?= number_format($pourcentage_paye, 0) ?>% payé
                    </small>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?= $pourcentage_paye ?>%"
                             aria-valuenow="<?= $pourcentage_paye ?>" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <small>Payé: <?= number_format($facture['total_paye'], 2, ',', ' ') ?> MAD</small>
                        <small>Reste: <?= number_format($reste_a_payer, 2, ',', ' ') ?> MAD</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire de paiement -->
        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Montant payé (MAD) *</label>
                    <input type="number" step="0.01" name="montant" class="form-control" 
                           placeholder="Saisir le montant..." required
                           max="<?= $reste_a_payer ?>"
                           value="<?= $reste_a_payer ?>">
                    <small class="form-text text-muted">
                        Maximum: <span class="fw-bold text-success"><?= number_format($reste_a_payer, 2, ',', ' ') ?> MAD</span>
                    </small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Date paiement *</label>
                    <input type="date" name="date_paiement" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Mode de paiement *</label>
                    <select name="mode_paiement" class="form-control" required>
                        <option value="especes">Espèces</option>
                        <option value="carte">Carte Bancaire</option>
                        <option value="virement">Virement</option>
                        <option value="cheque">Chèque</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Référence</label>
                    <input type="text" name="reference" class="form-control" placeholder="N° de chèque, virement...">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Commentaire</label>
                <textarea name="note" class="form-control" rows="3" placeholder="Notes supplémentaires..."><?= isset($_POST['note']) ? htmlspecialchars($_POST['note']) : '' ?></textarea>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="facture_view.php?id=<?= $facture_id ?>" class="btn btn-secondary me-md-2">
                    <i class="bi bi-x-circle me-2"></i>Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Enregistrer le Paiement
                </button>
            </div>
        </form>

        <!-- Résumé des paiements existants -->
        <?php 
        $stmt_paiements = $pdo->prepare("SELECT * FROM paiements WHERE facture_id = ? ORDER BY date_paiement DESC");
        $stmt_paiements->execute([$facture_id]);
        $paiements_existants = $stmt_paiements->fetchAll(PDO::FETCH_ASSOC);
        
        if ($paiements_existants): 
        ?>
        <div class="mt-5">
            <h5 class="mb-3"><i class="bi bi-clock-history me-2"></i>Historique des Paiements</h5>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Montant</th>
                            <th>Mode</th>
                            <th>Référence</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($paiements_existants as $paiement): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($paiement['date_paiement'])) ?></td>
                            <td class="text-success fw-bold"><?= number_format($paiement['montant'], 2, ',', ' ') ?> MAD</td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($paiement['mode_paiement']) ?></span></td>
                            <td><small><?= htmlspecialchars($paiement['reference'] ?? '-') ?></small></td>
                            <td><small class="text-muted"><?= htmlspecialchars($paiement['note'] ?? '-') ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
  </div>

  <script src="js/bootstrap.bundle.min.js"></script>
  <script>
    // Validation en temps réel du montant
    const montantInput = document.querySelector('input[name="montant"]');
    const maxMontant = parseFloat(montantInput.getAttribute('max'));
    
    montantInput.addEventListener('input', function() {
        const value = parseFloat(this.value) || 0;
        
        if (value > maxMontant) {
            this.setCustomValidity('Le montant ne peut pas dépasser ' + maxMontant.toFixed(2) + ' MAD');
            this.classList.add('is-invalid');
        } else if (value <= 0) {
            this.setCustomValidity('Le montant doit être supérieur à 0');
            this.classList.add('is-invalid');
        } else {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
    });

    // Pré-remplir avec le reste à payer
    montantInput.value = maxMontant.toFixed(2);
  </script>
</body>
</html>