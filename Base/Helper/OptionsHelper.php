<?php

namespace Base\Helper;
class OptionsHelper
{
    protected static $options = [
        'curricular_system_zh' => [
            '1' => '主修课',
            '2' => '口语课',
            '3' => '选修课',
            '4' => '技能课',
            '5' => '定制课'
        ]
    ];

    public static function get_options($key, $optionKey = null)
    {
        if (isset(self::$options[$key])) {
            if ($optionKey !== null &&
                isset(self::$options[$key][$optionKey])
            ) {
                return self::$options[$key][$optionKey];
            } elseif ($optionKey === null) {
                return self::$options[$key];
            }
        }
        return FALSE;
    }

    /**
     * 通过option keys数组获取对应的values数组
     * @param $key
     * @param array $keys
     * @return array
     */
    public static function convert_keys_values($key, array $keys)
    {
        $result = [];
        if ($options = self::get_options($key)) {
            foreach ($keys as $key) {
                if (isset($options[$key])) {
                    $result[] = $options[$key];
                }
            }
        }
        return $result;
    }
}