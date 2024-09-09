<?php

namespace Model;

use Model\ActiveRecord;

class Proyecto extends ActiveRecord {
    protected static $tabla = 'proyectos';
    protected static $columnasDB = ['id', 'proyecto', 'url', 'propietario_id'];

    public $id;
    public $proyecto;
    public $url;
    public $propietario_id;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->proyecto = $args['proyecto'] ?? '';
        $this->url = $args['url'] ?? '';
        $this->propietario_id = $args['propietario_id'] ?? '';
    }

    public function validarProyecto() {
        if (!$this->proyecto) {
            self::$alertas['error'][] = 'El Nombre del Proyecto es Obligatorio';
        }
        return self::$alertas;
    }
}