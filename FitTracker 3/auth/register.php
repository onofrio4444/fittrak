<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);

    // Validazione
    if (empty($username)) {
        $errors['username'] = 'Username obbligatorio';
    } elseif (strlen($username) < 4) {
        $errors['username'] = 'Username troppo corto (min 4 caratteri)';
    } elseif (usernameExists($username)) {
        $errors['username'] = 'Username già in uso';
    }

    if (empty($email)) {
        $errors['email'] = 'Email obbligatoria';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email non valida';
    } elseif (emailExists($email)) {
        $errors['email'] = 'Email già registrata';
    }

    if (empty($password)) {
        $errors['password'] = 'Password obbligatoria';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password troppo corta (min 8 caratteri)';
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Le password non coincidono';
    }

    if (empty($errors)) {
        if (registerUser($username, $email, $password, $first_name, $last_name)) {
            $success = true;
        } else {
            $errors['general'] = 'Errore durante la registrazione. Riprova più tardi.';
        }
    }
}

include '../includes/header.php';
?>

<div class="register-container">
    <h1>Crea il tuo account FitTracker</h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            Registrazione completata con successo! <a href="login.php">Accedi ora</a>
        </div>
    <?php else: ?>
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="register.php" id="register-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">Nome</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                    <?php if (!empty($errors['first_name'])): ?>
                        <span class="error"><?php echo $errors['first_name']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Cognome</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                    <?php if (!empty($errors['last_name'])): ?>
                        <span class="error"><?php echo $errors['last_name']; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="username">Username*</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                <?php if (!empty($errors['username'])): ?>
                    <span class="error"><?php echo $errors['username']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="email">Email*</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                <?php if (!empty($errors['email'])): ?>
                    <span class="error"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password*</label>
                    <input type="password" id="password" name="password" required>
                    <?php if (!empty($errors['password'])): ?>
                        <span class="error"><?php echo $errors['password']; ?></span>
                    <?php endif; ?>
                    <div class="password-hint">Minimo 8 caratteri</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Conferma Password*</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <?php if (!empty($errors['confirm_password'])): ?>
                        <span class="error"><?php echo $errors['confirm_password']; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Registrati</button>
                <div class="login-link">
                    Hai già un account? <a href="login.php">Accedi</a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
// Validazione lato client
document.getElementById('register-form').addEventListener('submit', function(e) {
    let valid = true;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    // Controlla che le password coincidano
    if (password !== confirmPassword) {
        alert('Le password non coincidono');
        valid = false;
    }
    
    // Controlla la lunghezza della password
    if (password.length < 8) {
        alert('La password deve essere di almeno 8 caratteri');
        valid = false;
    }
    
    if (!valid) {
        e.preventDefault();
    }
});
</script>

<?php include '../includes/footer.php'; ?>