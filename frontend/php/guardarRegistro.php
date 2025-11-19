<?php
include("conexion.php");

// Verificar conexión
if (!$con) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Recibir datos
$usu = mysqli_real_escape_string($con, trim($_POST['username']));
$correo = mysqli_real_escape_string($con, trim($_POST['email']));
$contra = $_POST['password'];
$contra2 = $_POST['confirm_password'];
$fecha_actual = date('Y-m-d'); // Mejor con hora

// Validaciones
if(empty($usu) || empty($correo) || empty($contra) || empty($contra2)) {
    header("Location: register.php?error=Debes de llenar todos los campos");
    exit();
}

if($contra != $contra2) {
    header("Location: register.php?error=Las contraseñas no coinciden");
    exit();
}

// Verificar si usuario existe
$sql_check = "SELECT id_Usuario FROM usuario WHERE Nombre = '$usu' OR Correo = '$correo'";
$resultado = mysqli_query($con, $sql_check);

if(!$resultado) {
    header("Location: register.php?error=Error en la consulta: " . urlencode(mysqli_error($con)));
    exit();
}

if(mysqli_num_rows($resultado) > 0) {
    header("Location: register.php?error=El usuario o email ya existe");
    exit();
}



// INSERT con nombres de columnas explícitos (MÁS SEGURO)
$sql_insert = "INSERT INTO usuario 
               (Rol, Fecha, Nombre, Correo, Contraseña, Estado) 
               VALUES 
               (1, '$fecha_actual', '$usu', '$correo', '$contra', 'activo')";

// Debug
error_log("INSERT SQL: " . $sql_insert);

if(mysqli_query($con, $sql_insert)) {
    // Éxito
    $id_nuevo = mysqli_insert_id($con);
    error_log("USUARIO REGISTRADO - ID: $id_nuevo");
    header("Location: login.php?success=Usuario registrado correctamente");
} else {
    // Error detallado
    $error_mysql = mysqli_error($con);
    error_log("ERROR MySQL: " . $error_mysql);
    header("Location: register.php?error=Error en base de datos: " . urlencode($error_mysql));
}

mysqli_close($con);
?>