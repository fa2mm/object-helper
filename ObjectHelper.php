<?php

namespace \tecsvit;

/**
 * Class ObjectHelper
 *
 * @static PublicErrors $errorInstance
 *
 * @use \tecsvit\PublicErrors
 */
class ObjectHelper
{
    public static $errorInstance;

    /**
     * @return void
     */
    public static function initErrors()
    {
        if (null === self::$errorInstance) {
            self::$errorInstance = new PublicErrors();
        }
    }

    /**
     * @return PublicErrors
     */
    public static function errorInstance()
    {
        self::initErrors();
        return self::$errorInstance;
    }


    /**
     * @param $attributes
     * @param $default
     * @return void
     */
    public static function removeNull(&$attributes, $default = [])
    {
        foreach ($attributes as $attribute => &$value) {
            if ($value === null) {
                $value = self::getAttribute($default, $attribute);
            }
        }
    }

    /**
     * @param object|array $object
     * @param string|array   $attributes
     * @param mixed          $default
     * @param callable       $callback
     * @param boolean        $trim
     * @return mixed
     */
    public static function getAttribute($object, $attributes, $default = '', $callback = null, $trim = true)
    {
        try {
            if (is_array($attributes)) {
                if (!empty($attributes)) {
                    $localObject = $object;
                    foreach ($attributes as $attribute) {
                        $localObject = self::findElement($localObject, $attribute, $default);
                    }
                } else {
                    $localObject = $object;
                }

                $result = $localObject;
            } else {
                $result = self::findElement($object, $attributes, $default);
            }

            $result = $trim ? self::trimString($result) : $result;

            return $callback ? $callback($result) : $result;
        } catch (\Exception $e) {
            self::errorInstance()->addError($e->getMessage());
            return $default;
        }
    }

    /**
     * @param string $data
     * @return string
     */
    public static function trimString($data)
    {
        return is_string($data) ? trim($data) : $data;
    }

    /**
     * @param string $json
     * @param null   $default
     * @param bool   $assoc
     * @return mixed|null
     */
    public static function jsonDecode($json, $default = null, $assoc = false)
    {
        $result = \json_decode($json, $assoc);

        if (json_last_error() == JSON_ERROR_NONE) {
            return $result;
        } else {

            self::errorInstance()->addError('JSON error: ' . json_last_error_msg() . '. JSON body: ' . PHP_EOL . $json);

            return $default;
        }
    }

    /**
     * @param     $value
     * @param int $options
     * @param int $depth
     * @return string
     */
    public static function jsonEncode($value, $options = 0, $depth = 512)
    {
        $result = \json_encode($value, $options, $depth);

        if (json_last_error() !== JSON_ERROR_NONE) {
            self::errorInstance()->addError(
                'JSON encode error: ' . json_last_error_msg() . '. Body: ' . PHP_EOL . $value
            );
        }

        return $result;
    }

    /**
     * @param $object
     * @param $attribute
     * @param $default
     * @return mixed
     */
    private static function findElement($object, $attribute, $default)
    {
        if ($attribute === null) {
            $result = $object;
        } elseif (isset($object->$attribute)) {
            $result = $object->$attribute;
        } elseif (is_array($object) && isset($object[$attribute])) {
            $result = $object[$attribute];
        } else {
            $result = $default;
        }

        return $result;
    }
}
