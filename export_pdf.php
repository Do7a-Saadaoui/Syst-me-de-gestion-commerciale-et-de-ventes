<?php
session_start();
if(!isset($_SESSION['USER_ID'])){ 
    header("location:login.php"); 
    exit; 
}

// Inclure TCPDF
require_once('tcpdf/tcpdf.php');

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

switch($type) {
    case 'ventes':
        export_ventes_pdf($db);
        break;
    case 'clients':
        export_clients_pdf($db);
        break;
    case 'produits':
        export_produits_pdf($db);
        break;
    default:
        export_ventes_pdf($db);
}

function export_ventes_pdf($db) {
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
        
        // Créer nouveau PDF
        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Information du document
        $pdf->SetCreator('Gestion Commerciale');
        $pdf->SetAuthor('Système Gestion Commerciale');
        $pdf->SetTitle('Rapport des Ventes');
        $pdf->SetSubject('Export PDF des ventes');
        
        // Marges
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        
        // Ajouter une page
        $pdf->AddPage();
        
        // Titre
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'RAPPORT DES VENTES', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Date d\'export : ' . date('d/m/Y à H:i'), 0, 1, 'C');
        $pdf->Ln(10);
        
        // En-tête du tableau
        $pdf->SetFillColor(66, 133, 244);
        $pdf->SetTextColor(255);
        $pdf->SetFont('helvetica', 'B', 10);
        
        $header = array('ID', 'Client', 'Produit', 'Quantité', 'Prix Total', 'Date Vente');
        $w = array(15, 50, 60, 25, 30, 30);
        
        for($i = 0; $i < count($header); $i++) {
            $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        }
        $pdf->Ln();
        
        // Données
        $pdf->SetFillColor(255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('helvetica', '', 9);
        
        $total = 0;
        $fill = false;
        
        if (empty($ventes)) {
            $pdf->Cell(array_sum($w), 6, 'Aucune vente trouvée', 1, 0, 'C', $fill);
            $pdf->Ln();
        } else {
            foreach($ventes as $vente) {
                $pdf->Cell($w[0], 6, $vente['id'], 1, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, $vente['client'] ?? 'N/A', 1, 0, 'L', $fill);
                $pdf->Cell($w[2], 6, $vente['produit'] ?? 'N/A', 1, 0, 'L', $fill);
                $pdf->Cell($w[3], 6, $vente['quantite'], 1, 0, 'C', $fill);
                $pdf->Cell($w[4], 6, number_format($vente['prix_total'], 2) . ' MAD', 1, 0, 'R', $fill);
                $pdf->Cell($w[5], 6, date('d/m/Y', strtotime($vente['date_vente'])), 1, 0, 'C', $fill);
                $pdf->Ln();
                
                $total += $vente['prix_total'];
                $fill = !$fill;
            }
            
            // Total
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(232, 245, 232);
            $pdf->Cell(array_sum($w) - $w[4] - $w[5], 6, 'TOTAL GENERAL', 1, 0, 'R', true);
            $pdf->Cell($w[4] + $w[5], 6, number_format($total, 2) . ' MAD', 1, 0, 'C', true);
        }
        
        // Output
        $pdf->Output('rapport_ventes_' . date('Y-m-d') . '.pdf', 'D');
        
    } catch(PDOException $e) {
        die("Erreur lors de l'export PDF : " . $e->getMessage());
    } catch(Exception $e) {
        die("Erreur PDF : " . $e->getMessage());
    }
}

function export_clients_pdf($db) {
    try {
        $stmt = $db->prepare("SELECT * FROM clients ORDER BY client");
        $stmt->execute();
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Créer nouveau PDF
        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator('Gestion Commerciale');
        $pdf->SetAuthor('Système Gestion Commerciale');
        $pdf->SetTitle('Rapport des Clients');
        
        $pdf->SetMargins(5, 15, 5); // Marges très réduites
        $pdf->AddPage();
        
        // Titre
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'RAPPORT DES CLIENTS', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Date d\'export : ' . date('d/m/Y à H:i'), 0, 1, 'C');
        $pdf->Ln(10);
        
        // En-tête du tableau - Toutes les colonnes
        $pdf->SetFillColor(66, 133, 244);
        $pdf->SetTextColor(255);
        $pdf->SetFont('helvetica', 'B', 8); // Police plus petite
        
        $header = array('ID', 'Client', 'Email', 'Téléphone', 'Adresse', 'Ville', 'Pays', 'Commercial', 'Contact');
        $w = array(10, 35, 40, 25, 40, 25, 25, 30, 30);
        
        for($i = 0; $i < count($header); $i++) {
            $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        }
        $pdf->Ln();
        
        // Données
        $pdf->SetFillColor(255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('helvetica', '', 7); // Police très petite
        
        $fill = false;
        
        if (empty($clients)) {
            $pdf->Cell(array_sum($w), 6, 'Aucun client trouvé', 1, 0, 'C', $fill);
            $pdf->Ln();
        } else {
            foreach($clients as $client) {
                // Tronquer les textes longs
                $adresse = strlen($client['adresse'] ?? '') > 30 ? 
                    substr($client['adresse'], 0, 27) . '...' : $client['adresse'];
                
                $pdf->Cell($w[0], 6, $client['id'], 1, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, $client['client'] ?? '', 1, 0, 'L', $fill);
                $pdf->Cell($w[2], 6, $client['email'] ?? '', 1, 0, 'L', $fill);
                $pdf->Cell($w[3], 6, $client['telephone'] ?? '', 1, 0, 'C', $fill);
                $pdf->Cell($w[4], 6, $adresse, 1, 0, 'L', $fill);
                $pdf->Cell($w[5], 6, $client['ville'] ?? '', 1, 0, 'L', $fill);
                $pdf->Cell($w[6], 6, $client['pays'] ?? '', 1, 0, 'L', $fill);
                $pdf->Cell($w[7], 6, $client['commercial'] ?? '', 1, 0, 'L', $fill);
                $pdf->Cell($w[8], 6, $client['personne_a_contacter'] ?? '', 1, 0, 'L', $fill);
                $pdf->Ln();
                
                $fill = !$fill;
            }
        }
        
        // Output
        $pdf->Output('rapport_clients_' . date('Y-m-d') . '.pdf', 'D');
        
    } catch(Exception $e) {
        die("Erreur lors de l'export PDF : " . $e->getMessage());
    }
}

function export_produits_pdf($db) {
    try {
        $stmt = $db->prepare("
            SELECT p.*, COALESCE(SUM(v.quantite), 0) as total_vendu,
            COALESCE(SUM(v.prix_total), 0) as chiffre_affaires
            FROM produits p 
            LEFT JOIN ventes v ON p.id = v.produit_id 
            GROUP BY p.id 
            ORDER BY total_vendu DESC
        ");
        $stmt->execute();
        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Créer nouveau PDF en format paysage avec marges réduites
        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator('Gestion Commerciale');
        $pdf->SetAuthor('Système Gestion Commerciale');
        $pdf->SetTitle('Rapport des Produits');
        
        // Marges réduites pour plus d'espace
        $pdf->SetMargins(8, 15, 8);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(8);
        $pdf->AddPage();
        
        // Titre principal
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 12, 'RAPPORT COMPLET DES PRODUITS', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 6, 'Date d\'export : ' . date('d/m/Y à H:i'), 0, 1, 'C');
        $pdf->Ln(8);
        
        // Statistiques rapides
        $totalProduits = count($produits);
        $totalStock = 0;
        $totalVentes = 0;
        $totalCA = 0;
        
        foreach($produits as $produit) {
            $totalStock += $produit['stock'];
            $totalVentes += $produit['total_vendu'];
            $totalCA += $produit['chiffre_affaires'];
        }
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, "Statistiques : {$totalProduits} produits | Stock total : {$totalStock} | Ventes totales : {$totalVentes} | CA : " . number_format($totalCA, 2, ',', ' ') . " MAD", 0, 1, 'C');
        $pdf->Ln(8);
        
        // En-tête du tableau élargi
        $pdf->SetFillColor(59, 89, 152); // Bleu plus foncé
        $pdf->SetTextColor(255);
        $pdf->SetFont('helvetica', 'B', 11);
        
        // Colonnes élargies avec plus d'informations
        $header = array('ID', 'Nom du Produit', 'Prix Unitaire', 'Stock', 'Vendus', 'Chiffre Affaires', 'Description');
        $w = array(12, 45, 25, 18, 18, 28, 100); // Largeurs ajustées pour format paysage
        
        // Centrer le tableau
        $totalWidth = array_sum($w);
        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $tableStartX = ($pageWidth - $totalWidth) / 2 + $pdf->getMargins()['left'];
        $pdf->SetX($tableStartX);
        
        for($i = 0; $i < count($header); $i++) {
            $pdf->Cell($w[$i], 8, $header[$i], 1, 0, 'C', true);
        }
        $pdf->Ln();
        
        // Données avec formatage amélioré
        $pdf->SetFillColor(255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('helvetica', '', 9);
        
        $fill = false;
        
        if (empty($produits)) {
            $pdf->SetX($tableStartX);
            $pdf->Cell($totalWidth, 8, 'AUCUN PRODUIT TROUVÉ', 1, 0, 'C', $fill);
            $pdf->Ln();
        } else {
            foreach($produits as $produit) {
                $pdf->SetX($tableStartX);
                
                // ID
                $pdf->Cell($w[0], 7, $produit['id'], 1, 0, 'C', $fill);
                
                // Nom (tronqué si trop long)
                $nom = strlen($produit['nom']) > 30 ? substr($produit['nom'], 0, 27) . '...' : $produit['nom'];
                $pdf->Cell($w[1], 7, $nom, 1, 0, 'L', $fill);
                
                // Prix
                $pdf->Cell($w[2], 7, number_format($produit['prix'], 2) . ' MAD', 1, 0, 'R', $fill);
                
                // Stock avec couleur conditionnelle
                $stockColor = '';
                if ($produit['stock'] <= 5) {
                    $pdf->SetTextColor(220, 53, 69); // Rouge pour stock faible
                } elseif ($produit['stock'] <= 15) {
                    $pdf->SetTextColor(255, 193, 7); // Orange pour stock moyen
                }
                $pdf->Cell($w[3], 7, $produit['stock'], 1, 0, 'C', $fill);
                $pdf->SetTextColor(0); // Réinitialiser la couleur
                
                // Total vendu
                $pdf->Cell($w[4], 7, $produit['total_vendu'], 1, 0, 'C', $fill);
                
                // Chiffre d'affaires
                $pdf->Cell($w[5], 7, number_format($produit['chiffre_affaires'], 2) . ' MAD', 1, 0, 'R', $fill);
                
                // Description (tronquée si trop longue)
                $description = $produit['description'] ?? '';
                if (strlen($description) > 80) {
                    $description = substr($description, 0, 77) . '...';
                }
                $pdf->Cell($w[6], 7, $description, 1, 0, 'L', $fill);
                
                $pdf->Ln();
                $fill = !$fill;
            }
        }
        
        // Ligne de totaux
        $pdf->SetY($pdf->GetY() + 5);
        $pdf->SetX($tableStartX);
        $pdf->SetFillColor(40, 167, 69); // Vert pour les totaux
        $pdf->SetTextColor(255);
        $pdf->SetFont('helvetica', 'B', 10);
        
        $pdf->Cell($w[0] + $w[1] + $w[2], 8, 'TOTAUX GÉNÉRAUX', 1, 0, 'C', true);
        $pdf->Cell($w[3], 8, $totalStock, 1, 0, 'C', true);
        $pdf->Cell($w[4], 8, $totalVentes, 1, 0, 'C', true);
        $pdf->Cell($w[5] + $w[6], 8, number_format($totalCA, 2, ',', ' ') . ' MAD', 1, 0, 'C', true);
        
        // Notes en bas de page
        $pdf->SetY($pdf->GetY() + 10);
        $pdf->SetTextColor(128, 128, 128);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, '* Les produits en rouge ont un stock faible (≤ 5 unités)', 0, 1, 'L');
        $pdf->Cell(0, 5, '* Les produits en orange ont un stock moyen (6-15 unités)', 0, 1, 'L');
        
        // Output
        $pdf->Output('rapport_produits_complet_' . date('Y-m-d') . '.pdf', 'D');
        
    } catch(Exception $e) {
        die("Erreur lors de l'export PDF : " . $e->getMessage());
    }
}
?>