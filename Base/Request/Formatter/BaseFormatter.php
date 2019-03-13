<?php
namespace Base\Request\Formatter;

use Base\Exception\BadRequestException;

/**
 * BaseFormatter 公共基类
 *
 * - 提供基本的公共功能，便于子类重用
 *
 * @package     PhalApi\Request
 * @license     http://www.phalapi.net/license GPL 协议
 * @link        http://www.phalapi.net/
 * @author      dogstar <chanzonghuang@gmail.com> 2015-11-07
 */


class BaseFormatter {

    /**
     * 根据范围进行控制
     * @throws BadRequestException
     */
    protected function filterByRange($value, $rule) {
        $this->filterRangeMinLessThanOrEqualsMax($rule);

        $this->filterRangeCheckMin($value, $rule);

        $this->filterRangeCheckMax($value, $rule);

        return $value;
    }

    /**
     * @param $rule
     * @throws BadRequestException
     */
    protected function filterRangeMinLessThanOrEqualsMax($rule) {
        if (isset($rule['min']) && isset($rule['max']) && $rule['min'] > $rule['max']) {
            throw new BadRequestException("最小值应该小于等于最大值，但现在{$rule['name']}的最小值为：{$rule['min']}，最大值为：{$rule['max']}");
        }
    }

    /**
     * @param $value
     * @param $rule
     * @throws BadRequestException
     */
    protected function filterRangeCheckMin($value, $rule) {
        if (isset($rule['min']) && $value < $rule['min']) {
            throw new BadRequestException("{$rule['name']}应该大于或等于{$rule['min']}, 但现在{$rule['name']} = {$value}");
        }
    }

    /**
     * @param $value
     * @param $rule
     * @throws BadRequestException
     */
    protected function filterRangeCheckMax($value, $rule) {
        if (isset($rule['max']) && $value > $rule['max']) {
            throw new BadRequestException("{$rule['name']}应该小于等于{$rule['max']}, 但现在{$rule['name']} = {$value}");
        }
    }

    /**
     * 格式化枚举类型
     * @param string $value 变量值
     * @param array $rule array('name' => '', 'type' => 'enum', 'default' => '', 'range' => array(...))
     * @throws BadRequestException
     */
    protected function formatEnumValue($value, $rule) {
        if (!in_array($value, $rule['range'])) {
            $range = implode('/', $rule['range']);
            throw new BadRequestException("参数{$rule['name']}应该为：{$range}，但现在{$rule['name']} = {$value}");
        }
    }
}
