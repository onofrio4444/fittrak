<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Arial', sans-serif;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            padding: 20px;
            /* Aggiungi qui il tuo contenuto o layout */
        }

        footer {
            background-color: #2c3e50;
            color: white;
            padding: 30px 0;
            text-align: center;
        }

        .footer-container {
            display: flex;
            justify-content: space-around;
            max-width: 1200px;
            margin: 0 auto;
            flex-wrap: wrap;
        }

        .footer-section {
            flex: 1;
            min-width: 200px;
            margin: 10px;
        }

        .footer-section h3 {
            margin-bottom: 15px;
            font-size: 18px;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section ul li {
            margin-bottom: 10px;
        }

        .footer-section ul li a {
            color: #bdc3c7;
            text-decoration: none;
        }

        .footer-section ul li a:hover {
            color: white;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .social-icons a {
            color: white;
            margin: 0 10px;
            font-size: 20px;
        }

        .copyright {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #34495e;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="content">
            <!-- Il contenuto principale della tua pagina va qui -->
        </div>

        <footer>
            <div class="footer-container">
                <div class="footer-section">
                    <h3>FitTrack</h3>
                    <p>Traccia e analizza le tue attività fisiche con semplicità.</p>
                </div>
                <div class="footer-section">
                    <h3>Link Utili</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="attivita.php">Le tue attività</a></li>
                        <li><a href="profilo.php">Profilo</a></li>
                        <li><a href="contatti.php">Contatti</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Informazioni</h3>
                    <ul>
                        <li><a href="chi-siamo.php">Chi Siamo</a></li>
                        <li><a href="politiche-privacy.php">Politiche di Privacy</a></li>
                        <li><a href="obiettivi.php">Obiettivi dell'Applicazione</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contatti</h3>
                    <ul>
                        <li>Email: info@fittrack.com</li>
                        <li>Telefono: +39 123 4567890</li>
                        <li>Indirizzo: Via Esempio, 123, Altamura, Italia</li>
                    </ul>
                </div>
            </div>
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="https://github.com/onofrio4444"><i class="fab fa-github"></i></a>
                <a href="https://www.instagram.com/ocutecchia/"><i class="fab fa-instagram"></i></a>
            </div>
            <div class="copyright">
                <p>&copy; 2025 FitTrack. Tutti i diritti riservati.
                    <br>
                    Onofrio Cutecchia
                </p>
            </div>
        </footer>
    </div>

</body>
</html>
