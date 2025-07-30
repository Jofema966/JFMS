<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Registro</title>
    <link rel="stylesheet" href="styles.css"> 
</head>
<body>
    <div class="form-container">
        <h2>Formulario de Registro</h2>
        <form action="registro.php" method="POST" enctype="multipart/form-data">
            <label for="Pnom">Nombre:</label>
            <input type="text" name="Pnom" id="Pnom" required>

            <label for="FechNac">Edad:</label>
            <input type="number" name="FechNac" id="FechNac" required>

            <label for="Email">Email:</label>
            <input type="email" name="Email" id="Email" required>

            <label for="Contraseña">Contraseña:</label>
            <input type="password" name="Contraseña" id="Contraseña" required>

            <label for="imagen">Imagen (opcional):</label>
            <input type="file" name="imagen" id="imagen">

            <input type="submit" value="Registrarse">
        </form>
    </div>
</body>
</html>
