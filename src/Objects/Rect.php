<?php

namespace UniversalShelf\Objects;

/**
 * @property Point $a regular read/write property
 * @property Point $b regular read/write property
 * @property Point $c regular read/write property
 * @property Point $d regular read/write property
 * @property double $thickness regular read/write property
 * @property double $gap regular read/write property
 * @property int $index regular read/write property
 *
 * @property string $meta regular read/write property
 * @property string $name regular read/write property
 * @property string $comment regular read/write property
 * @property string $identifier regular read/write property
 */

class Rect extends AbstractJsonModel {
    protected $a, $b, $c, $d, $thickness, $gap;
    protected $comment, $meta, $name, $identifier, $index;


    protected function objectForKeyFromArray($key, $arrayValue) {
        if ( in_array($key,['a', 'b', 'c', 'd'])) {
            return new Rect(json_encode($arrayValue));
        }
        die('NO overloaded objectForKeyFromArray found for key:' . $key);
    }

    protected function propTypes() : array {
        return [
            'a' => self::JSON_OBJECT,
            'b' => self::JSON_OBJECT,
            'c' => self::JSON_OBJECT,
            'd' => self::JSON_OBJECT,
            'thickness'    => self::JSON_FLOAT,
            'gap'          => self::JSON_FLOAT,
            'name'         => self::JSON_STRING,
            'comment'      => self::JSON_STRING,
            'meta'         => self::JSON_STRING,
            'identifier'   => self::JSON_STRING,
            'index'        => self::JSON_INT
        ];
    }

    protected function propMapping() : array {
        return [
            ['a'       => 'a'],
            ['b'       => 'b'],
            ['c'       => 'c'],
            ['d'       => 'd'],
            ['thickness'  => 'thickness'],
            ['name'       => 'name'],
            ['comment'    => 'comment'],
            ['meta'       => 'meta'],
            ['gap'        => 'gap'],
            ['identifier'  => 'identifier'],
            ['index'       => 'index']
        ];
    }

    /**
     * @param Point $p1
     * @param Point $p2
     * @param Point $p3
     * @param Point $p4
     * @param double $thickness
     * @return Rect
     */
    public static function newRect($p1, $p2, $p3, $p4, $thickness) {
        $res = new Rect();
        $res->a = $p1;
        $res->b = $p2;
        $res->c = $p3;
        $res->d = $p4;
        $res->thickness = $thickness;
        return $res;
    }

    /**
     * @param Point $sample
     * @return bool
     */
    public function havePoint($sample) {
        return ($this->a->isEqualTo($sample) || $this->b->isEqualTo($sample) ||
                $this->c->isEqualTo($sample) || $this->d->isEqualTo($sample)  );
    }

    /**
     * @param Point $point
     * @return Point[]
     */

    public function nearPointsForPoint($point) {
        $arr = [$this->a, $this->b, $this->c, $this->d, $this->a, $this->b, $this->c, $this->d];
        for ($i = 1; $i < count($arr) - 1; $i++) {
            if ($point->isEqualTo($arr[$i]))
                return [$arr[$i - 1], $arr[$i + 1]];
        }
        return [];
    }

    public function rectDescriptorTemplate() {
        $rectSupport = 2;
        $rectLengthSupport = 30;
        $params = implode(";\n", [
            'a_XX = ' . $this->a->defaultVarName(),
            'b_XX = ' . $this->b->defaultVarName(),
            'c_XX = ' . $this->c->defaultVarName(),
            'd_XX = ' . $this->d->defaultVarName(),
            'thickness_XX = ' . $this->thickness,
            'gapr_XX = ' . $this->gap,
            'rectThickSupport_XX = ' . $rectSupport,
            'rectLSupport_XX = ' .  $rectLengthSupport,
            'rect_XX = [a_XX, b_XX, c_XX, d_XX, thickness_XX, gapr_XX, rectLSupport_XX, rectThickSupport_XX];  //plate #' . $this->index
        ]) . ";\n";
        return str_replace('XX',  $this->identifier, $params);
    }


    public function defaultVarName() {
        return 'rect_' . $this->identifier;
    }

    public function drawCode() {
        $var = $this->defaultVarName();
        return "\nshelfRect($var);\n";
    }
}

