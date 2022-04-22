<?php

namespace App\VKBundle\Helper;

use InvalidArgumentException;

class VkObjectValidator
{
    /**
     * @param array $expectedParams
     * @param array $object
     * @throws InvalidArgumentException
     * @return void
     */
    public static function validateObject(array $expectedParams, array $object) {
        foreach ($expectedParams as $param) {
            if (empty($object[$param])) {
                throw new InvalidArgumentException('Missing param: ' . $param);
            }
        }
    }
}