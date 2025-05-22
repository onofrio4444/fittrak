<?php
session_start();
require 'connessione.php';

if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit();
}

$id_utente = $_SESSION['utente_id'];
$messaggio = '';

// Eliminazione attività
if (isset($_GET['elimina'])) {
    $id_attivita = $_GET['elimina'];
    $sql = "DELETE FROM attivita WHERE id = ? AND id_utente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_attivita, $id_utente);
    
    if ($stmt->execute()) {
        $messaggio = '<div class="success">Attività eliminata con successo!</div>';
    } else {
        $messaggio = '<div class="error">Errore durante l\'eliminazione</div>';
    }
}

// Ricerca e filtri
$filtro_tipo = $_GET['tipo'] ?? '';
$filtro_data = $_GET['data'] ?? '';

$sql = "SELECT * FROM attivita WHERE id_utente = ?";
$params = array($id_utente);
$types = "i";

if (!empty($filtro_tipo)) {
    $sql .= " AND tipo_attivita = ?";
    $params[] = $filtro_tipo;
    $types .= "s";
}

if (!empty($filtro_data)) {
    $sql .= " AND DATE(data_attivita) = ?";
    $params[] = $filtro_data;
    $types .= "s";
}

$sql .= " ORDER BY data_attivita DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$attivita = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Le tue attività - FitTrack</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .filtri {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .filtri label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .filtri select, .filtri input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .filtri button {
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .filtri button:hover {
            background-color: #2980b9;
        }
        .btn-nuova {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2ecc71;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .btn-nuova:hover {
            background-color: #27ae60;
        }
        .tabella-attivita {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .tabella-attivita th, 
        .tabella-attivita td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .tabella-attivita th {
            background-color: #3498db;
            color: white;
        }
        .tabella-attivita tr:hover {
            background-color: #f9f9f9;
        }
        .azioni a {
            display: inline-block;
            padding: 5px 10px;
            margin-right: 5px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 14px;
        }
        .modifica {
            background-color: #f39c12;
            color: white;
        }
        .modifica:hover {
            background-color: #e67e22;
        }
        .elimina {
            background-color: #e74c3c;
            color: white;
        }
        .elimina:hover {
            background-color: #c0392b;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

         /* Navbar Styles */
        .navbar {
            background-color: #2c3e50;
            padding: 10px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }
        
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-welcome {
            color: white;
            font-size: 14px;
        }
        
        .navbar-links {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .navbar-link {
            display: flex;
            align-items: center;
            gap: 5px;
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s;
            font-size: 14px;
        }
        
        .navbar-link:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .navbar-link.logout {
            background-color: #e74c3c;
        }
        
        .navbar-link.logout:hover {
            background-color: #c0392b;
        }
        
        .icon {
            width: 16px;
            height: 16px;
            fill: currentColor;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
        }
        
        .riepilogo {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .attivita-box {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .attivita {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #3498db;
        }
        
        .link-rapidi {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
<!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="dashboard.php" class="navbar-brand">FitTrack</a>
            
            <div class="navbar-user">
                <span class="user-welcome">Benvenuto, <?php echo htmlspecialchars($_SESSION['utente_username']); ?>!</span>
                
                <div class="navbar-links">
                    <a href="profilo.php" class="navbar-link">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        Profilo
                    </a>
                    
                    <a href="impostazioni.php" class="navbar-link">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.82,11.69,4.82,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/>
                        </svg>
                        Impostazioni
                    </a>
                    
                    <a href="logout.php" class="navbar-link logout">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

<div class="container">
    <h1>Le tue attività</h1>
    
    <?php echo $messaggio; ?>
    
    <div class="filtri">
        <div>
            <label for="tipo">Filtra per tipo:</label>
            <select id="tipo" name="tipo" onchange="applicaFiltri()">
                <option value="">Tutti i tipi</option>
                <option value="Corsa" <?= $filtro_tipo == 'Corsa' ? 'selected' : '' ?>>Corsa</option>
                <option value="Ciclismo" <?= $filtro_tipo == 'Ciclismo' ? 'selected' : '' ?>>Ciclismo</option>
                <option value="Nuoto" <?= $filtro_tipo == 'Nuoto' ? 'selected' : '' ?>>Nuoto</option>
                <option value="Palestra" <?= $filtro_tipo == 'Palestra' ? 'selected' : '' ?>>Palestra</option>
            </select>
        </div>
        <div>
            <label for="data">Filtra per data:</label>
            <input type="date" id="data" name="data" value="<?= $filtro_data ?>" onchange="applicaFiltri()">
        </div>
        <div style="align-self: flex-end;">
            <button onclick="resettaFiltri()">Reset filtri</button>
        </div>
    </div>
    
    <a href="attivita_aggiungi.php" class="btn-nuova">+ Nuova Attività</a>
    
    <table class="tabella-attivita">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Durata (min)</th>
                <th>Distanza (km)</th>
                <th>Calorie</th>
                <th>Data/Ora</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($attivita->num_rows > 0): ?>
                <?php while ($att = $attivita->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($att['tipo_attivita']) ?></td>
                        <td><?= $att['durata'] ?></td>
                        <td><?= $att['distanza'] ?? '-' ?></td>
                        <td><?= $att['calorie'] ?? '-' ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($att['data_attivita'])) ?></td>
                        <td class="azioni">
                            <a href="attivita_modifica.php?id=<?= $att['id'] ?>" class="modifica">Modifica</a>
                            <a href="attivita.php?elimina=<?= $att['id'] ?>" class="elimina" 
                               onclick="return confirm('Sei sicuro di voler eliminare questa attività?')">Elimina</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Nessuna attività trovata</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>

<script>
function applicaFiltri() {
    const tipo = document.getElementById('tipo').value;
    const data = document.getElementById('data').value;
    
    let url = 'attivita.php?';
    if (tipo) url += `tipo=${tipo}&`;
    if (data) url += `data=${data}`;
    
    window.location.href = url;
}

function resettaFiltri() {
    window.location.href = 'attivita.php';
}
</script>
</body>
</html>