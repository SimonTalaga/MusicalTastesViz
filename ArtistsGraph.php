<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 21/02/15
 * Time: 18:47
 */

class ArtistsGraph {
    private $nodes;
    private $edges;

    public function getNodes() {
        return $this->nodes;
    }

    public function __construct($nodes) {
        // Nivelation des tailles
        $playcounts = array();
        foreach($nodes as $node) {
            array_push($playcounts, $node->size);
        }

        $min = min($playcounts);
        $max = max($playcounts);
        $avg = array_sum($playcounts) / count($playcounts);

        foreach($nodes as &$node) {
          $node->size = ($node->size - $avg) / ($max - $min) * 4 + $avg / ($max - $min) * 4 + 1;
        };

        $this->nodes = $nodes;
        $this->edges = array();
    }

    public function addEdge(Edge $edge) {
        array_push($this->edges, $edge);
    }

    public function searchArtist($name) {
        foreach($this->nodes as $node) {
           if($node->label == $name)
               return $node->id;
        }
        return false;
    }

    public function searchLink($source, $target) {
        foreach($this->edges as $edge) {
            if($edge->source == $source && $edge->target == $target || $edge->source == $target && $edge->target == $source)
                return $edge->id;
        }
        return false;
    }

    public function saveToJSON($filename) {
        $data = array('nodes' => (array)$this->nodes, 'edges' => (array)$this->edges);
        $file = fopen($filename, 'w');
        $json = stripslashes(json_encode($data, JSON_UNESCAPED_UNICODE));
        fwrite($file, mb_convert_encoding($json, 'UTF-8', 'auto'));
        fclose($file);
    }
} 