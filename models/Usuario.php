<?php

namespace Model;

class Usuario extends ActiveRecord {
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre','email', 'password', 'token', 'confirmado'];

    public $id;
    public $nombre;
    public $email;
    public $password;
    public $password2; // es una variable temporal, nunca la registramos a la base de datos
    public $password_actual;
    public $password_nuevo;
    public $token;
    public $confirmado;

    public function __construct( $args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? '';
        $this->password_actual = $args['password_actual'] ?? '';
        $this->password_nuevo = $args['password_nuevo'] ?? '';
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
    }

    // Validar login de usuarios
    public function validarLogin() : array {
        if(!$this->email) {
            self::$alertas['error'][] = 'El Email del Usuario es Obligatorio';
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no Válido';
        }
        if(!$this->password) {
            self::$alertas['error'][] = 'El Password no puede ir vacío';
        }
        return self::$alertas;
    }

    // Validación para un nuevo usuario
    public function validarNuevaCuenta() : array {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El Nombre del Usuario es Obligatorio';
        }
        if(!$this->email) {
            self::$alertas['error'][] = 'El Email del Usuario es Obligatorio';
        }
        if(!$this->password) {
            self::$alertas['error'][] = 'El Password no puede ir vacío';
        }
        if( strlen( $this->password ) < 6 ) {
            self::$alertas['error'][] = 'El Password debe contener al menos 6 caracteres';
        }
        if ($this->password !== $this->password2) {
            self::$alertas['error'][] = 'Los Password son diferentes';
        }
        return self::$alertas;
    }

    // Hashear password
    public function hashPassword() : void {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    // Generar un Token
    public function crearToken() : void {
        $this->token = uniqid();        
    }

    public function validarEmail() : array {
        if (!$this->email) {
            self::$alertas['error'][] = 'El Email es Obligatorio';
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no Válido';
        }
        return self::$alertas;
    }

    public function validarPassword() : array {
        if(!$this->password) {
            self::$alertas['error'][] = 'El Password no puede ir vacío';
        }
        if( strlen( $this->password ) < 6 ) {
            self::$alertas['error'][] = 'El Password debe contener al menos 6 caracteres';
        }
        return self::$alertas;
    }

    public function validarPerfil() : array {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El Nombre del Usuario es Obligatorio';
        }
        if (!$this->email) {
            self::$alertas['error'][] = 'El Email es Obligatorio';
        }
        return self::$alertas;
    }

    public function nuevoPassword() : array {
        if (!$this->password_actual) {
            self::$alertas['error'][] = 'El Password Actual no puede ir vacío';
        }
        if (!$this->password_nuevo) {
            self::$alertas['error'][] = 'El Password Nuevo no puede ir vacío';
        }
        if( strlen( $this->password_nuevo ) < 6 ) {
            self::$alertas['error'][] = 'El Password debe contener al menos 6 caracteres';
        }
        return self::$alertas;
    }

    public function comprobarPassword() : bool {
        return password_verify($this->password_actual, $this->password);
    }
}