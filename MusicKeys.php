<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 25/02/15
 * Time: 02:11
 */

class MusicKeys {
    public static $keys = array(  0 => 'Do', 1 => 'Do♯',
                            2 => 'Ré', 3 => 'Mi♭',
                            4 => 'Mi', 5 => 'Fa',
                            6 => 'Fa♯', 7 => 'Sol',
                            8 => 'Sol♯', 9 => 'La',
                            10 => 'Si♭', 11 => 'Si'  );

    public static $modes = array(0 => 'Mineur', 1 => 'Majeur');

    public static function getPitch($key, $mode) {
        if($key == 8 && $mode == 1)
            return 'La♭ Majeur';
        return MusicKeys::$keys[$key] . ' ' . MusicKeys::$modes[$mode];
    }
} 