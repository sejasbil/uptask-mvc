<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {
    public static function login(Router $router) {
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);

            $alertas = $usuario->validarLogin();

            if (empty($alertas)) {
                // Verificar que el usuario exista
                $usuario = Usuario::where('email', $usuario->email); 

                if (!$usuario || !$usuario->confirmado) {
                    Usuario::setAlerta('error', 'El Usuario no Existe o no esta confirmado');
                } else {
                    // El usuario existe
                    if (password_verify($_POST['password'], $usuario->password)) {
                        // Iniciar la sesión

                        session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        // Redireccionar 
                        header('Location: /dashboard');
                    } else {
                        Usuario::setAlerta('error', 'Password Incorrecto');
                    }
                }
            }
        }
        $alertas = Usuario::getAlertas();

        // Render a la vista
        $router->render('auth/login', [
            'titulo' => 'Iniciar Sesión',
            'alertas' => $alertas
        ]);
    }

    public static function logout() {
        session_start();
        $_SESSION = [];

        header('Location: /');

    }

    public static function crear(Router $router) {
        // arreglo vacío para limpiar
        $alertas = [];
        // Instanciar el usuario
        $usuario = new Usuario;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Asignamos lo digitado con la instancia
            $usuario->sincronizar($_POST);
            // Llamado a las alertas con las validaciones
            $alertas = $usuario->validarNuevaCuenta();

            // Si alertas está vacío: 
            if (empty($alertas)) {
                // Revisar si el usuario existe
                $existeUsuario = Usuario::where('email', $usuario->email);
                
                // Si el usuario existe:
                if ($existeUsuario) {
                    Usuario::setAlerta('error', 'El Usuario ya esta registrado');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Hashear el password
                    $usuario->hashPassword();
                    // ELiminar passsword2
                    unset($usuario->password2);
                    // Generar el Token
                    $usuario->crearToken();
                    // Crear un nuevo usuario
                    $resultado = $usuario->guardar();
                    // Enviar el email
                    $email = new Email($usuario->nombre, $usuario->email, $usuario->token);
                    $email->enviarConfirmacion();

                    if ($resultado) {
                        header('Location: /mensaje');
                    }
                }
            }
        }
        // Render a la vista
        $router->render('auth/crear', [
            'titulo' => 'Crea tu cuenta en UpTask',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function olvide(Router $router) {
        // arreglo vacío de alertas
        $alertas = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Crear una nueva instacia
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if (empty($alertas)) {
                // Buscar el usuario
                $usuario = Usuario::where('email', $usuario->email);

                if ($usuario && $usuario->confirmado) {
                    // Encontré el usuario
                    // Generar un nuevo token 
                    $usuario->crearToken();
                    unset($usuario->password2);
                    // Actualizar el usuario
                    $usuario->guardar();
                    // Enviar el email
                    $email = new Email($usuario->nombre, $usuario->email, $usuario->token);
                    $email->enviarInstrucciones();
                    // Imprimir la alerta
                    Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');
                } else {
                    Usuario::setAlerta('error', 'El Usuario no existe o no esta confirmado');
                }
            }
        }
        $alertas = Usuario::getAlertas();

        // Render a la vista
        $router->render('auth/olvide', [
            'titulo' => 'Olvide mi Password',
            'alertas' => $alertas
        ]);
    }

    public static function reestablecer(Router $router) {
        $token = s($_GET['token']);
        $mostrar = true;

        if (!$token) header('Location: /');

        // Identificar el usuario con este token
        $usuario = Usuario::where('token', $token);

        if (empty($usuario)) {
            Usuario::setAlerta('error', 'Token No Válido');
            $mostrar = false;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Añadir el nuevo password
            $usuario->sincronizar($_POST);
            // Validar el password
            $alertas = $usuario->validarPassword();

            if (empty($alertas)) {
                // Hashear el nuevo password
                $usuario->hashPassword();
                // Eliminar el Token
                $usuario->token = null;
                // Guardar el usuario en la BD
                $resultado = $usuario->guardar();
                // Redireccionar
                if ($resultado) {
                    header('Location: /');
                }
                debuguear($usuario);
            }
        }
        $alertas = Usuario::getAlertas();

        // Render a la vista
        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer Password',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);
    }

    public static function mensaje(Router $router) {
        // Render a la vista
        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta Creada Existosamente'
        ]);
    }

    public static function confirmar(Router $router) {
        // Agarramos el token de la url
        $token = s($_GET['token']);

        // Si no existe el token
        if (!$token) header('Location: /');

        // Encontrar al usuario con este token
        $usuario = Usuario::where('token', $token);

        
        if (empty($usuario)) {
            // No existe un usuario
            Usuario::setAlerta('error', 'Token No Válido');
        } else {
            // Confirmar la cuenta
            $usuario->confirmado = 1;
            $usuario->token = null;
            unset($usuario->password2);
            
            // Guardar en la base de datos
            $usuario->guardar();

            Usuario::setAlerta('exito', 'Cuenta Comprobada Correctamente');
        }

        // Llamar a las alertas
        $alertas = Usuario::getAlertas();

        // Render a la vista
        $router->render('auth/confirmar', [
            'titulo' => 'Confirma tu cuenta UpTask',
            'alertas' => $alertas
        ]);
    }
}