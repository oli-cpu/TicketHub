<?php
session_start();
require_once '../connectpdo.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Zugriff verweigert.");
}

// Logik zum Löschen eines Events
if (isset($_GET['delete_id'])) {
    $delId = $_GET['delete_id'];
    try {
        $pdo->beginTransaction();
        // Zuerst abhängige Sitze löschen (Referenzielle Integrität)
        $pdo->prepare("DELETE FROM tblseat WHERE fkEvent = ?")->execute([$delId]);
        // Dann das Event löschen
        $pdo->prepare("DELETE FROM tblevent WHERE pkEvent = ?")->execute([$delId]);
        $pdo->commit();
        header("Location: admin_dashboard.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Fehler beim Löschen: " . $e->getMessage());
    }
}

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
    <title>Admin Dashboard - TicketHub</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; padding: 30px; background: #f4f7f6; }
        .container { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #007bff; color: white; }
        .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; color: white; display: inline-block; margin-right: 5px; }
        .btn-edit { background: #ffc107; color: #333; }
        .btn-delete { background: #dc3545; }
        .btn-add { background: #28a745; margin-bottom: 20px; font-weight: bold; }
        .msg { padding: 10px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Event Management</h2>
    
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="msg">Event erfolgreich gelöscht.</div>
    <?php endif; ?>

    <a href="add_event.php" class="btn btn-add">+ Neues Event anlegen</a>
    <a href="../index.php" class="btn" style="background: #6c757d;">Zurück zum Shop</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Event Name</th>
                <th>Datum</th>
                <th>Preis</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $e): ?>
            <tr>
                <td><?= $e['pkEvent'] ?></td>
                <td><strong><?= htmlspecialchars($e['fldEventName']) ?></strong></td>
                <td><?= date("d.m.Y H:i", strtotime($e['fldEventDatum'])) ?></td>
                <td>CHF <?= number_format($e['fldBasisPreis'], 2) ?></td>
                <td>
                    <a href="edit_event.php?id=<?= $e['pkEvent'] ?>" class="btn btn-edit">✏️ Ändern</a>
                    <a href="admin_dashboard.php?delete_id=<?= $e['pkEvent'] ?>" 
                       class="btn btn-delete" 
                       onclick="return confirm('Sicher löschen? Alle zugehörigen Sitzplätze werden ebenfalls entfernt!')">🗑️ Löschen</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>