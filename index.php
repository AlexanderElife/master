<?php

require 'flight/Flight.php';

Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=universidad', 'root', '0503d_'));

// SELECT de profesores
Flight::route('GET /SELECTprofesores', function () {
    $sql = "SELECT id,nombre,apellido FROM usuarios WHERE tipo_usuario='profesor' AND activo = '1'";
    $resultados = Flight::db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    Flight::json($resultados);
});

// SELECT de estudiantes
Flight::route('GET /SELECTestudiantes', function () {
    $sql = "SELECT id,nombre,apellido FROM usuarios WHERE tipo_usuario='estudiante' AND activo = '1'";
    $resultados = Flight::db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    Flight::json($resultados);
});

// SELECT de usuarios
Flight::route('GET /SELECTusuarios', function () {
    $sql = "SELECT * FROM usuarios";
    $resultados = Flight::db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    Flight::json($resultados);
});

Flight::route('GET /SELECTusuariosCorreo/@correo_electronico', function ($correo_electronico) {
    $sql = "SELECT * FROM usuarios WHERE correo_electronico=?";
    $sentencia = Flight::db()->prepare($sql);
    $sentencia->bindParam(1, $correo_electronico, PDO::PARAM_STR);
    $sentencia->execute();
    $resultados = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    Flight::json($resultados);
});

// SELECT de materias
Flight::route('GET /SELECTmaterias', function () {
    $sql = "SELECT * FROM materias";
    $resultados = Flight::db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    Flight::json($resultados);
});

// SELECT de semestres
Flight::route('GET /SELECTsemestres', function () {
    $sql = "SELECT id_semestre, nombre_semestre FROM semestres";
    $resultados = Flight::db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    Flight::json($resultados);
});

Flight::route('GET /SELECTparalelos', function () {
    $sql = "SELECT id_paralelo,nombre_paralelo FROM paralelos";
    $resultados = Flight::db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    Flight::json($resultados);
});
// url = "localhost/apirepaso/insertarUsuario"
Flight::route('POST /INSERTUsuario', function () {

    $nombre = (Flight::request()->data->nombre);
    $apellido = (Flight::request()->data->apellido);
    $cedula = (Flight::request()->data->cedula);
    $correo = (Flight::request()->data->correo);
    $contrasenia = (Flight::request()->data->contrasenia);
    $tipo_usuario = (Flight::request()->data->tipo_usuario);
    $hash_correo = (Flight::request()->data->hash_correo);

    $sql = "INSERT INTO usuarios(nombre,apellido,cedula,correo_electronico,contrasenia,tipo_usuario,hash_correo) VALUES(?,?,?,?,?,?,?)"; //cambiar todo esto

    //hacer bind param para todos los datos de la base de datos
    $sentencia = Flight::db()->prepare($sql);
    $sentencia->bindParam(1, $nombre);
    $sentencia->bindParam(2, $apellido);
    $sentencia->bindParam(3, $cedula);
    $sentencia->bindParam(4, $correo);
    $sentencia->bindParam(5, $contrasenia);
    $sentencia->bindParam(6, $tipo_usuario);
    $sentencia->bindParam(7, $hash_correo);

    if ($sentencia->execute()) {
        Flight::json(["1"]);
    } else {
        Flight::json(["0"]);
    }
});

Flight::route('POST /login', function () {
    $correo = Flight::request()->data->correo;
    $contrasenia = Flight::request()->data->contrasenia;

    $sql = "SELECT id, correo_electronico, contrasenia, activo, tipo_usuario
            FROM Usuarios
            WHERE correo_electronico = ? AND contrasenia = ? AND activo = 1";

    $sentencia = Flight::db()->prepare($sql);
    $sentencia->bindParam(1, $correo);
    $sentencia->bindParam(2, $contrasenia);

    $sentencia->execute();
    $usuario = $sentencia->fetch(PDO::FETCH_ASSOC);

    Flight::json($usuario);
});

Flight::route('GET /SELECTclases', function () {
    $sql = "SELECT ma.*, m.nombre_materia, p.nombre_paralelo, s.nombre_semestre, CONCAT(u.nombre, ' ', u.apellido) AS nombre_profesor
            FROM master_all ma
            JOIN materias m ON ma.id_materia = m.id_materia
            JOIN paralelos p ON ma.id_paralelo = p.id_paralelo
            JOIN semestres s ON ma.id_semestre = s.id_semestre
            JOIN profesores pr ON ma.id_profesor = pr.id_profesor
            JOIN usuarios u ON pr.id_usuario = u.id";

    $resultados = Flight::db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    Flight::json($resultados);
});

Flight::route('GET /SELECTclases/@id', function ($id) {
    $sql = "SELECT ma.*, m.nombre_materia, p.nombre_paralelo, s.nombre_semestre, CONCAT(u.nombre, ' ', u.apellido) AS nombre_profesor
            FROM master_all ma
            JOIN materias m ON ma.id_materia = m.id_materia
            JOIN paralelos p ON ma.id_paralelo = p.id_paralelo
            JOIN semestres s ON ma.id_semestre = s.id_semestre
            JOIN profesores pr ON ma.id_profesor = pr.id_profesor
            JOIN usuarios u ON pr.id_usuario = u.id
            JOIN matriculas mat ON ma.id_master = mat.id_master
            WHERE mat.id_estudiante = ?;
            ";
    $sentencia = Flight::db()->prepare($sql);
    $sentencia->bindParam(1, $id, PDO::PARAM_INT);
    $sentencia->execute();
    $resultados = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    Flight::json($resultados);
});

Flight::route('GET /SELECTclases_by_profesor/@id_profesor', function ($idProfesor) {
    $sql = "SELECT ma.*, m.nombre_materia, p.nombre_paralelo, s.nombre_semestre, CONCAT(u.nombre, ' ', u.apellido) AS nombre_profesor
    FROM master_all ma
    JOIN materias m ON ma.id_materia = m.id_materia
    JOIN paralelos p ON ma.id_paralelo = p.id_paralelo
    JOIN semestres s ON ma.id_semestre = s.id_semestre
    JOIN profesores pr ON ma.id_profesor = pr.id_profesor
    JOIN usuarios u ON pr.id_usuario = u.id
    WHERE ma.id_profesor = ?";

    $sentencia = Flight::db()->prepare($sql);
    $sentencia->bindParam(1, $idProfesor, PDO::PARAM_INT);
    $sentencia->execute();
    $resultados = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    Flight::json($resultados);
});

Flight::route('GET /verificar_correo', function () {
    $correoElectronico = Flight::request()->data->correo_electronico;
    $hashCorreo = Flight::request()->data->hash_correo;

    $sql = "SELECT correo_electronico, hash_correo, activo FROM usuarios WHERE correo_electronico=? AND hash_correo=? AND activo='0'";

    $stmt = Flight::db()->prepare($sql);
    $stmt->bindParam(1, $correoElectronico);
    $stmt->bindParam(2, $hashCorreo);

    $stmt->execute();

    $resultado = $stmt->rowCount();

    Flight::json($resultado);
});

Flight::route('PUT /activar_cuenta', function () {
    $correoElectronico = Flight::request()->data->correo_electronico;
    $hashCorreo = Flight::request()->data->hash_correo;

    $sqlUpdate = "UPDATE usuarios SET activo='1' WHERE correo_electronico=? AND hash_correo=? AND activo='0'";

    $stmtUpdate = Flight::db()->prepare($sqlUpdate);
    $stmtUpdate->bindParam(1, $correoElectronico);
    $stmtUpdate->bindParam(2, $hashCorreo);

    $resultado = $stmtUpdate->execute();

    Flight::json([$resultado]);
});

Flight::route('GET /get_password/@correo_electronico', function ($correo_electronico) {

    $sql = "SELECT contrasenia FROM usuarios WHERE correo_electronico=?";
    $sentencia = Flight::db()->prepare($sql);
    $sentencia->bindParam(1, $correo_electronico, PDO::PARAM_STR);
    $sentencia->execute();
    $resultados = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    Flight::json($resultados);
});

Flight::route('POST /INSERTsemestre', function () {
    $nombreSemestre = Flight::request()->data->nombre_semestre;

    $sql = "INSERT INTO semestres (nombre_semestre) VALUES (?)";

    $sentencia = Flight::db()->prepare($sql);
    $sentencia->bindParam(1, $nombreSemestre);

    $sentencia->execute();

    $resultado = Flight::db()->lastInsertId();
    Flight::json([$resultado]);
});
Flight::route('POST /INSERTparalelo', function () {
    $nombreParalelo = Flight::request()->data->nombre_paralelo;

    $sql = "INSERT INTO paralelos (nombre_paralelo) VALUES (?)";

    $sentencia = Flight::db()->prepare($sql);

    $sentencia->bindParam(1, $nombreParalelo);

    $sentencia->execute();

    $resultado = Flight::db()->lastInsertId();
    Flight::json([$resultado]);
});
Flight::route('POST /INSERTmateria', function () {
    $nombreMateria = Flight::request()->data->nombre_materia;

    $sql = "INSERT INTO materias (nombre_materia) VALUES (?)";

    $sentencia = Flight::db()->prepare($sql);

    $sentencia->bindParam(1, $nombreMateria);

    $sentencia->execute();

    $resultado = Flight::db()->lastInsertId();
    Flight::json([$resultado]);
});
// SELECT de semestres
Flight::route('POST /SELECTsemestre', function () {
    $nombreSemestre = Flight::request()->data->nombre_semestre;
    $sqlGetSemestreId = "SELECT id_semestre FROM semestres WHERE nombre_semestre = ?";
    $stmtGetSemestreId = Flight::db()->prepare($sqlGetSemestreId);
    $stmtGetSemestreId->bindParam(1, $nombreSemestre);
    $stmtGetSemestreId->execute();
    $row = $stmtGetSemestreId->fetch(PDO::FETCH_ASSOC);

    Flight::json([$row]);
});
Flight::route('POST /SELECTparalelo', function () {
    $nombreParalelo = Flight::request()->data->nombre_paralelo;
    $sqlGetParaleloId = "SELECT id_paralelo FROM paralelos WHERE nombre_paralelo = :parallel";
    $stmtGetParaleloId = Flight::db()->prepare($sqlGetParaleloId);
    $stmtGetParaleloId->bindParam(':parallel', $nombreParalelo, PDO::PARAM_STR);
    $stmtGetParaleloId->execute();
    $row = $stmtGetParaleloId->fetch(PDO::FETCH_ASSOC);

    Flight::json([$row]);
});

// SELECT de materias
Flight::route('POST /SELECTmateria', function () {
    $nombreMateria = Flight::request()->data->nombre_materia;
    $sqlGetMateriaId = "SELECT id_materia FROM materias WHERE nombre_materia = :materia";
    $stmtGetMateriaId = Flight::db()->prepare($sqlGetMateriaId);
    $stmtGetMateriaId->bindParam(':materia', $nombreMateria, PDO::PARAM_STR);
    $stmtGetMateriaId->execute();
    $row = $stmtGetMateriaId->fetch(PDO::FETCH_ASSOC);

    Flight::json([$row]);
});

Flight::route('GET /obtener-id-profesor/@id_usuario', function ($id_usuario) {
    $sql = "SELECT id_profesor FROM profesores WHERE id_usuario = ?";
    $stmt = Flight::db()->prepare($sql);
    $stmt->bindParam(1, $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    Flight::json($resultado);
});
Flight::route('GET /obtener-id-estudiante/@id_usuario', function ($id_usuario) {
    $sql = "SELECT id_estudiante FROM estudiantes WHERE id_usuario = ?";
    $stmt = Flight::db()->prepare($sql);
    $stmt->bindParam(1, $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    Flight::json($resultado);
});

Flight::route('GET /SELECTALLmaster_all', function () {

    $sqlSelect = "SELECT id_master,nombre_materia FROM master_all";
    $resultados = Flight::db()->query($sqlSelect)->fetchAll(PDO::FETCH_ASSOC);
    Flight::json($resultados);
});

Flight::route('POST /SELECTmaster_all', function () {
    $nombreMateria = Flight::request()->data->nombre_materia;
    $idParalelo = Flight::request()->data->id_paralelo;
    $idSemestre = Flight::request()->data->id_semestre;
    $idMateria = Flight::request()->data->id_materia;
    $idProfesor = Flight::request()->data->id_profesor;

    $sqlSelect = "SELECT id_master FROM master_all WHERE nombre_materia = ? AND id_paralelo = ? AND id_semestre = ? AND id_materia = ? AND id_profesor = ?";
    $stmtSelect = Flight::db()->prepare($sqlSelect);
    $stmtSelect->bindParam(1, $nombreMateria);
    $stmtSelect->bindParam(2, $idParalelo);
    $stmtSelect->bindParam(3, $idSemestre);
    $stmtSelect->bindParam(4, $idMateria);
    $stmtSelect->bindParam(5, $idProfesor);
    $stmtSelect->execute();
    $row = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    Flight::json([$row]);
});

Flight::route('GET /SELECTmaster_allMATERIAS/@id_profesor', function ($idProfesor) {

    $sqlSelect = "SELECT id_master, nombre_materia FROM master_all WHERE id_profesor = ?";
    $stmtSelect = Flight::db()->prepare($sqlSelect);

    $stmtSelect->bindParam(1, $idProfesor, PDO::PARAM_INT);
    $stmtSelect->execute();
    $resultado = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

    Flight::json($resultado);
});

Flight::route('POST /INSERTmaster_all', function () {
    $nombreMateria = Flight::request()->data->nombre_materia;
    $idParalelo = Flight::request()->data->id_paralelo;
    $idSemestre = Flight::request()->data->id_semestre;
    $idMateria = Flight::request()->data->id_materia;
    $idProfesor = Flight::request()->data->id_profesor;

    $sqlInsert = "INSERT INTO master_all (nombre_materia, id_paralelo, id_semestre, id_materia, id_profesor) VALUES (?, ?, ?, ?, ?)";
    $stmtInsert = Flight::db()->prepare($sqlInsert);
    $stmtInsert->bindParam(1, $nombreMateria);
    $stmtInsert->bindParam(2, $idParalelo);
    $stmtInsert->bindParam(3, $idSemestre);
    $stmtInsert->bindParam(4, $idMateria);
    $stmtInsert->bindParam(5, $idProfesor);
    $stmtInsert->execute();

    $resultado = Flight::db()->lastInsertId();
    Flight::json([$resultado]);
});

Flight::route('POST /SELECTmatricula', function () {
    $idMasterMatricula = Flight::request()->data->id_master;
    $idEstudianteMatricula = Flight::request()->data->id_estudiante;

    // Verificar si la matrícula ya existe
    $sqlSelect = "SELECT id_matricula FROM matriculas WHERE id_master = ? AND id_estudiante = ?";
    $stmtSelect = Flight::db()->prepare($sqlSelect);
    $stmtSelect->bindParam(1, $idMasterMatricula);
    $stmtSelect->bindParam(2, $idEstudianteMatricula);
    $stmtSelect->execute();

    $stmtSelect->execute();
    $row = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    Flight::json([$row]);
});

Flight::route('POST /INSERTmatricula', function () {
    $idMasterMatricula = Flight::request()->data->id_master;
    $idEstudianteMatricula = Flight::request()->data->id_estudiante;

    $sqlInsert = "INSERT INTO matriculas (id_master, id_estudiante) VALUES (?, ?)";
    $stmtInsert = Flight::db()->prepare($sqlInsert);
    $stmtInsert->bindParam(1, $idMasterMatricula);
    $stmtInsert->bindParam(2, $idEstudianteMatricula);
    $stmtInsert->execute();

    $resultado = Flight::db()->lastInsertId();
    Flight::json([$resultado]);
});

Flight::route('POST /INSERTtarea', function () {
    $descripcionTarea = Flight::request()->data->descripcion;
    $nombre_tarea = Flight::request()->data->nombre_tarea;
    $fecha = Flight::request()->data->fecha;
    $idMasterTarea = Flight::request()->data->id_master;
    $id_profesor = Flight::request()->data->id_profesor;

    $sql = "INSERT INTO tareas (descripcion, nombre_tarea, fecha, id_master,id_profesor) VALUES (?, ?, ?, ?, ?)";

    $sentencia = Flight::db()->prepare($sql);

    $sentencia->bindParam(1, $descripcionTarea);
    $sentencia->bindParam(2, $nombre_tarea);
    $sentencia->bindParam(3, $fecha);
    $sentencia->bindParam(4, $idMasterTarea);
    $sentencia->bindParam(5, $id_profesor);

    $sentencia->execute();
    $resultado = Flight::db()->lastInsertId();
    Flight::json($resultado);
});
Flight::route('GET /SELECTtareas_by_profesor/@id_profesor', function ($id_profesor) {
    $sql = "SELECT t.id_tarea, t.descripcion, t.fecha, t.nombre_tarea, ma.nombre_materia FROM tareas t JOIN master_all ma ON t.id_master = ma.id_master WHERE t.id_profesor = ?";

    $stmt = Flight::db()->prepare($sql);
    $stmt->bindParam(1, $id_profesor, PDO::PARAM_INT);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    Flight::json($resultados);
});

Flight::route('POST /INSERTnota', function () {
    $idTareaNota = Flight::request()->data->id_tarea;
    $idEstudianteNota = Flight::request()->data->id_estudiante;
    $calificacionNota = Flight::request()->data->calificacion_nota;

    $sql = "INSERT INTO notas (id_tarea, id_estudiante, calificacion_nota) VALUES (?, ?, ?)";

    $sentencia = Flight::db()->prepare($sql);

    $sentencia->bindParam(1, $idTareaNota);
    $sentencia->bindParam(2, $idEstudianteNota);
    $sentencia->bindParam(3, $calificacionNota);

    $resultado = $sentencia->execute();

    if ($resultado) {
        Flight::json(["Mensaje" => "Nota ingresada exitosamente"]);
    } else {
        Flight::json(["Mensaje" => "Error al ingresar la nota"]);
    }
});

Flight::route('GET /SELECTnotas', function () {
    $correoEstudiante = Flight::request()->data->correo_electronico;  // Cambiar a la variable que recibes del formulario

    $sql = "SELECT m.nombre_materia, n.calificacion_nota
            FROM notas n
            JOIN tareas t ON n.id_tarea = t.id_tarea
            JOIN materias m ON t.id_master = m.id_master
            JOIN estudiantes e ON n.id_estudiante = e.id_estudiante
            JOIN usuarios u ON e.id_usuario = u.id
            WHERE u.correo_electronico = ?";

    $sentencia = Flight::db()->prepare($sql);
    $sentencia->bindParam(1, $correoEstudiante);

    $sentencia->execute();
    $resultados = $sentencia->fetchAll(PDO::FETCH_ASSOC);

    if ($resultados) {
        Flight::json(["Notas del estudiante" => $resultados]);
    } else {
        Flight::json(["Mensaje" => "No se encontraron notas para el estudiante"]);
    }
});
Flight::route('DELETE /eliminar_tarea', function () {
   
        $id_tarea = (Flight::request()->data->id);
        // Elimina las notas asociadas a la tarea
        
        $stmtNotas = Flight::db()->prepare("DELETE FROM notas WHERE id_tarea = ?");
        $stmtNotas->execute([$id_tarea]);

        // Elimina la tarea
        $stmtTarea = Flight::db()->prepare("DELETE FROM tareas WHERE id_tarea = ?");
        $stmtTarea->execute([$id_tarea]);

        Flight::db()->commit();

        Flight::json(['message' => 'Tarea eliminada exitosamente.']);
    
});
Flight::route('DELETE /DELETEusuario', function () {
    //pedimos datos necesarios
    $id = (Flight::request()->data->id);

    $sql1 = "DELETE FROM estudiantes WHERE id_usuario=?";
    $sentencia_uno = Flight::db()->prepare($sql1);
    $sentencia_uno->bindParam(1, $id);
    $sentencia_uno->execute();
    $filas_afectadas = $sentencia_uno->rowCount();

    if ($filas_afectadas > 0) {
        //escribimos sql final
        $sql = "DELETE FROM usuarios WHERE id = ?";
        //preparamos sentencia final
        $sentencia = Flight::db()->prepare($sql);
        $sentencia->bindParam(1, $id);
        //ejecutamos la sentencia
        $sentencia->execute();
        //retornamos una respuesta
        Flight::json(["Dato eliminado exitosamente"]); //esto funciona como un return
    } else {
        Flight::json(["Dato no eliminado exitosamente"]);
    }
});
Flight::route('PUT /UPDATEUsuario', function () {
    $id = Flight::request()->data->id;
    $correo = Flight::request()->data->correo;
    $nombre = Flight::request()->data->nombre;
    $apellido = Flight::request()->data->apellido;
    $cedula = Flight::request()->data->cedula;
    $contrasenia = Flight::request()->data->contrasenia;

    $sql = "UPDATE usuarios
            SET nombre = ?, apellido = ?, cedula = ?, contrasenia = ?, correo_electronico = ?
            WHERE id = ?";

    $sentencia = Flight::db()->prepare($sql);

    $sentencia->bindParam(1, $nombre);
    $sentencia->bindParam(2, $apellido);
    $sentencia->bindParam(3, $cedula);
    $sentencia->bindParam(4, $contrasenia);
    $sentencia->bindParam(5, $correo);
    $sentencia->bindParam(6, $id);

    $resultado = $sentencia->execute();
    Flight::json($resultado);
});

Flight::route('GET /SELECTusuarios/@id', function ($id) {
    $sentencia = Flight::db()->prepare('SELECT * FROM usuarios WHERE id =?');
    $sentencia->bindParam(1, $id);
    $sentencia->execute();
    $datos = $sentencia->fetchAll();
    Flight::json($datos);
});

Flight::start();
