<?php
session_start();
if(!isset($_SESSION['USER_ID'])){ 
    header("location:login.php"); 
    exit; 
}

// Connexion base de données
try {
    $db = new PDO("mysql:host=localhost;dbname=gestion_commerciale;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$message = "";
$success = "";

if(isset($_POST['import'])) {
    if(isset($_FILES['file']['name'])) {
        $file_name = $_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_type = $_FILES['file']['type'];
        
        // Vérifier l'extension
        $allowed = array('xls', 'xlsx', 'csv');
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        
        if(in_array($ext, $allowed)) {
            // Lire le fichier Excel
            require_once 'PHPExcel/Classes/PHPExcel.php';
            
            try {
                $objPHPExcel = PHPExcel_IOFactory::load($file_tmp);
                $worksheet = $objPHPExcel->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();
                
                $imported = 0;
                $errors = 0;
                
                // Commencer à la ligne 2 (en supposant que la ligne 1 est l'en-tête)
                for($row = 2; $row <= $highestRow; $row++) {
                    $nom = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
                    $email = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                    $telephone = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                    $adresse = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                    
                    if(!empty($nom) && !empty($email)) {
                        // Vérifier si le client existe déjà
                        $check_stmt = $db->prepare("SELECT id FROM clients WHERE email = :email");
                        $check_stmt->bindParam(":email", $email);
                        $check_stmt->execute();
                        
                        if($check_stmt->rowCount() == 0) {
                            // Insérer le nouveau client
                            $insert_stmt = $db->prepare("INSERT INTO clients (nom, email, telephone, adresse) VALUES (:nom, :email, :telephone, :adresse)");
                            $insert_stmt->bindParam(":nom", $nom);
                            $insert_stmt->bindParam(":email", $email);
                            $insert_stmt->bindParam(":telephone", $telephone);
                            $insert_stmt->bindParam(":adresse", $adresse);
                            
                            if($insert_stmt->execute()) {
                                $imported++;
                            } else {
                                $errors++;
                            }
                        } else {
                            $errors++;
                        }
                    }
                }
                
                $success = "Importation terminée : $imported clients importés, $errors erreurs";
                
            } catch(Exception $e) {
                $message = "Erreur lors de la lecture du fichier : " . $e->getMessage();
            }
        } else {
            $message = "Format de fichier non supporté. Utilisez .xls, .xlsx ou .csv";
        }
    } else {
        $message = "Veuillez sélectionner un fichier";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importation Excel - Gestion Commerciale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f5f5f5; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:white; padding:1rem 2rem; }
        .sidebar { width: 250px; background: white; padding: 1.5rem; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .main-content { flex:1; padding:2rem; background:#f9f9f9; }
        .card { border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="header">
    <div class="d-flex justify-content-between align-items-center">
        <div class="logo">Gestion Commerciale</div>
        <div class="user-info">
            <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Utilisateur'); ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm ms-2">Déconnexion</a>
        </div>
    </div>
</div>

<div class="d-flex">
    <div class="sidebar">
        <h5 class="text-center mb-3 text-primary">Menu</h5>
        <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Tableau de Bord</a>
        <a href="clients.php" class="nav-link"><i class="bi bi-people me-2"></i> Clients</a>
        <a href="produits.php" class="nav-link"><i class="bi bi-box-seam me-2"></i> Produits</a>
        <a href="ventes.php" class="nav-link"><i class="bi bi-cart-check me-2"></i> Ventes</a>
        <a href="rapports.php" class="nav-link active"><i class="bi bi-bar-chart-line me-2"></i> Rapports</a>
        <a href="import_excel.php" class="nav-link"><i class="bi bi-upload me-2"></i> Import Excel</a>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Importation depuis Excel</h1>
            <a href="export_template.php" class="btn btn-outline-primary">
                <i class="bi bi-download"></i> Télécharger le Template
            </a>
        </div>

        <?php if($message): ?>
            <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Importer des Clients</h5>
                        <p class="text-muted">Format supporté : .xls, .xlsx, .csv</p>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Fichier Excel</label>
                                <input type="file" name="file" class="form-control" accept=".xls,.xlsx,.csv" required>
                            </div>
                            
                            <button type="submit" name="import" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Importer
                            </button>
                            <a href="rapports.php" class="btn btn-secondary">Retour</a>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Structure du Fichier</h5>
                        <p>Votre fichier Excel doit avoir cette structure :</p>
                        
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Adresse</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Jean Dupont</td>
                                    <td>jean@example.com</td>
                                    <td>0123456789</td>
                                    <td>Paris</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="alert alert-info">
                            <small>
                                <strong>Note :</strong><br>
                                - La première ligne doit contenir les en-têtes<br>
                                - Les colonnes doivent être dans cet ordre<br>
                                - Les doublons d'email seront ignorés
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>