<?php
session_start();
if(!isset($_SESSION['USER_ID'])){ 
    header("location:login.php"); 
    exit; 
}

// En-têtes pour Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=template_clients.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1'>";
echo "<tr style='background-color: #4285f4; color: white; font-weight: bold;'>";
echo "<th>Nom</th>";
echo "<th>Email</th>";
echo "<th>Téléphone</th>";
echo "<th>Adresse</th>";
echo "</tr>";
echo "<tr>";
echo "<td> Jean Dupont</td>";
echo "<td> jean@example.com</td>";
echo "<td> 0123456789</td>";
echo "<td> 123 Rue Example, Paris</td>";
echo "</tr>";
echo "</table>";
?>