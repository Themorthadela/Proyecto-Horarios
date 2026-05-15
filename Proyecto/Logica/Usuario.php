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
            include_once 'Persistencia/UsuarioBD.php';
            $usuarioBD = new UsuarioBD();
            $usuarioBD->Agregar(
                $this->nombre,
                $this->apellido,
                $this->correo,
                $this->contrasena,
                $this->activo,
                $this->rol
            );
        }

        public function ListarUsuarios() {
            include_once 'Persistencia/UsuarioBD.php';
            $usuarioBD = new UsuarioBD();
            return $usuarioBD->Listar();
        }

    }