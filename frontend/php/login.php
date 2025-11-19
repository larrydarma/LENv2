<?php
session_start();

// Si el usuario ya está logueado, redirige según su rol
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['rol'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: inteligencia.php');
    }
    exit;
}

// Ruta del archivo de usuarios
$filename = __DIR__ . '/backend/users.json';
if (!file_exists($filename)) {
    file_put_contents($filename, json_encode([]));
}

$users = json_decode(file_get_contents($filename), true) ?? [];

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Por favor, completa todos los campos.';
        header('Location: login.php');
        exit;
    }

    $found = false;

    foreach ($users as $user) {
        if (
            ($user['username'] === $username || $user['email'] === $username) &&
            password_verify($password, $user['password'])
        ) {
            $found = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['rol'] = $user['rol'] ?? 'usuario'; // si no tiene rol, por defecto 'usuario'

            // Redirige según el rol
            if ($_SESSION['rol'] === 'admin') {
                header('Location: admin.php');
            } else {
                header('Location: inteligencia.php');
            }
            exit;
        }
    }

    $_SESSION['error'] = 'Usuario o contraseña incorrectos.';
    header('Location: login.php');
    exit;
}

// Mensajes
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LendFind</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="ai-header">
                <div class="ai-icon">
                    <svg viewBox="0 0 24 24" width="48" height="48">
                        <path fill="currentColor" d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 5.5V7H9V5.5L3 7V9L5 9.5V15.5L3 16V18L9 16.5V18H15V16.5L21 18V16L19 15.5V9.5L21 9Z"/>
                    </svg>
                </div>
                <h1>LendFind</h1>
            </div>

            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif ($success): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="">
                <div class="form-group">
                    <label for="username">Usuario o Correo</label>
                    <input type="text" id="username" name="username" required placeholder="Ingresa tu usuario o correo">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña">
                </div>

                <div class="form-options">
                    <label class="checkbox">
                        <input type="checkbox" id="remember" name="remember">
                        <span class="checkmark"></span>
                        Recordar sesión
                    </label>
                </div>

                <button type="submit" class="login-btn" name="login">
                    <span>Iniciar Sesión</span>
                </button>

                <div class="register-link">
                    ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
                </div>
            </form>
        </div>

        <div class="floating-elements">
            <div class="floating-element element-1"></div>
            <div class="floating-element element-2"></div>
            <div class="floating-element element-3"></div>
        </div>
    </div>
</body>
</html>
