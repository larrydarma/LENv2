<?php
session_start();

// Ruta del archivo JSON
$filename = __DIR__ . '/backend/users.json';

// Si no existe, crear uno vacío
if (!file_exists($filename)) {
    file_put_contents($filename, json_encode([]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validaciones
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'Todos los campos son obligatorios.';
        header('Location: register.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Correo electrónico no válido.';
        header('Location: register.php');
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Las contraseñas no coinciden.';
        header('Location: register.php');
        exit;
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres.';
        header('Location: register.php');
        exit;
    }

    // Leer los usuarios existentes
    $users = json_decode(file_get_contents($filename), true);

    // Comprobar si el usuario o correo ya existen
    foreach ($users as $user) {
        if ($user['email'] === $email || $user['username'] === $username) {
            $_SESSION['error'] = 'El usuario o correo ya están registrados.';
            header('Location: register.php');
            exit;
        }
    }

    // Agregar el nuevo usuario
    $newUser = [
        'id' => uniqid(),
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'rol' => "usuario",
        'created_at' => date('Y-m-d H:i:s')
    ];

    $users[] = $newUser;

    // Guardar en el JSON
    file_put_contents($filename, json_encode($users, JSON_PRETTY_PRINT));

    $_SESSION['success'] = 'Cuenta creada correctamente. Ahora puedes iniciar sesión.';
    header('Location: login.php');
    exit;
}

// Mostrar mensajes
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - LendFind</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="ai-header">
                <h1>Registro</h1>
                <p>Crea tu cuenta en LendFind</p>
            </div>

            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif ($success): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" required placeholder="Elige un nombre de usuario">
                </div>

                <div class="form-group">
                    <label for="email">Correo</label>
                    <input type="email" id="email" name="email" required placeholder="tu@email.com">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="Mínimo 6 caracteres">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repite tu contraseña">
                </div>

                <button type="submit" class="login-btn">
                    <span>Crear Cuenta</span>
                </button>

                <div class="register-link">
                    ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
