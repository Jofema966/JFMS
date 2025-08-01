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
            <label for="usr_name">Nombre:</label>
            <input type="text" name="usr_name" id="usr_name" required>

            <label for="usr_age">Edad:</label>
            <input type="number" name="usr_age" id="usr_age" required>

            <label for="usr_email">Email:</label>
            <input type="email" name="usr_email" id="usr_email" required>

            <label for="usr_pass">Contraseña:</label>
            <input type="password" name="usr_pass" id="usr_pass" required>

            <label for="imagen">Imagen (opcional):</label>
            <input type="file" name="imagen" id="imagen">

            <input type="submit" value="Registrarse">
        </form>
    </div>
</body>
</html>
