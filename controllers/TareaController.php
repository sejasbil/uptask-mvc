<?php

namespace Controllers;

use Model\Proyecto;
use Model\Tarea;

class TareaController {
    public static function index() {
        $proyecto_id = $_GET['id'];

        // Validación para saber si existe el proyecto
        if (!$proyecto_id) header('Location: /dashboard');

        // Ver si es la misma cuenta que lo creo quien lo intenta ver
        $proyecto = Proyecto::where('url', $proyecto_id);
        session_start();

        if (!$proyecto || $proyecto->propietario_id !== $_SESSION['id']) header('Location: /404');

        $tareas = Tarea::belongsTo('proyecto_id', $proyecto->id);

        echo json_encode(['tareas' => $tareas]);
    }

    public static function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();

            $proyecto_id = $_POST['proyecto_id'];

            $proyecto = Proyecto::where('url', $proyecto_id);

            if (!$proyecto || $proyecto->propietario_id !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un Error al agregar la Tarea'
                ];
                echo json_encode($respuesta);
                return;
            }
            // Instanciar y crear la tarea
            $tarea = new Tarea($_POST);
            $tarea->proyecto_id = $proyecto->id;
            $resultado = $tarea->guardar();
            $respuesta = [
                'tipo' => 'exito',
                'id' => $resultado['id'],
                'mensaje' => 'Tarea Creada Correctamente',
                'proyecto_id' => $proyecto->id
            ];
            echo json_encode($respuesta);
        }
    }

    
    public static function actualizar() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar que el proyecto exista
            $proyecto = Proyecto::where('url', $_POST['proyecto_id']);

            session_start();

            if (!$proyecto || $proyecto->propietario_id !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un Error al actualizar la tarea'
                ];
                echo json_encode($respuesta);
                return;
            }        
            $tarea = new Tarea($_POST);
            $tarea->proyecto_id = $proyecto->id;

            $resultado = $tarea->guardar();

            if ($resultado) {
                $respuesta = [
                    'tipo' => 'exito',
                    'id' => $tarea->id,
                    'mensaje' => 'Actualizado Correctamente',
                    'proyecto_id' => $proyecto->id
                ];
                echo json_encode(['respuesta' => $respuesta]);
            }
        }
    }
    
    public static function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar que el proyecto exista
            $proyecto = Proyecto::where('url', $_POST['proyecto_id']);

            session_start();

            if (!$proyecto || $proyecto->propietario_id !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un Error al actualizar la tarea'
                ];
                echo json_encode($respuesta);
                return;
            }     

            $tarea = new Tarea($_POST);
            $resultado = $tarea->eliminar();

            $resultado = [
                'resultado' => $resultado,
                'mensaje' => 'Eliminado Correctamente',
                'tipo' => 'exito'
            ]; 

            echo json_encode($resultado);
        }
    }
}