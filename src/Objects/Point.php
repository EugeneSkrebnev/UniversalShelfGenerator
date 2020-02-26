<?php

namespace UniversalShelf\Objects;

/**
 * @property double $x regular read/write property
 * @property double $y regular read/write property
 * @property double $z regular read/write property
 *
 * @property int $index regular read/write property
 *
 * @property int $type regular read/write property
 * @property string $name regular read/write property
 * @property string $comment regular read/write property
 * @property string $meta regular read/write property
 * @property string $identifier regular read/write property
 */

class Point extends AbstractJsonModel {

    protected $x, $y, $z, $comment, $type, $meta, $name, $index, $identifier;

    protected function propTypes() : array {
        return [
            'x' => self::JSON_FLOAT,
            'y' => self::JSON_FLOAT,
            'z' => self::JSON_FLOAT,
            'type'         => self::JSON_INT,
            'index'        => self::JSON_INT,
            'name'         => self::JSON_STRING,
            'comment'      => self::JSON_STRING,
            'meta'         => self::JSON_STRING,
            'identifier'   => self::JSON_STRING
        ];
    }

    protected function propMapping() : array {
        return [
            ['x'       => 'x'],
            ['y'       => 'y'],
            ['z'       => 'z'],
            ['index'      => 'index'],
            ['type'       => 'type'],
            ['name'       => 'name'],
            ['comment'    => 'comment'],
            ['meta'       => 'meta'],
            ['identifier'  => 'identifier']
        ];
    }

    /**
     * @param array $vec
     * @return Point
     */
    public static function newPointFromVec($vec, $multiplier = 1) {
        if (count($vec) < 3)
            return null;
        $res = new Point();
        $res->x = $vec[0] * $multiplier;
        $res->y = $vec[1] * $multiplier;
        $res->z = $vec[2] * $multiplier;
        return $res;
    }

    public static function eps() {
        return 0.001;
    }
    /**
     * @param Point $p1
     * @param Point $p2
     * @return double
     */
    public static function veclen($p1, $p2) {
        $deltaX = $p2->x - $p1->x;
        $deltaY = $p2->y - $p1->y;
        $deltaZ = $p2->z - $p1->z;
        return sqrt(($deltaX*$deltaX) + ($deltaY*$deltaY) + ($deltaZ*$deltaZ));
    }

    /**
     * @param Point $p1
     * @param Point $p2
     * @return bool
     */
    public static function pointsAreEqual($p1, $p2) {
        return  self::veclen($p1, $p2) < self::eps();
    }

    /**
     * @param Point $sample
     * @return bool
     */
    public function isEqualTo($sample) {
        return self::pointsAreEqual($sample, $this);
    }

    public function pointDescriptorTemplate($pointName = 'point') {
        return $pointName . ' = [' . implode(', ', [$this->x, $this->y, $this->z]) . ']; // point #' . $this->index;
    }

    public function defaultVarName() {
        return 'point' . $this->identifier;
    }

    public function defaultDescriptor() {
        return $this->pointDescriptorTemplate($this->defaultVarName());
    }
    /**
     * @param Point $sample
     * @return double
     */
    public function distanceTo($sample) {
        return self::veclen($this, $sample);
    }
}

