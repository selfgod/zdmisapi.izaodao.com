<?php
namespace Base\Request\Formatter;

use Base\Exception\InternalServerErrorException;
use Base\Request\Formatter;

/**
 * CallableFormatter 格式化回调类型
 *
 * @package     PhalApi\Request
 * @license     http://www.phalapi.net/license GPL 协议
 * @link        http://www.phalapi.net/
 * @author      dogstar <chanzonghuang@gmail.com> 2015-11-07
 */


class CallableFormatter extends BaseFormatter implements Formatter {

    /**
     * 对回调类型进行格式化
     *
     * @param mixed $value 变量值
     * @param array $rule array('callback' => '回调函数', 'params' => '第三个参数')
     * @param $params
     * @return boolean/string 格式化后的变量
     * @throws InternalServerErrorException
     */
    public function parse($value, $rule, $params) {
        $callback = isset($rule['callback']) 
            ? $rule['callback'] 
            : (isset($rule['callable']) ? $rule['callable'] : NULL);

        // 提前触发回调类的加载，以便能正常回调
        if (is_array($callback) && count($callback) >= 2 && is_string($callback[0])) {
            // Type 2：静态类方法，如：array('MyClass', 'myCallbackMethod')
            class_exists($callback[0]);
        } else if (is_string($callback) && preg_match('/(.*)\:\:/', $callback, $macthes)) {
            // Type 4：静态类方法，如：'MyClass::myCallbackMethod'
            class_exists($macthes[1]);
        }

        if (empty($callback) || !is_callable($callback)) {
            throw new InternalServerErrorException("{$rule['name']}参数规则的回调函数非法");
        }

        if (isset($rule['params'])) {
            return call_user_func($callback, $value, $rule, $params, $rule['params']);
        } else {
            return call_user_func($callback, $value, $rule, $params);
        }
    }
}
