<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 21/02/15
 * Time: 17:17
 */

class Node {
    // Compteur d'instances
    public static $instances = 0;
    // Attributs
    public $id;
    public $label;
    public $x;
    public $y;
    public $size;
    public $img;

    public function __construct($label, $size, $img) {
        $this->id = 'n' . Node::$instances;
        $this->x = rand(0, 100);
        $this->y = rand(0, 100);
        $this->label = $label;
        $this->size = $size;
        $this->img = $img;

        Node::$instances++;
    }
} 