<?php
namespace Base\Request;

use Base\Exception\BadRequestException;

class Validator
{
    /**
     * @param $value
     * @param $rule
     * @return mixed
     * @throws BadRequestException
     */
    public static function validateSemTag($value, $rule)
    {
        if (count(explode('-', $value)) !== 2) {
            throw new BadRequestException('tag只能有两个部分');
        }
        return $value;
    }
}