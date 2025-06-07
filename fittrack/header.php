<?php
// Non avviare la sessione qui perché sarà già avviata in login.php
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FitTrack</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-header {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .login-header .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .logo i {
            margin-right: 10px;
            font-size: 1.8rem;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
        }
        
        .nav-links a:hover {
            opacity: 0.8;
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .login-header .container {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="login-header">

    
        <div class="container">
            <a href="index.php" class="logo">
                <i class="fas fa-heartbeat"></i>
                <span>FitTrack</span>
            </a>
            
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="registrazione.php">Registrati</a>
                <a href="password_dimenticata.php">Password dimenticata?</a>
            </nav>
        </div>
    </header>