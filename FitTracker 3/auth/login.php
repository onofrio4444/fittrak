<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (loginUser($username, $password)) {
        header('Location: ../pages/dashboard.php');
        exit();
    } else {
        $error = 'Username o password non validi';
    }
}

include '../includes/header.php';
?>

<div class="login-container">
    <h1>Accedi a FitTracker</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Accedi</button>
    </form>
    
    <div class="login-links">
        <a href="register.php">Registrati</a> | <a href="#">Password dimenticata?</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>