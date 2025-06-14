-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Giu 07, 2025 alle 18:18
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fittrack`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `attivita`
--

CREATE TABLE `attivita` (
  `id` int(11) NOT NULL,
  `id_utente` int(11) NOT NULL,
  `tipo_attivita` varchar(50) NOT NULL,
  `durata` int(11) NOT NULL,
  `distanza` decimal(6,2) DEFAULT NULL,
  `calorie` int(11) DEFAULT NULL,
  `data_attivita` datetime NOT NULL,
  `descrizione` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `attivita`
--

INSERT INTO `attivita` (`id`, `id_utente`, `tipo_attivita`, `durata`, `distanza`, `calorie`, `data_attivita`, `descrizione`) VALUES
(3, 1, 'Nuoto', 30, 900.00, 400, '2025-05-01 09:10:00', ''),
(4, 1, 'Camminata', 49, 5.00, 120, '2025-04-11 10:34:00', 'camminata chill'),
(6, 1, 'Corsa', 39, 7.00, 543, '2025-05-16 16:25:00', 'corsetta chill'),
(7, 1, 'Nuoto', 50, 5.00, 800, '2025-05-22 00:13:00', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `impostazioni`
--

CREATE TABLE `impostazioni` (
  `id` int(11) NOT NULL,
  `id_utente` int(11) NOT NULL,
  `notifica_email` tinyint(1) DEFAULT 1,
  `tema_scoruro` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `impostazioni`
--

INSERT INTO `impostazioni` (`id`, `id_utente`, `notifica_email`, `tema_scoruro`) VALUES
(1, 1, 1, 0),
(2, 2, 1, 0),
(4, 4, 1, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `impostazioni_utente`
--

CREATE TABLE `impostazioni_utente` (
  `id` int(11) NOT NULL,
  `id_utente` int(11) DEFAULT NULL,
  `notifiche_email` tinyint(1) DEFAULT 0,
  `notifiche_promemoria` tinyint(1) DEFAULT 0,
  `notifiche_obiettivi` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `impostazioni_utente`
--

INSERT INTO `impostazioni_utente` (`id`, `id_utente`, `notifiche_email`, `notifiche_promemoria`, `notifiche_obiettivi`) VALUES
(1, 1, 1, 0, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `obiettivi`
--

CREATE TABLE `obiettivi` (
  `id` int(11) NOT NULL,
  `id_utente` int(11) DEFAULT NULL,
  `obiettivo_peso` decimal(5,2) DEFAULT NULL,
  `obiettivo_calorie` int(11) DEFAULT NULL,
  `obiettivo_passi` int(11) DEFAULT NULL,
  `obiettivo_attivita` int(11) DEFAULT NULL,
  `data_scadenza` date DEFAULT NULL,
  `completato` tinyint(1) DEFAULT 0,
  `stato` varchar(20) DEFAULT 'in_corso',
  `nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `obiettivi`
--

INSERT INTO `obiettivi` (`id`, `id_utente`, `obiettivo_peso`, `obiettivo_calorie`, `obiettivo_passi`, `obiettivo_attivita`, `data_scadenza`, `completato`, `stato`, `nome`) VALUES
(4, 1, NULL, NULL, 10000, NULL, '2025-05-31', 1, 'in_corso', ''),
(6, 1, NULL, NULL, 2000, NULL, '2025-05-31', 1, 'in_corso', 'passi settimanali');

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `altezza` decimal(5,2) DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `nome` varchar(50) DEFAULT NULL,
  `cognome` varchar(50) DEFAULT NULL,
  `data_nascita` date DEFAULT NULL,
  `sesso` enum('M','F','Altro') DEFAULT NULL,
  `data_registrazione` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `utenti`
--

INSERT INTO `utenti` (`id`, `username`, `email`, `altezza`, `peso`, `password`, `nome`, `cognome`, `data_nascita`, `sesso`, `data_registrazione`) VALUES
(1, 'admin', 'pipo@gmail.com', 160.00, 60.00, '$2y$10$Vuwm0u5MQAErx.xM.bNrCeHT1eOcEOf5//hgKK.A2qzJ..7s.aDBq', 'Onofrio', 'Cutecchia', '2025-05-03', 'M', '2025-05-22 13:31:45'),
(2, 'mario.rossi', 'poppo@gmail.bom', NULL, NULL, '$2y$10$tV50hw9WbWTRW/.cMQJS7ut5BZIN0doaVr4zzBJH2juGDu.EzRnF.', 'poi', 'iop', '2025-05-02', 'M', '2025-05-22 18:14:57'),
(4, 'mimmo', 'mimmo@gmail.com', NULL, NULL, '$2y$10$7SVWxL.evgCl63DzB4AExOp8krk8bZwamSbLQh/l.R//hWGPcMh/y', 'mario', 'kart', '1999-02-10', 'M', '2025-06-06 16:20:25');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `attivita`
--
ALTER TABLE `attivita`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_utente` (`id_utente`);

--
-- Indici per le tabelle `impostazioni`
--
ALTER TABLE `impostazioni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_utente` (`id_utente`);

--
-- Indici per le tabelle `impostazioni_utente`
--
ALTER TABLE `impostazioni_utente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_utente` (`id_utente`);

--
-- Indici per le tabelle `obiettivi`
--
ALTER TABLE `obiettivi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_utente` (`id_utente`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `attivita`
--
ALTER TABLE `attivita`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `impostazioni`
--
ALTER TABLE `impostazioni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `impostazioni_utente`
--
ALTER TABLE `impostazioni_utente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `obiettivi`
--
ALTER TABLE `obiettivi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `attivita`
--
ALTER TABLE `attivita`
  ADD CONSTRAINT `attivita_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `impostazioni`
--
ALTER TABLE `impostazioni`
  ADD CONSTRAINT `impostazioni_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `impostazioni_utente`
--
ALTER TABLE `impostazioni_utente`
  ADD CONSTRAINT `impostazioni_utente_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`id`);

--
-- Limiti per la tabella `obiettivi`
--
ALTER TABLE `obiettivi`
  ADD CONSTRAINT `obiettivi_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
