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

// Récupérer le type de rapport
$type = $_GET['type'] ?? 'ventes';

// Vérification du type valide
$types_valides = ['ventes', 'clients', 'produits'];
if (!in_array($type, $types_valides)) {
    $type = 'ventes';
}

// En-têtes Excel
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"rapport_$type" . date('Y-m-d') . ".xls\"");
header("Pragma: no-cache");
header("Expires: 0");

// BOM UTF-8 pour Excel
echo "\xEF\xBB\xBF";

switch($type) {
    case 'ventes':
        export_ventes($db);
        break;
    case 'clients':
        export_clients($db);
        break;
    case 'produits':
        export_produits($db);
        break;
    default:
        export_ventes($db);
}

function export_ventes($db) {
    try {
        $stmt = $db->prepare("
            SELECT v.id, c.client as client, p.nom as produit, v.quantite, v.prix_total, v.date_vente 
            FROM ventes v 
            LEFT JOIN clients c ON v.client_id = c.id 
            LEFT JOIN produits p ON v.produit_id = p.id 
            ORDER BY v.date_vente DESC
        ");
        $stmt->execute();
        $ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #4285f4; color: white; font-weight: bold; height: 30px;'>";
        echo "<th colspan='6' style='padding: 8px;'>RAPPORT DES VENTES - " . date('d/m/Y') . "</th>";
        echo "</tr>";
        echo "<tr style='background-color: #f2f2f2; font-weight: bold;'>";
        echo "<th style='padding: 6px;'>ID</th>";
        echo "<th style='padding: 6px;'>Client</th>";
        echo "<th style='padding: 6px;'>Produit</th>";
        echo "<th style='padding: 6px;'>Quantité</th>";
        echo "<th style='padding: 6px;'>Prix Total</th>";
        echo "<th style='padding: 6px;'>Date Vente</th>";
        echo "</tr>";
        
        $total = 0;
        if (empty($ventes)) {
            echo "<tr><td colspan='6' style='text-align: center; padding: 10px;'>Aucune vente trouvée</td></tr>";
        } else {
            foreach($ventes as $vente) {
                echo "<tr>";
                echo "<td style='padding: 5px;'>{$vente['id']}</td>";
                echo "<td style='padding: 5px;'>" . htmlspecialchars($vente['client'] ?? 'N/A') . "</td>";
                echo "<td style='padding: 5px;'>" . htmlspecialchars($vente['produit'] ?? 'N/A') . "</td>";
                echo "<td style='padding: 5px; text-align: center;'>{$vente['quantite']}</td>";
                echo "<td style='padding: 5px; text-align: right;'>" . number_format($vente['prix_total'], 2, ',', ' ') . " MAD</td>";
                echo "<td style='padding: 5px;'>" . date('d/m/Y', strtotime($vente['date_vente'])) . "</td>";
                echo "</tr>";
                $total += $vente['prix_total'];
            }
        }
        
        echo "<tr style='background-color: #e8f5e8; font-weight: bold;'>";
        echo "<td colspan='4' style='padding: 8px;'>TOTAL GÉNÉRAL</td>";
        echo "<td colspan='2' style='padding: 8px; text-align: right;'>" . number_format($total, 2, ',', ' ') . " MAD</td>";
        echo "</tr>";
        echo "</table>";
        
    } catch(PDOException $e) {
        echo "<p style='color: red;'>Erreur lors de l'export des ventes : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

function export_clients($db) {
    try {
        $stmt = $db->prepare("SELECT * FROM clients ORDER BY client");
        $stmt->execute();
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #4285f4; color: white; font-weight: bold; height: 30px;'>";
        echo "<th colspan='9' style='padding: 8px;'>RAPPORT DES CLIENTS - " . date('d/m/Y') . "</th>";
        echo "</tr>";
        echo "<tr style='background-color: #f2f2f2; font-weight: bold;'>";
        echo "<th style='padding: 6px;'>ID</th>";
        echo "<th style='padding: 6px;'>Client</th>";
        echo "<th style='padding: 6px;'>Email</th>";
        echo "<th style='padding: 6px;'>Téléphone</th>";
        echo "<th style='padding: 6px;'>Adresse</th>";
        echo "<th style='padding: 6px;'>Ville</th>";
        echo "<th style='padding: 6px;'>Pays</th>";
        echo "<th style='padding: 6px;'>Personne à contacter</th>";
        echo "<th style='padding: 6px;'>Commercial</th>";
        echo "</tr>";
        
        if (empty($clients)) {
            echo "<tr><td colspan='9' style='text-align: center; padding: 10px;'>Aucun client trouvé</td></tr>";
        } else {
            foreach($clients as $client) {
                echo "<tr>";
                echo "<td style='padding: 5px;'>{$client['id']}</td>";
                echo "<td style='padding: 5px;'>" . htmlspecialchars($client['client'] ?? '') . "</td>";
                echo "<td style='padding: 5px;'>" . htmlspecialchars($client['email'] ?? '') . "</td>";
                echo "<td style='padding: 5px;'>" . htmlspecialchars($client['telephone'] ?? '') . "</td>";
                echo "<td style='padding: 5px;'>" . htmlspecialchars($client['adresse'] ?? '') . "</td>";
                echo "<td style='padding: 5px;'>" . htmlspecialchars($client['ville'] ?? '') . "</td>";
                echo "<td style='padding: 5px;'>" . htmlspecialchars($client['pays'] ?? '') . "</td>";
                echo "<td style='padding: 5px;'>" . htmlspecialchars($client['personne_a_contacter'] ?? '') . "</td>";
                echo "<td style='padding: 5px;'>" . htmlspecialchars($client['commercial'] ?? '') . "</td>";
                echo "</tr>";
            }
        }
        
        // Statistiques
        echo "<tr style='background-color: #e8f5e8; font-weight: bold;'>";
        echo "<td colspan='9' style='padding: 8px; text-align: center;'>TOTAL CLIENTS : " . count($clients) . "</td>";
        echo "</tr>";
        echo "</table>";
        
    } catch(PDOException $e) {
        echo "<p style='color: red;'>Erreur lors de l'export des clients : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

function export_produits($db) {
    try {
        $stmt = $db->prepare("
            SELECT p.*, COALESCE(SUM(v.quantite), 0) as total_vendu 
            FROM produits p 
            LEFT JOIN ventes v ON p.id = v.produit_id 
            GROUP BY p.id 
            ORDER BY total_vendu DESC
        ");
        $stmt->execute();
        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #4285f4; color: white; font-weight: bold; height: 30px;'>";
        echo "<th colspan='6' style='padding: 8px;'>RAPPORT DES PRODUITS - " . date('d/m/Y') . "</th>";
        echo "</tr>";
        echo "<tr style='background-color: #f2f2f2; font-weight: bold;'>";
        echo "<th style='padding: 6px;'>ID</th>";
        echo "<th style='padding: 6px;'>Nom</th>";
        echo "<th style='padding: 6px;'>Prix</th>";
        echo "<th style='padding: 6px;'>Stock</th>";
        echo "<th style='padding: 6px;'>Total Vendu</th>";
        echo "<th style='padding: 6px;'>Description</th>";
        echo "</tr>";
        
        if (empty($produits)) {
            echo "<tr><td colspan='6' style='text-align: center; padding: 10px;'>Aucun produit trouvé</td></tr>";
        } else {
            foreach($produits as $produit) {
                echo "<tr>";
                echo "<td style='padding: 5px;'>{$produit['id']}</td>";
                echo "<td style='padding: 5px;'>" . htmlspecialchars($produit['nom']) . "</td>";
                echo "<td style='padding: 5px; text-align: right;'>" . number_format($produit['prix'], 2, ',', ' ') . " MAD</td>";
                echo "<td style='padding: 5px; text-align: center;'>{$produit['stock']}</td>";
                echo "<td style='padding: 5px; text-align: center;'>{$produit['total_vendu']}</td>";
                echo "<td style='padding: 5px;'>" . htmlspecialchars($produit['description'] ?? '') . "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
        
    } catch(PDOException $e) {
        echo "<p style='color: red;'>Erreur lors de l'export des produits : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>