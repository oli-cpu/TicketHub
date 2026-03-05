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
        $pdo->prepare("DELETE FROM tblseat WHERE fkEvent = ?")->execute([$delId]);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TicketHub</title>
    <style>
        /* Globaler Dark Look */
        body { 
            font-family: Arial, sans-serif; 
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
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* Toolbar */
        .toolbar { margin-bottom: 30px; display: flex; gap: 10px; }

        /* Buttons */
        .btn { 
            padding: 10px 16px; 
            border-radius: 4px; 
            text-decoration: none; 
            font-size: 14px; 
            font-weight: bold; 
            transition: 0.2s; 
            display: inline-block;
            border: none;
            cursor: pointer;
        }
        .btn-add { background: #ff9900; color: #000; }
        .btn-add:hover { background: #e68a00; }
        
        .btn-secondary { background: transparent; color: #888; border: 1px solid #444; }
        .btn-secondary:hover { color: #fff; border-color: #fff; }

        .btn-edit { background: #333; color: #ff9900; border: 1px solid #ff9900; font-size: 12px; }
        .btn-edit:hover { background: #ff9900; color: #000; }

        .btn-delete { background: transparent; color: #ff4444; border: 1px