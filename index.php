<?php
include("connectpdo.php");

/**
 * SQL-Anpassung: Wir holen die Events und verknüpfen sie (JOIN) mit den Künstlern
 * und dem Veranstaltungsort, um eine aussagekräftige Übersicht zu erhalten.
 */
$sql = "SELECT 
            e.pkEvent, 
            e.fldEventName, 
            e.fldEventDatum, 
            e.fldBasisPreis, 
            a.fldArtistName,
            o.fldOrtName
        FROM 
            tblevent e
        LEFT JOIN tblartist a ON e.fkArtist = a.pkArtist
        LEFT JOIN tblraumplan r ON e.fkRaumplan = r.pkRaumplanID
        LEFT JOIN tblort o ON r.fkOrt = o.pkOrt
        ORDER BY e.fldEventDatum ASC";

$stmt = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TicketHub - Eventübersicht</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .btn { text-decoration: none; padding: 5px 10px; background: #eee; border: 1px solid #ccc; color: #333; }
    </style>
</head>

<body>

<div>
    <h1>TicketHub Admin-Panel</h1>
    <h2>Veranstaltungsübersicht</h2>

    <div style="margin-bottom: 20px;">
        <a class="btn" href="EventErfassen.php">➕ Neues Event erstellen</a>
        <a class="btn" href="Kundenuebersicht.php">👥 User-Verwaltung</a>
        <a class="btn" href="Bestellungen.php">🎟 Buchungen einsehen</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Event</th>
                <th>Künstler</th>
                <th>Ort</th>
                <th>Datum & Zeit</th>
                <th>Basispreis</th>
                <th>Aktion</th>
            </tr>
        </thead>

        <tbody>
        <?php
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Datum schöner formatieren
            $datum = date("d.m.Y H:i", strtotime($row['fldEventDatum']));
            
            echo "<tr>
                <td>" . htmlspecialchars($row['pkEvent']) . "</td>
                <td><strong>" . htmlspecialchars($row['fldEventName']) . "</strong></td>
                <td>" . htmlspecialchars($row['fldArtistName'] ?? 'Kein Künstler') . "</td>
                <td>" . htmlspecialchars($row['fldOrtName'] ?? 'Unbekannter Ort') . "</td>
                <td>" . $datum . "</td>
                <td>CHF " . number_format($row['fldBasisPreis'], 2, '.', "'") . "</td>
                <td>
                    <a href='EventEdit.php?pk=" . urlencode($row['pkEvent']) . "' title='Bearbeiten'>✏️</a>
                    <a href='EventDelete.php?pk=" . urlencode($row['pkEvent']) . "' 
                       onclick=\"return confirm('Event unwiderruflich löschen?')\" title='Löschen'>❌</a>
                </td>
            </tr>";
        }
        $conn = null;
        ?>
        </tbody>
    </table>

    <br>
    <hr>
    <div>
        &copy; <?php echo date("Y"); ?> TicketHub – Premium Ticketing System
    </div>

</div>

</body>
</html>