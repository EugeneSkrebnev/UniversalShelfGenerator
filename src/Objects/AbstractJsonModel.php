<?php

namespace UniversalShelf\Objects;


abstract class AbstractJsonModel {
    const JSON_FLOAT = 0;
    const JSON_STRING = 1;
    const JSON_INT = 2;
    const JSON_ARRAY = 3;
    const JSON_DATE = 4;
    const JSON_BOOL = 6;
    const JSON_PRICE = 7;
    const JSON_ARRAY_OF_OBJECTS = 8;
    const JSON_OBJECT = 9;
    const TIME_ZONE = 2 * 24 * 60 * 60;  //timezone correction . ' Europe/Minsk');

    abstract protected function propTypes() : array;
    abstract protected function propMapping() : array;

    protected $id, $reflectionClass;

//magic only right way php sorry
    public function __set($name, $value) {
        $setType = $this->propTypes()[$name];
        $value = $this->validate($name, $value);

        if ($setType === self::JSON_FLOAT) {
            return $this->{$name} = (double)$value;
        }
        if ($setType === self::JSON_PRICE) {
            return $this->{$name} = (double)round($value, 2);
        }
        if ($setType === self::JSON_STRING) {
            return $this->{$name} = (string)$value;
        }
        if ($setType === self::JSON_INT) {
            return $this->{$name} = (int)$value;
        }
        if ($setType === self::JSON_DATE) {
            $val = (int)($value - ($value % (24 * 3600)));
            return $this->{$name} = $val;
        }
        if ($setType === self::JSON_ARRAY) {
            return $this->{$name} = count((array)$value) > 0 ? $value : null;
        }
        if ($setType === self::JSON_ARRAY_OF_OBJECTS) {
            return $this->{$name} = count((array)$value) > 0 ? $value : array();
        }
        if ($setType === self::JSON_OBJECT) {
            return $this->{$name} = $value;
        }
        if ($setType === self::JSON_BOOL) {
            return $this->{$name} = (bool)$value;
        }
        die('NO SUCH PROPERTY1: ' . $name . "\n");
    }

    public function __get($name) {
        $setType = $this->propTypes()[$name];
        if (is_null($setType))
            die('NO SUCH PROPERTY2: ' . $name . "\n");
        if ($setType == self::JSON_PRICE)
            return round($this->{$name}, 2);
        return $this->{$name};
    }

    protected function validate($key, $value) {
        return $value;
    }

    protected function objectForKeyFromArray($key, $arrayValue) {
        die('NO overloaded objectForKeyFromArray found for key:' . $key);
        return null;
    }

    /**
     * @return array
     */
    protected function keysForEqualCompare() {
        die('NO overloaded keysForEqualCompare find:');
        return array();
    }

    /**
     * @return array
     */
    protected function keysForExcludeFromDiff() {
        return array();
    }

    /**
     * @param $propertyName string
     * @return string
     */
    public function descriptionForProperty($propertyName) {
        die('NO overloaded keysForEqualCompare find:');
        return "";
    }

    /**
     * @return mixed
     */
    public function shortDescription() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function description() {
        return $this->id;
    }

    /**
     * @param $action string
     * @return mixed
     */
    public function descriptionForAction($action) {
        return $this->id;
    }

    function __construct($json = null) {
        if (is_null($json))
            return;

        $jsonArray = json_decode($json, true);

        foreach ($this->propMapping() as $mapping) {
            foreach ($mapping as $jsonKey => $objKey) {
                $value = array_key_exists($jsonKey, $jsonArray) ? $jsonArray[$jsonKey] : null;
                $setType = $this->propTypes()[$objKey];
                if ($setType == self::JSON_DATE) {
                    $value = (int)strtotime($value) + AbstractJsonModel::TIME_ZONE;
                }
                if ($setType == self::JSON_STRING) {
                    //check if its array than convert to json string
                    if (is_array($value))
                        $value = json_encode($value);
                }
                if ($setType == self::JSON_ARRAY_OF_OBJECTS) {
                    $resValue = array();
                    $objects = is_null($value) ? [] : $value;
                    foreach ($objects as $item) {
                        $resValue[] = $this->objectForKeyFromArray($objKey, $item);
                    }
                    $value = $resValue;
                }
                if ($setType == self::JSON_OBJECT) {
                    $value = $this->objectForKeyFromArray($objKey, $value);
                }
                $this->__set($objKey, $value);
            }
        }
    }
    
    public function toArray() : array {
        $result = array();
        foreach ($this->propMapping() as $mapping) {
            foreach ($mapping as $jsonKey => $objectKey) {
                $setType = $this->propTypes()[$objectKey];
                if ($setType === self::JSON_DATE) {
                    $timestamp = $this->{$objectKey};
                    if ($timestamp > 3 * 3600) {
                        $strDate = gmdate("Y-m-d H:i:s", $timestamp + AbstractJsonModel::TIME_ZONE);
                        $result[$jsonKey] = $strDate;
                    } else {
                        $result[$jsonKey] = '';
                    }
                }
                else if ($setType === self::JSON_ARRAY_OF_OBJECTS) {
                    $objects = $this->{$objectKey};
                    if (is_null($objects))
                        $objects = [];
                    $result[$jsonKey] = array_map(function ($itm) {
                        return $itm->toArray();
                    }, $objects);
                }
                else if ($setType === self::JSON_OBJECT) {
                    $object = $this->{$objectKey};
                    if (!is_null($object)) {
                        $result[$jsonKey] = $object->toArray();
                    }
                } else if ($setType === self::JSON_STRING) {
                    $text = $this->{$objectKey};
                    while (strlen($text) > 0 && iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text) == false) {
                        $text = substr($text, 1);
                    }
                    if ($text != $this->{$objectKey})
                        $text = iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text);
                    $result[$jsonKey] = $text;
                }
                else {
                    $result[$jsonKey] = $this->{$objectKey};
                }
            }
        }
        if (strlen($this->reflectionClass()) > 0) {
            $result['reflectionClass'] = $this->reflectionClass();
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function compareProperty($propName, $value) {
        $val = $this->{$propName};
        $type = $this->propTypes()[$propName];
        if ($type === self::JSON_FLOAT || $type === self::JSON_INT || $type === self::JSON_DATE)  {
            return abs($val - $value) < 0.00001;
        }
        if ($type === self::JSON_STRING) {
            return strcmp($val, $value) == 0;
        }
        if ($type === self::JSON_BOOL) {
            return $val === $value;
        }
        if ($type === self::JSON_ARRAY) {
            $find = false;
            foreach ($val as $item) {
                $find = $find || ($item == $value);
            }
            return $find;

        }
        return false;
    }


    public function isEqualTo($sample) {
        $result = true;
        foreach ($this->keysForEqualCompare() as $keypath) {
            $val = $this->{$keypath};
            $val2 = $sample->{$keypath};
            $result = $result && ($val == $val2);
        }
        return $result;
    }

    /**
     * @param $sample
     * @param bool $byMainProperties
     * @return array
     */
    public function findDiffs($sample, $byMainProperties = false) {
        $properties = $this->propTypes();
        $changes = [];

        $properties = $byMainProperties ? $this->keysForEqualCompare() : array_keys($properties);
        $keyPaths = array_diff($properties, $this->keysForExcludeFromDiff());

        foreach ($keyPaths as $keyPath) {
            $val = $this->{$keyPath};
            $val2 = $sample->{$keyPath};
            $isEqual = ($val == $val2);
            if (!$isEqual) {
                $changeItem = [$keyPath, $val, $val2];
                $changes[$keyPath] = $changeItem;
            }
        }

        return $changes;
    }

    /**
     * @param $json string
     */
    public function updateFieldsFromJson($json) {
        $className = get_class($this);
        $source = new $className($json);
        $keyPaths = array_keys($this->propTypes());
        foreach ($keyPaths as $keyPath) {
            if ($source->{$keyPath}) {
                $this->{$keyPath} = $source->{$keyPath};
            }
        }
    }

    public function reflectionClass() {
        return $this->reflectionClass;
    }
}

