

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contatti - FitTrack</title>
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
            color: #2c3e50;
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
        input[type="email"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        textarea {
            height: 150px;
            resize: vertical;
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

        .error {
            color: #e74c3c;
            margin-top: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .contact-info {
            margin-top: 30px;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    

    <div class="container">
        <h1>Contatti</h1>


        <div class="contact-info">
            <h3>Informazioni di Contatto</h3>
            <p><strong>Email:</strong> supporto@fittrack.com</p>
            <p><strong>Telefono:</strong> +39 123 456 7890</p>
            <p><strong>Indirizzo:</strong> Via Roma, 123, 00100 Altamura, Italia</p>
        </div>

        <div class="button-group">
            <a href="dashboard.php" class="btn btn-secondary">Torna alla Dashboard</a>
        </div>
    </div>
</body>
</html>
