<?php
    class Usuario {
        private $nombre;
        private $apellido;
        private $correo;
        private $contrasena;
        private $activo;
        private $rol;

        public function GetNombre() {
            return $this->nombre;
        }

        public function SetNombre($nombre) {
            $this->nombre = $nombre;
        }

        public function GetApellido() {
            return $this->apellido;
        }

        public function SetApellido($apellido) {
            $this->apellido = $apellido;
        }

        public function GetCorreo() {
            return $this->correo;
        }

        public function SetCorreo($correo) {
            $this->correo = $correo;
        }

        public function GetContrasena() {
            return $this->contrasena;
        }

        public function SetContrasena($contrasena) {
            $this->contrasena = $contrasena;
        }

        public function GetActivo() {
            return $this->activo;
        }

        public function SetActivo($activo) {
            $this->activo = $activo;
        }

        public function GetRol() {
            return $this->rol;
        }

        public function SetRol($rol) {
            $this->rol = $rol;
        }

        public function AgregarUsuario() {
            include_once __DIR__ . "/../Persistencia/UsuarioBD.php";
            $errores = $this->ValidarDatos();
            if ($errores) {
                throw new InvalidArgumentException(implode(' ', $errores));
            }

            $usuarioBD = new UsuarioBD();
            $usuarioBD->Agregar(
                trim($this->nombre),
                trim($this->apellido),
                strtolower(trim($this->correo)),
                password_hash($this->contrasena, PASSWORD_DEFAULT),
                $this->activo,
                $this->rol
            );
        }

        public function ValidarDatos() {
            $errores = [];
            $nombre = trim((string) $this->nombre);
            $apellido = trim((string) $this->apellido);
            $correo = trim((string) $this->correo);
            $contrasena = (string) $this->contrasena;

            if (!preg_match("/^[\\p{L}][\\p{L} '\\-]{1,49}$/u", $nombre)) {
                $errores[] = 'El nombre debe tener entre 2 y 50 caracteres válidos.';
            }
            if (!preg_match("/^[\\p{L}][\\p{L} '\\-]{1,49}$/u", $apellido)) {
                $errores[] = 'El apellido debe tener entre 2 y 50 caracteres válidos.';
            }
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 254) {
                $errores[] = 'El correo electrónico no es válido.';
            }
            if (strlen($contrasena) < 8 || strlen($contrasena) > 72 ||
                !preg_match('/[A-Za-z]/', $contrasena) || !preg_match('/\\d/', $contrasena)) {
                $errores[] = 'La contraseña debe tener entre 8 y 72 caracteres, al menos una letra y un número.';
            }
            if (!in_array($this->rol, ['Admin', 'Director', 'Secretaria', 'Docente', 'Usuario'], true)) {
                $errores[] = 'El rol seleccionado no es válido.';
            }
            return $errores;
        }

        public static function Autenticar($correo, $contrasena) {
            include_once __DIR__ . "/../Persistencia/UsuarioBD.php";
            $correo = strtolower(trim((string) $correo));
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || $contrasena === '') {
                return null;
            }

            $usuarioBD = new UsuarioBD();
            $usuario = $usuarioBD->BuscarPorCorreo($correo);
            if (!$usuario || !(bool) $usuario['Activo']) {
                return null;
            }

            $guardada = (string) $usuario['Contraseña'];
            $esHash = password_get_info($guardada)['algo'] !== null;
            $valida = $esHash ? password_verify($contrasena, $guardada) : hash_equals($guardada, $contrasena);
            if (!$valida) {
                return null;
            }

            // Migra de forma transparente las contraseñas antiguas guardadas en texto plano.
            if (!$esHash || password_needs_rehash($guardada, PASSWORD_DEFAULT)) {
                $usuarioBD->ActualizarContrasena((int) $usuario['ID_U'], password_hash($contrasena, PASSWORD_DEFAULT));
            }
            unset($usuario['Contraseña']);
            return $usuario;
        }

        public function ListarUsuarios() {
            include_once __DIR__ . "/../Persistencia/UsuarioBD.php";
            $usuarioBD = new UsuarioBD();
            return $usuarioBD->Listar();
        }

        public static function CambiarRol($id, $rol) {
            include_once __DIR__ . "/../Persistencia/UsuarioBD.php";
            $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                throw new InvalidArgumentException('El usuario seleccionado no es válido.');
            }
            if (!in_array($rol, ['Admin', 'Director', 'Secretaria', 'Docente', 'Usuario'], true)) {
                throw new InvalidArgumentException('El rol seleccionado no es válido.');
            }

            $usuarioBD = new UsuarioBD();
            $usuarioBD->ActualizarRol($id, $rol);
        }

    }
