<?php
require_once '../includes/config.php';
redirectIfNotLoggedIn();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['confirm']) || $input['confirm'] !== true) {
        echo json_encode(['status' => 'error', 'message' => 'Conferma richiesta']);
        exit();
    }
    
    try {
        $pdo->beginTransaction();
        
        // Elimina tutte le attività dell'utente
        $stmt = $pdo->prepare("DELETE FROM activities WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Elimina tutti gli eventi del calendario
        $stmt = $pdo->prepare("DELETE FROM calendar_events WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Elimina tutte le statistiche
        $stmt = $pdo->prepare("DELETE FROM user_stats WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Elimina tutti gli obiettivi
        $stmt = $pdo->prepare("DELETE FROM goals WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Infine, elimina l'utente
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $pdo->commit();
        
        // Distruggi la sessione
        session_destroy();
        
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metodo non supportato']);
}
?>