<?php
    session_start();
    if(isset($_SESSION['Pnom'])){
        header('location: ../index.php');
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login de Usuario</title>
    <link rel="stylesheet" href="styles.css"> 
</head>
<body>
    <div class="container">
        <h2>Login de usuario</h2>
        <form action="login_back.php" method="POST">
            <label for="Pnom">Nombre:</label>
            <input type="text" name="Pnom" id="Pnom" required>

            <label for="Contraseña">Contraseña:</label>
            <input type="password" name="Contraseña" id="Contraseña" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
