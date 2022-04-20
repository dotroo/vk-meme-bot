<?php

namespace App\VKBundle\Helper;

use InvalidArgumentException;

class VkObjectValidator
{
    public static function validateObject(array $expectedParams, array $object) {
        foreach ($expectedParams as $param) {
            if (empty($object[$param])) {
                throw new InvalidArgumentException('Missing param: ' . $param);
            }
        }
    }
}