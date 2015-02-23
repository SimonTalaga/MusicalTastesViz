<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 21/02/15
 * Time: 17:18
 */

class Edge {
    // Compteur d'instances
    public static $instances = 0;
    // Attributs
    public $id;
    public $source;
    public $target;

    public function __construct($source, $target) {
        $this->id = 'e' . Edge::$instances;
        $this->source = $source;
        $this->target = $target;
        Edge::$instances++;
    }
} 