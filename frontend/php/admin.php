<?php
session_start();
// Evitar cache del navegador (para que no se pueda usar el botón "atrás")
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Verificar si el usuario tiene sesión
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Tiempo máximo de inactividad (5 minutos)
$inactividad = 300;

if (isset($_SESSION['ultimo_acceso'])) {
    $tiempo = time() - $_SESSION['ultimo_acceso'];
    if ($tiempo > $inactividad) {
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit;
    }
}
$_SESSION['ultimo_acceso'] = time();

// Verificación de sesión y rol
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Ruta del archivo de usuarios
$filename = __DIR__ . '/backend/users.json';
if (!file_exists($filename)) {
    file_put_contents($filename, json_encode([]));
}
$users = json_decode(file_get_contents($filename), true) ?? [];

// Agregar usuario
if (isset($_POST['add_user'])) {
    $newUser = [
        'id' => count($users) ? max(array_column($users, 'id')) + 1 : 1,
        'username' => trim($_POST['username']),
        'email' => trim($_POST['email']),
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'rol' => $_POST['rol'],
        'created_at' => date('Y-m-d H:i:s'),
      ];
    $users[] = $newUser;
    file_put_contents($filename, json_encode($users, JSON_PRETTY_PRINT));
    $_SESSION['success'] = 'Usuario agregado correctamente.';
    header('Location: admin.php');
    exit;
}

// Eliminar usuario
if (isset($_GET['delete'])) {
    $idToDelete = intval($_GET['delete']);
    $users = array_filter($users, fn($u) => $u['id'] !== $idToDelete);
    file_put_contents($filename, json_encode(array_values($users), JSON_PRETTY_PRINT));
    $_SESSION['success'] = 'Usuario eliminado.';
    header('Location: admin.php');
    exit;
}

// Cambiar rol
if (isset($_GET['toggle_role'])) {
    $id = intval($_GET['toggle_role']);
    foreach ($users as &$u) {
        if ($u['id'] === $id) {
            $u['rol'] = ($u['rol'] === 'admin') ? 'usuario' : 'admin';
        }
    }
    file_put_contents($filename, json_encode($users, JSON_PRETTY_PRINT));
    $_SESSION['success'] = 'Rol actualizado correctamente.';
    header('Location: admin.php');
    exit;
}

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - LendFind</title>
    <style>
        
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #e2e8f0;
            margin: 0;
            padding: 0;
        }
        header {
            background: #334155;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 {
            margin: 0;
            font-size: 1.5rem;
            color: #38bdf8;
        }
        header a {
            color: #f87171;
            text-decoration: none;
        }
        main {
            padding: 20px 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #1e293b;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #334155;
            text-align: left;
        }
        th {
            background: #0f172a;
        }
        tr:hover {
            background: #2d3748;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-delete { background: #ef4444; }
        .btn-role { background: #3b82f6; }
        .btn-add { background: #10b981; margin-top: 10px; display: inline-block; }
        form.add-user {
            margin-top: 30px;
            background: #1e293b;
            padding: 20px;
            border-radius: 8px;
            width: 100%;
            max-width: 500px;
        }
        form.add-user input, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            border: none;
            border-radius: 4px;
            background: #334155;
            color: #e2e8f0;
        }
        form.add-user button {
            background: #10b981;
            border: none;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            font-weight: bold;
            width: 100%;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
            background: #0f766e;
            color: white;
        }
    </style>
</head>

<body>
    <header>
        <h1>Panel de Administración</h1>
        <a href="logout.php">Cerrar sesión</a>
    </header>

    <main>
        <?php if ($success): ?>
            <div class="message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <h2>Usuarios registrados</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['id']) ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['rol']) ?></td>
                        <td>
                            <a class="btn btn-role" href="?toggle_role=<?= $u['id'] ?>">Cambiar Rol</a>
                            <a class="btn btn-delete" href="?delete=<?= $u['id'] ?>" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Agregar nuevo usuario</h2>
        <form class="add-user" method="POST">
            <input type="text" name="username" placeholder="Nombre de usuario" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <select name="rol" required>
                <option value="usuario">Usuario</option>
                <option value="admin">Administrador</option>
            </select>
            <button type="submit" name="add_user">Agregar Usuario</button>
        </form>
    </main>
    <script>
// Si el usuario intenta usar el botón "Atrás"
window.history.pushState(null, "", window.location.href);
window.onpopstate = function () {
  window.history.pushState(null, "", window.location.href);
  window.location.href = "logout.php"; // Cierra sesión si intenta volver
};

// Cerrar sesión si cierra la pestaña
window.addEventListener("beforeunload", function () {
  navigator.sendBeacon("logout.php");
});
</script>

</body>
</html>
