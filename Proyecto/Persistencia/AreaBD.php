<?php
class AreaBD {
    private $con;

    public function __construct() {
        include_once 'Conexion.php';
        $conexion = new Conexion();
        $this->con = $conexion->Conectar();
    }

    public function Agregar($nombre) {
        $sql = "INSERT INTO Area (Nombre) VALUES (?)";
        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $this->con->error);
        }
        $stmt->bind_param("s", $nombre);
        if (!$stmt->execute()) {
            die("Error al ejecutar la consulta: " . $stmt->error);
        }
        $stmt->close();
    }

    public function Listar() {
        $sql = "SELECT ID_A, Nombre FROM Area";
        $result = $this->con->query($sql);
        if (!$result) {
            die("Error al ejecutar la consulta: " . $this->con->error);
        }
        return $result;
    }
}
?>