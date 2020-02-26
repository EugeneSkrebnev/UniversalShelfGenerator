<?php

namespace UniversalShelf\Objects;

/**
 * @property Point $p1 regular read/write property
 * @property Point $p2 regular read/write property
 * @property double $d regular read/write property
 * @property double $gap regular read/write property
 * @property int $index regular read/write property
 *
 * @property string $meta regular read/write property
 * @property string $name regular read/write property
 * @property string $comment regular read/write property
 * @property string $identifier regular read/write property
 */

class Tube extends AbstractJsonModel {

    protected  $p1, $p2, $d, $comment, $meta, $name, $gap, $identifier, $index;

    protected function propTypes() : array {
        return [
            'p1' => self::JSON_OBJECT,
            'p2' => self::JSON_OBJECT,
            'd'  => self::JSON_FLOAT,
            'gap'  => self::JSON_FLOAT,

            'name'         => self::JSON_STRING,
            'comment'      => self::JSON_STRING,
            'meta'         => self::JSON_STRING,
            'identifier'   => self::JSON_STRING,
            'index'        => self::JSON_INT
        ];
    }

    protected function propMapping() : array {
        return [
            ['p1'       => 'p1'],
            ['p2'       => 'p2'],
            ['d'        => 'd'],
            ['name'       => 'name'],
            ['comment'    => 'comment'],
            ['meta'       => 'meta'],
            ['gap'        => 'gap'],
            ['identifier' => 'identifier'],
            ['index'      => 'index']
        ];
    }

    /**
     * @param Point $p1
     * @param Point $p2
     * @param double $d
     * @return Tube
     */
    public static function newTube($p1, $p2, $d) {
        $res = new Tube();
        $res->p1 = $p1;
        $res->p2 = $p2;
        $res->d = $d;
        return $res;
    }


    /**
     * @param Point $sample
     * @return bool
     */
    public function havePoint($sample) {
        return ($this->p1->isEqualTo($sample) || $this->p2->isEqualTo($sample));
    }

    /**
     * @param Point $sample
     * @return Point
     */
    public function oppositePoint($sample) {
        if ($this->p1->isEqualTo($sample)) {
            return $this->p2;
        } else if ($this->p2->isEqualTo($sample)) {
            return $this->p1;
        } else {
            return null;
        }
    }

    public function tubeDescriptorTemplate() {
        $tubeSupport = 2;
        $tubeLengthSupport = 40;
        $res = implode(";\n", [
            'p1_XX = ' . $this->p1->defaultVarName(),
            'p2_XX = ' . $this->p2->defaultVarName(),
            'r_XX = ' . ($this->d / 2),
            'gap_XX = ' . $this->gap,
            'thickSupport_XX = ' . $tubeSupport,
            'lSupport_XX = ' .  $tubeLengthSupport,
            'tube_XX = [p1_XX, p2_XX, r_XX, gap_XX, lSupport_XX, thickSupport_XX];  //tube #' . $this->index
        ]) . ";\n";
        return str_replace('XX',  $this->identifier, $res);
    }

    public function defaultVarName() {
        return 'tube_' . $this->identifier;
    }

    public function drawCode() {
        $var = $this->defaultVarName();
        return "\nshelfTube($var);\n";
    }
}

