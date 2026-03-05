<?php
session_start();
require_once '../connectpdo.php';

// Zugriffsschutz: Nur Admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Zugriff verweigert.");
}

// Logik zum Löschen eines Events
if (isset($_GET['delete_id'])) {
    $delId = $_GET['delete_id'];
    try {
        $pdo->beginTransaction();

        // Zuerst Tickets löschen, die auf dieses Event verweisen
        $pdo->prepare("DELETE FROM tblticket WHERE fkEvent = ?")->execute([$delId]);

        // Dann Sitzplätze löschen
        $pdo->prepare("DELETE FROM tblseat WHERE fkEvent = ?")->execute([$delId]);

        // Zuletzt das Event selbst löschen
        $pdo->prepare("DELETE FROM tblevent WHERE pkEvent = ?")->execute([$delId]);

        $pdo->commit();
        header("Location: admin_dashboard.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Fehler beim Löschen: " . $e->getMessage());
    }
}

// Events für die Tabelle laden
try {
    $sql = "SELECT e.pkEvent, e.fldEventName, e.fldEventDatum, e.fldBasisPreis FROM tblevent e ORDER BY e.fldEventDatum ASC";
    $events = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Datenbankfehler: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TicketHub</title>
    <style>
        /* Globaler Dark Look */
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #000;
            color: #fff;
            margin: 0;
            padding: 40px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: #121212;
            padding: 30px;
            border-radius: 8px;
            border: 1px solid #222;
        }

        /* Branding */
        .brand-header {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .brand-header span.highlight {
            background: #ff9900;
            color: #000;
            padding: 2px 10px;
            border-radius: 4px;
            margin-left: 5px;
        }

        /* Toolbar */
        .toolbar { margin-bottom: 30px; display: flex; gap: 15px; }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: 0.2s;
            display: inline-block;
            border: none;
            cursor: pointer;
            text-align: center;
        }
        .btn-add { background: #ff9900; color: #000; }
        .btn-add:hover { background: #e68a00; transform: translateY(-2px); }

        .btn-secondary { background: transparent; color: #888; border: 1px solid #444; }
        .btn-secondary:hover { color: #fff; border-color: #fff; }

        .btn-edit { background: #222; color: #ff9900; border: 1px solid #ff9900; margin-right: 5px; }
        .btn-edit:hover { background: #ff9900; color: #000; }

        .btn-delete { background: transparent; color: #ff4444; border: 1px solid #444; }
        .btn-delete:hover { background: #ff4444; color: #fff; border-color: #ff4444; }

        /* Tabelle */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            text-align: left;
            color: #888;
            font-size: 12px;
            text-transform: uppercase;
            padding: 15px;
            border-bottom: 1px solid #222;
        }
        td {
            padding: 20px 15px;
            border-bottom: 1px solid #1a1a1a;
            font-size: 15px;
        }
        tr:hover td { background: #161616; }

        .event-name { font-weight: bold; color: #fff; }
        .event-date { color: #ff9900; font-family: monospace; }

        /* Status Meldungen */
        .msg {
            background: #1a3320;
            color: #44ff44;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #224422;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="brand-header">
        <div>TicketHub<span class="highlight">ADMIN</span></div>
        <a href="../index.php" class="btn btn-secondary">← Zum Shop</a>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="msg">✓ Event und alle zugehörigen Daten wurden dauerhaft gelöscht.</div>
    <?php endif; ?>

    <div class="toolbar">
        <a href="add_event.php" class="btn btn-add">+ Neues Event erstellen</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Event & Details</th>
                <th>Datum</th>
                <th>Preis</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $e): ?>
            <tr>
                <td style="color: #444;"><?= $e['pkEvent'] ?></td>
                <td>
                    <div class="event-name"><?= htmlspecialchars($e['fldEventName']) ?></div>
                </td>
                <td>
                    <div class="event-date"><?= date("d.m.Y H:i", strtotime($e['fldEventDatum'])) ?></div>
                </td>
                <td>CHF <?= number_format($e['fldBasisPreis'], 2) ?></td>
                <td>
                    <a href="edit_event.php?id=<?= $e['pkEvent'] ?>" class="btn btn-edit">Ändern</a>
                    <a href="admin_dashboard.php?delete_id=<?= $e['pkEvent'] ?>"
                       class="btn btn-delete"
                       onclick="return confirm('ACHTUNG: Willst du dieses Event wirklich löschen? Alle verkauften Tickets und Sitzplätze gehen verloren!')">
                       Löschen
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
