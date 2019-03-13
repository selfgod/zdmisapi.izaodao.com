<?php
namespace Base\Request\Formatter;

use Base\Request\Formatter;

/**
 * IntFormatter 格式化整型
 *
 * @package     PhalApi\Request
 * @license     http://www.phalapi.net/license GPL 协议
 * @link        http://www.phalapi.net/
 * @author      dogstar <chanzonghuang@gmail.com> 2015-11-07
 */

class IntFormatter extends BaseFormatter implements Formatter {

    /**
     * 对整型进行格式化
     *
     * @param mixed $value 变量值
     * @param array $rule array('min' => '最小值', 'max' => '最大值')
     * @param $params
     * @return int/string 格式化后的变量
     *
     * @throws \Base\Exception\BadRequestException
     */
    public function parse($value, $rule, $params) {
        return intval($this->filterByRange(intval($value), $rule));
    }
}
