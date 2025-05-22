<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benvenuto su FitTrack</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }
        .container {
            text-align: center;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        h1 span {
            color: #1e88e5;
        }
        p {
            margin-bottom: 30px;
            font-size: 16px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px;
            background-color: #1e88e5;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #1565c0;
        }
        .btn-register {
            background-color: #4caf50;
        }
        .btn-register:hover {
            background-color: #388e3c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Benvenuto su <span>FitTrack</span></h1>
       
        <p>Traccia e analizza le tue attività fisiche con semplicità.</p>
        <a href="login.php" class="btn">Login</a>
        <a href="registrazione.php" class="btn btn-register">Registrati</a>
    </div>
    <br><br>
    <?php 
    
    include 'footer.php'; ?>
</body>
</html>
