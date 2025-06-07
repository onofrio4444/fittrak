<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit();
}

include 'connessione.php';
$id_utente = $_SESSION['utente_id'];

$errori = [];
$successo = false;
$nome = '';
$obiettivo_peso = null;
$obiettivo_calorie = null;
$obiettivo_passi = null;
$obiettivo_attivita = null;
$data_scadenza = '';
$tipo_obiettivo = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $tipo_obiettivo = trim($_POST['tipo_obiettivo'] ?? '');
    $data_scadenza = $_POST['data_scadenza'] ?? '';

    // Set values based on goal type
    if ($tipo_obiettivo == 'peso') {
        $obiettivo_peso = trim($_POST['obiettivo_peso'] ?? '');
    } elseif ($tipo_obiettivo == 'calorie') {
        $obiettivo_calorie = trim($_POST['obiettivo_calorie'] ?? '');
    } elseif ($tipo_obiettivo == 'passi') {
        $obiettivo_passi = trim($_POST['obiettivo_passi'] ?? '');
    } elseif ($tipo_obiettivo == 'attivita') {
        $obiettivo_attivita = trim($_POST['obiettivo_attivita'] ?? '');
    }

    // Validation
    if (empty($nome)) $errori[] = "Il nome dell'obiettivo è obbligatorio";
    if (empty($data_scadenza)) $errori[] = "La data di scadenza è obbligatoria";
    if (empty($tipo_obiettivo)) $errori[] = "Il tipo di obiettivo è obbligatorio";

    if (empty($errori)) {
        $sql = "INSERT INTO obiettivi (id_utente, nome, data_scadenza, obiettivo_peso, obiettivo_calorie, obiettivo_passi, obiettivo_attivita) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $errori[] = "Errore nella preparazione della query: " . $conn->error;
        } else {
            // Convert empty strings to null for database
            $obiettivo_peso_val = !empty($obiettivo_peso) ? $obiettivo_peso : null;
            $obiettivo_calorie_val = !empty($obiettivo_calorie) ? $obiettivo_calorie : null;
            $obiettivo_passi_val = !empty($obiettivo_passi) ? $obiettivo_passi : null;
            $obiettivo_attivita_val = !empty($obiettivo_attivita) ? $obiettivo_attivita : null;
            
            $stmt->bind_param("issddii", 
                $id_utente,
                $nome,
                $data_scadenza,
                $obiettivo_peso_val,
                $obiettivo_calorie_val,
                $obiettivo_passi_val,
                $obiettivo_attivita_val
            );

            if ($stmt->execute()) {
                $_SESSION['messaggio_successo'] = "Obiettivo aggiunto con successo!";
                header("Location: obiettivi.php");
                exit();
            } else {
                $errori[] = "Errore durante il salvataggio: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Obiettivo - FitTrack</title>
    <link rel="icon" href="img/clipboard2-pulse.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            width: 90%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color:#3498db;
            margin-bottom: 30px;
        }
        .card {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-secondary {
            background-color: #95a5a6;
        }
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        .alert-danger {
            margin-bottom: 20px;
        }
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .goal-type-fields {
            display: none;
        }
    </style>
</head>
<body>
    <?php
    // Include navbar
    /*if (file_exists('navbar-attivita.php')) {
        include 'navbar-attivita.php';
    } elseif (file_exists('navbar.php')) {
        include 'navbar.php';
    } else {
        echo "<nav class='navbar navbar-expand-lg navbar-dark bg-primary mb-4'>
                <div class='container'>
                    <a class='navbar-brand' href='#'>FitTrack</a>
                </div>
              </nav>";
    }
    */?>

    <div class="container">
        <h1>Aggiungi Nuovo Obiettivo</h1>

        <?php if (!empty($errori)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errori as $errore): ?>
                        <li><?= htmlspecialchars($errore) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label for="nome">Nome Obiettivo *</label>
                    <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($nome) ?>">
                </div>

                <div class="form-group">
                    <label for="tipo_obiettivo">Tipo di Obiettivo *</label>
                    <select id="tipo_obiettivo" name="tipo_obiettivo" required>
                        <option value="">Seleziona un tipo di obiettivo</option>
                        <option value="peso" <?= $tipo_obiettivo == 'peso' ? 'selected' : '' ?>>Peso</option>
                        <option value="calorie" <?= $tipo_obiettivo == 'calorie' ? 'selected' : '' ?>>Calorie</option>
                        <option value="passi" <?= $tipo_obiettivo == 'passi' ? 'selected' : '' ?>>Passi</option>
                        <option value="attivita" <?= $tipo_obiettivo == 'attivita' ? 'selected' : '' ?>>Attività</option>
                    </select>
                </div>

                <div id="obiettivo_peso_group" class="form-group goal-type-fields">
                    <label for="obiettivo_peso">Obiettivo Peso (kg)</label>
                    <input type="number" step="0.1" id="obiettivo_peso" name="obiettivo_peso" value="<?= htmlspecialchars($obiettivo_peso ?? '') ?>">
                </div>

                <div id="obiettivo_calorie_group" class="form-group goal-type-fields">
                    <label for="obiettivo_calorie">Obiettivo Calorie (kcal)</label>
                    <input type="number" id="obiettivo_calorie" name="obiettivo_calorie" value="<?= htmlspecialchars($obiettivo_calorie ?? '') ?>">
                </div>

                <div id="obiettivo_passi_group" class="form-group goal-type-fields">
                    <label for="obiettivo_passi">Obiettivo Passi giornalieri</label>
                    <input type="number" id="obiettivo_passi" name="obiettivo_passi" value="<?= htmlspecialchars($obiettivo_passi ?? '') ?>">
                </div>

                <div id="obiettivo_attivita_group" class="form-group goal-type-fields">
                    <label for="obiettivo_attivita">Obiettivo Attività (minuti)</label>
                    <input type="number" id="obiettivo_attivita" name="obiettivo_attivita" value="<?= htmlspecialchars($obiettivo_attivita ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="data_scadenza">Data di Scadenza *</label>
                    <input type="date" id="data_scadenza" name="data_scadenza" required value="<?= htmlspecialchars($data_scadenza) ?>">
                </div>

                <div class="button-group">
                    <button type="submit" class="btn">Salva Obiettivo</button>
                    <a href="obiettivi.php" class="btn btn-secondary">Annulla</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('tipo_obiettivo').addEventListener('change', function() {
            // Hide all fields first
            document.querySelectorAll('.goal-type-fields').forEach(field => {
                field.style.display = 'none';
            });
            
            // Show only the selected field
            const selectedType = this.value;
            if (selectedType === 'peso') {
                document.getElementById('obiettivo_peso_group').style.display = 'block';
            } else if (selectedType === 'calorie') {
                document.getElementById('obiettivo_calorie_group').style.display = 'block';
            } else if (selectedType === 'passi') {
                document.getElementById('obiettivo_passi_group').style.display = 'block';
            } else if (selectedType === 'attivita') {
                document.getElementById('obiettivo_attivita_group').style.display = 'block';
            }
        });

        // Trigger change event on page load if a type is already selected
        if (document.getElementById('tipo_obiettivo').value) {
            document.getElementById('tipo_obiettivo').dispatchEvent(new Event('change'));
        }
    </script>
</body>
</html>