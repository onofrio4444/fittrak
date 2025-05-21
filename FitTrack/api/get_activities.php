<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

$user_id = $_SESSION['user_id'];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

try {
    $stmt = $pdo->prepare("SELECT * FROM activities WHERE user_id = ? ORDER BY start_time DESC LIMIT ?");
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $activities = $stmt->fetchAll();
    
    echo json_encode($activities);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>