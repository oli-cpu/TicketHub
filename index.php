<?php
include("connectpdo.php");


#nehmen was wichtig ist (SQL)
$sql="SELECT
    pkArtikelID,
    fldArtikelname,
    fldPreis
FROM
    stu141.tblArtikel
ORDER BY fldPreis DESC";

$stmt = $conn->query($sql);

#HTML
?>

<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>M141 Artikelübersicht</title>
</head>

<body>

<div>

<h1>InfP.24 Startformular</h1>
<h2>M141 Hardware Übersicht V2.0</h2>

<div>
    <a href="M141Erfassenpdo.php">+ Neuer Hardware Eintrag</a>
    <a href="M141Bestellungkomplettpdo.php">+ Neue Bestellung</a>
    <a href="http://localhost/prj3/kundensicht.php">👥 Kundenübersicht</a>
</div>

<table>
<thead>
<tr>
<th>PK</th>
<th>Produkt</th>
<th>Preis (CHF)</th>
<th>Aktion</th>
</tr>
</thead>

<tbody>

<?php
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>
        <td>" . htmlspecialchars($row['pkArtikelID']) . "</td>
        <td>" . htmlspecialchars($row['fldArtikelname']) . "</td>
        <td>CHF " . htmlspecialchars($row['fldPreis']) . "</td>
        <td>
            <a href='M141Editpdo.php?pk=" . urlencode($row['pkArtikelID']) . "'>✏</a>
            <a href='M141Deletepdo.php?pk=" . urlencode($row['pkArtikelID']) . "' onclick=\"return confirm('Wirklich löschen?')\">✖</a>
        </td>
    </tr>";
}

$conn = null;
?>

</tbody>
</table>

<div>
    <?php echo date("Y"); ?> Imperium Novum Romanum – König Oliver Edition
</div>

</div>

</body>
</html>
