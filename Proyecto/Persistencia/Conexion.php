<?php
class Conexion{
    
    private $nombreServidor = "db";
    private $nombreUsuario = "Admin";
    private $contraseña = "126658"; // Tu contraseña
    private $bd = "institutobd";// Tu base de datos (coincide con el SQL proporcionado)
    protected $con; // Protegida para herencia

    public function Conectar()
    {
        $this->con = new mysqli(
            $this->nombreServidor,
            $this->nombreUsuario,
            $this->contraseña,
            $this->bd
        );

        if ($this->con->connect_error) {
            die("Error al conectarse: " . $this->con->connect_error);
        }

        // ¡IMPORTANTE! Establecer charset para tildes y ñ
        $this->con->set_charset("utf8mb4");

        return $this->con;
    }

    public function Desconectar()
    {
        if ($this->con) {
            $this->con->close();
        }
    }
}