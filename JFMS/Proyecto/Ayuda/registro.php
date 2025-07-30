<?php
// Configuración de la base de datos
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'bd_virtualyect';
$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    die('Error al conectar con la base de datos: ' . mysqli_connect_error());
}

// Obtener datos del formulario
$nombre = $_POST['Pnom'];
$FechaNac = $_POST['FechaNac'];
$email = $_POST['Email'];
$pass = $_POST['Contraseña'];
$imagen = null;

// Validación mínima
if (empty($nombre) || empty($FechaNac) || empty($email) || empty($pass)) {
    die('Por favor, completa todos los campos obligatorios.');
}

// Hashear la contraseña
$passHash = password_hash($pass, PASSWORD_DEFAULT);

// Manejo del archivo de imagen si se subió
if (!empty($_FILES['imagen']['name'])) {
    $nombreImagen = basename($_FILES['imagen']['name']);
    $rutaDestino = 'uploads/' . $nombreImagen;

    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
        $imagen = $nombreImagen;
    } else {
        die('Error al subir la imagen.');
    }
}

// Crear query SQL
$query = "INSERT INTO Persona (Pnom, FechaNac, Email, Contraseña , imagen) 
          VALUES (?, ?, ?, ?, ?)";

// Preparar y ejecutar
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "sisss", $nombre, $edad, $email, $passHash, $imagen);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    echo "Usuario registrado con éxito.";
} else {
    echo "Error al registrar usuario: " . mysqli_error($conn);
}

// Cerrar conexión
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
