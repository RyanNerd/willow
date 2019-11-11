<?php
declare(strict_types=1);

namespace Willow\Controllers;

abstract class ActionBase
{
    protected function sanitize(array &$data, array $modelFields): void
    {
        foreach ($data as $field => $value) {
            if (array_key_exists($field, $modelFields)) {
                $dataType = $modelFields[$field];
                if ($dataType[0] === '*') {
                    unset($data[$field]);
                }
            }
        }
    }
}