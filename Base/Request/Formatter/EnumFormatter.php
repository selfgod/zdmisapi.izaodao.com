<?php
namespace Base\Request\Formatter;

use Base\Exception\InternalServerErrorException;
use Base\Request\Formatter;

/**
 * EnumFormatter 格式化枚举类型
 *
 * @package     PhalApi\Request
 * @license     http://www.phalapi.net/license GPL 协议
 * @link        http://www.phalapi.net/
 * @author      dogstar <chanzonghuang@gmail.com> 2015-11-07
 */

class EnumFormatter extends BaseFormatter implements Formatter {

    /**
     * 检测枚举类型
     * @param string $value 变量值
     * @param array $rule array('name' => '', 'type' => 'enum', 'default' => '', 'range' => array(...))
     * @param $params
     * @return 当不符合时返回$rule
     * @throws InternalServerErrorException
     * @throws \Base\Exception\BadRequestException
     */
    public function parse($value, $rule, $params) {
        $this->formatEnumRule($rule);

        $this->formatEnumValue($value, $rule);

        return $value;
    }

    /**
     * 检测枚举规则的合法性
     * @param array $rule array('name' => '', 'type' => 'enum', 'default' => '', 'range' => array(...))
     * @throws InternalServerErrorException
     */
    protected function formatEnumRule($rule) {
        if (!isset($rule['range'])) {
            throw new InternalServerErrorException("{$rule['name']}缺少枚举范围");
        }

        if (empty($rule['range']) || !is_array($rule['range'])) {
            throw new InternalServerErrorException("{$rule['name']}枚举规则中的range不能为空");
        }
    }
}
