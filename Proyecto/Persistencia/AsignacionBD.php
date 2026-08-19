<?php
    include_once 'Conexion.php';

    class AsignacionBD extends Conexion {

        public function Guardar($id_curso, $id_docente, $id_materia) {
            $this->Conectar();

            $sql = "INSERT INTO Asignacion_Materia (ID_C, ID_D, ID_M) VALUES (?, ?, ?)";
            $stmt = $this->con->prepare($sql);
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $this->con->error);
            }

            $stmt->bind_param('iii', $id_curso, $id_docente, $id_materia);
            $resultado = $stmt->execute();

            if (!$resultado) {
                throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
            }

            return $resultado;
        }

        public function Listar() {
            $this->Conectar();

            $sql = "SELECT * FROM Asignacion_Materia";
            $result = $this->con->query($sql);

            if (!$result) {
                throw new Exception('Error al ejecutar la consulta: ' . $this->con->error);
            }

            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }