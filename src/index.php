<?php

session_start();

require_once __DIR__ . '/vendor/autoload.php';

$uri = "mongodb+srv://mariobg_db_user:<db_password>@cluster0.q9wfhgy.mongodb.net/";

try {
  $conexion = new MongoDB\Client($uri);
  $bd = $conexion->selectDatabase("monster_shop");
  $usuarios = $bd->selectCollection("usuarios");

  $usuarios->createIndex(
    ['email' => 1],
    ['unique' => true]
  );

} catch (Throwable $e) {
  die("Error al conectar con la base de datos.");
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $accion = $_POST["accion"] ?? "";

  if ($accion == "registro") {

    $nombre = trim($_POST["nombre"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";

    if (empty($nombre) || empty($email) || empty($password)) {

      $mensaje = "Debes rellenar todos los campos.";

    } elseif (strlen($nombre) < 2) {

      $mensaje = "El nombre debe tener al menos 2 caracteres.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

      $mensaje = "El correo no es valido.";

    } elseif (strlen($password) < 8 || strlen($password) > 72) {

      $mensaje = "La contraseña debe tener entre 8 y 72 caracteres.";

    } else {

      try {

        $existe = $usuarios->findOne([
          "email" => $email
        ]);

        if ($existe) {

          $mensaje = "Este correo ya esta registrado.";

        } else {

          $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
          );

          $usuarios->insertOne([
            "nombre" => $nombre,
            "email" => $email,
            "password" => $passwordHash,
            "rol" => "comprador"
          ]);

          $mensaje = "Registro realizado correctamente.";

        }

      } catch (Throwable $e) {

        $mensaje = "No se ha podido realizar el registro.";

      }
    }
  }

  if ($accion == "login") {

    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {

      $mensaje = "Debes rellenar todos los campos.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

      $mensaje = "El correo no es valido.";

    } else {

      try {

        $usuario = $usuarios->findOne([
          "email" => $email
        ]);

        if ($usuario && password_verify($password, $usuario->password)) {

          session_regenerate_id(true);

          $_SESSION["usuario_id"] = (string) $usuario->_id;
          $_SESSION["nombre"] = $usuario->nombre;
          $_SESSION["email"] = $usuario->email;
          $_SESSION["rol"] = $usuario->rol;

          header("Location: paginas/tienda.php");
          exit;

        } else {

          $mensaje = "Correo o contraseña incorrectos.";

        }

      } catch (Throwable $e) {

        $mensaje = "Error al iniciar sesion.";

      }
    }
  }
}

?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Monster Shop - Inicio</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="estilos/style.css" />
</head>

<body>
  <header class="bg-dark text-white p-3">
    <div class="container d-flex justify-content-between align-items-center">
      <h2 class="mb-0">Monster Shop</h2>

      <div>
        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#login">
          Iniciar sesión
        </button>

        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#registro">
          Registrarse
        </button>
      </div>
    </div>
  </header>

  <main class="container mt-5">
    <div class="text-center p-5 bg-light rounded">
      <h1>Bienvenido a Monster Shop</h1>

      <p class="mt-3">Esta es una tienda online de bebidas Monster.</p>

      <p>
        Aquí puedes consultar los diferentes productos disponibles y conocer
        sus características.
      </p>

      <a href="paginas/tienda.html" class="btn btn-success mt-3">
        Ver productos
      </a>
    </div>

    <div class="text-center mt-5">
      <a href="paginas/contacto.html" class="btn btn-secondary">
        Contactar
      </a>
    </div>
  </main>

  <div class="modal fade" id="login">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Iniciar sesión</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <form>
            <label class="form-label">Correo electrónico</label>
            <input type="email" class="form-control mb-3" required />

            <label class="form-label">Contraseña</label>
            <input type="password" class="form-control mb-3" required />

            <button type="submit" class="btn btn-success">Entrar</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="registro">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Registrarse</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <form>
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control mb-3" required />

            <label class="form-label">Correo electrónico</label>
            <input type="email" class="form-control mb-3" required />

            <label class="form-label">Contraseña</label>
            <input type="password" class="form-control mb-3" required />

            <button type="submit" class="btn btn-success">
              Crear cuenta
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>