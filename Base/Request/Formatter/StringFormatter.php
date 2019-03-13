<?php
namespace Base\Request\Formatter;

use Base\Exception\BadRequestException;
use Base\Request\Formatter;

/**
 * StringFormatter  格式化字符串
 * @package     PhalApi\Request
 * @license     http://www.phalapi.net/license GPL 协议
 * @link        http://www.phalapi.net/
 * @author      dogstar <chanzonghuang@gmail.com> 2015-11-07
 */
class StringFormatter extends BaseFormatter implements Formatter {

    /**
     * 对字符串进行格式化
     *
     * @param $value
     * @param mixed $value 变量值
     * @param array $rule array('len' => ‘最长长度’)
     * @param $params
     * @return string 格式化后的变量
     * @throws BadRequestException
     */
    public function parse($value, $rule, $params) {

        $rs = strval($this->filterByStrLen(strval($value), $rule));

        $this->filterByRegex($rs, $rule);

        return $rs;
    }

    /**
     * 根据字符串长度进行截取
     * @throws \Base\Exception\BadRequestException
     */
    protected function filterByStrLen($value, $rule) {

        $lenRule         = $rule;
        $lenRule['name'] = $lenRule['name'] . '.len';
        $lenValue        = mb_strlen($value, 'utf-8');
        $this->filterByRange($lenValue, $lenRule);
        return $value;
    }

    /**
     * 进行正则匹配
     * @throws BadRequestException
     */
    protected function filterByRegex($value, $rule) {

        if (!isset($rule['regex']) || empty($rule['regex'])) {
            return;
        }

        // 为安全起见，仅在调试模式下，才显示正则表达式
        if (preg_match($rule['regex'], $value) <= 0) {
            throw new BadRequestException("{$rule['name']}无法匹配");
        }
    }

}
