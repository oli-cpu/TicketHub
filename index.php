<?php
include("connectpdo.php");

# Events aus der Datenbank abfragen
$sql = "SELECT 
            e.pkEvent,
            e.fldEventName,
            a.fldArtistName,
            e.fldEventDatum,
            r.fldRaumName,
            o.fldOrtName,
            o.fldKanton,
            e.fldBasisPreis,
            (SELECT COUNT(*) FROM tblseat s WHERE s.fkEvent = e.pkEvent AND s.fldStatus = 'frei') as verfuegbarePlaetze
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
    <title>Ticketcorner - Events & Tickets</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .header {
            background-color: #e30613;
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .nav-bar {
            background-color: #333;
            padding: 1rem 2rem;
            display: flex;
            gap: 2rem;
        }
        
        .nav-bar a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .nav-bar a:hover {
            background-color: #e30613;
        }
        
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .welcome-section h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .event-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        .event-image {
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 4rem;
        }
        
        .event-details {
            padding: 1.5rem;
        }
        
        .event-name {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .event-artist {
            color: #e30613;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .event-info {
            margin: 1rem 0;
            color: #666;
        }
        
        .event-info p {
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .event-info i {
            width: 20px;
            color: #e30613;
        }
        
        .event-price {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
            margin: 1rem 0;
        }
        
        .event-price small {
            font-size: 1rem;
            color: #666;
            font-weight: normal;
        }
        
        .availability {
            display: inline-block;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .availability.high {
            background-color: #d4edda;
            color: #155724;
        }
        
        .availability.medium {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .availability.low {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-primary {
            background-color: #e30613;
            color: white;
            width: 100%;
            text-align: center;
        }
        
        .btn-primary:hover {
            background-color: #b30000;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .action-buttons .btn-small {
            padding: 0.4rem 1rem;
            font-size: 0.9rem;
        }
        
        .footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
        }
        
        .footer a {
            color: #e30613;
            text-decoration: none;
        }
        
        .search-bar {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .search-btn {
            padding: 0.8rem 2rem;
            background-color: #e30613;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .search-btn:hover {
            background-color: #b30000;
        }
        
        .kategorie-badge {
            display: inline-block;
            padding: 0.2rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            background-color: #f0f0f0;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎫 Ticketcorner</h1>
        <p>Schweizer Ticketplattform für unvergessliche Erlebnisse</p>
    </div>
    
    <div class="nav-bar">
        <a href="index.php">🏠 Startseite</a>
        <a href="events.php">📅 Events</a>
        <a href="meine-tickets.php">🎟️ Meine Tickets</a>
        <a href="konto.php">👤 Mein Konto</a>
        <a href="admin.php">⚙️ Admin</a>
    </div>
    
    <div class="container">
        <div class="welcome-section">
            <h2>Willkommen bei Ticketcorner</h2>
            <p>Entdecken Sie die besten Events in Ihrer Nähe</p>
        </div>
        
        <div class="search-bar">
            <input type="text" class="search-input" placeholder="Suche nach Künstler, Event oder Ort...">
            <select class="search-input">
                <option value="">Alle Kategorien</option>
                <option value="konzert">Konzerte</option>
                <option value="festival">Festivals</option>
                <option value="theater">Theater</option>
                <option value="sport">Sport</option>
            </select>
            <button class="search-btn">🔍 Suchen</button>
        </div>
        
        <h2 style="margin-bottom: 1rem;">Aktuelle Events</h2>
        
        <div class="event-grid">
            <?php
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $eventDate = new DateTime($row['fldEventDatum']);
                $today = new DateTime();
                $interval = $today->diff($eventDate);
                $daysUntil = $interval->days;
                
                // Verfügbarkeit bestimmen
                $availableSeats = $row['verfuegbarePlaetze'];
                if ($availableSeats > 100) {
                    $availClass = 'high';
                    $availText = '✓ Viele Plätze verfügbar';
                } elseif ($availableSeats > 30) {
                    $availClass = 'medium';
                    $availText = '⚠︎ Begrenzte Verfügbarkeit';
                } else {
                    $availClass = 'low';
                    $availText = '‼︎ Nur noch wenige Plätze';
                }
                
                echo '<div class="event-card">';
                echo '<div class="event-image">🎪</div>';
                echo '<div class="event-details">';
                echo '<div class="event-name">' . htmlspecialchars($row['fldEventName']) . '</div>';
                echo '<div class="event-artist">' . htmlspecialchars($row['fldArtistName'] ?? 'Various Artists') . '</div>';
                
                echo '<div class="event-info">';
                echo '<p>📅 ' . $eventDate->format('d.m.Y H:i') . ' Uhr</p>';
                echo '<p>📍 ' . htmlspecialchars($row['fldRaumName'] ?? 'Location') . ', ' . htmlspecialchars($row['fldOrtName'] ?? '') . ' (' . htmlspecialchars($row['fldKanton'] ?? 'CH') . ')</p>';
                echo '</div>';
                
                echo '<div class="availability ' . $availClass . '">' . $availText . '</div>';
                
                echo '<div class="event-price">CHF ' . number_format($row['fldBasisPreis'], 2) . ' <small>pro Ticket</small></div>';
                
                echo '<a href="ticketkauf.php?event=' . $row['pkEvent'] . '" class="btn btn-primary">🎟️ Tickets kaufen</a>';
                
                // Admin-Aktionen (nur für eingeloggte Admins sichtbar)
                // Hier könnte eine Session-Prüfung eingebaut werden
                echo '<div class="action-buttons">';
                echo '<a href="event-bearbeiten.php?id=' . $row['pkEvent'] . '" class="btn btn-secondary btn-small">✏️ Bearbeiten</a>';
                echo '<a href="event-loeschen.php?id=' . $row['pkEvent'] . '" class="btn btn-secondary btn-small" onclick="return confirm(\'Event wirklich löschen?\')">🗑️ Löschen</a>';
                echo '</div>';
                
                echo '</div>';
                echo '</div>';
            }
            
            $conn = null;
            ?>
        </div>
        
        <div style="text-align: center; margin-top: 3rem;">
            <h3>Kein passendes Event gefunden?</h3>
            <p>Melde dich für unseren Newsletter an und erhalte exklusive Angebote!</p>
            <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 1rem;">
                <input type="email" placeholder="Deine E-Mail-Adresse" style="padding: 0.8rem; width: 300px; border: 1px solid #ddd; border-radius: 5px;">
                <button class="btn btn-primary">📧 Anmelden</button>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; 2025 Ticketcorner – Offizieller Vorverkauf</p>
        <p style="margin-top: 1rem;">
            <a href="#">AGB</a> | 
            <a href="#">Datenschutz</a> | 
            <a href="#">Impressum</a> | 
            <a href="#">Kontakt</a>
        </p>
    </div>
</body>
</html>