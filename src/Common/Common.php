<?php
/**
 * Created by PhpStorm.
 * User: LINH
 * Date: 9/24/2020
 * Time: 1:48 PM
 */

namespace Linhnh95\LaravelQuerySupport\Common;


class Common
{
    /**
     * @param string $table
     * @param string $field
     * @param string $relation
     * @param string $keyField
     *
     * @return array
     */
    public static function parseQueryCondition(string $table = '', string $field = '', string $relation = '', string $keyField = '')
    {
        return [
            'is_relation' => $relation !== '' ? true : false,
            'relation' => $relation,
            'table' => $table,
            'field' => $field,
            'key_field' => $keyField
        ];
    }
}
