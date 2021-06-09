<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Willow\Models\ModelBase;

abstract class ActionBase
{
    protected ModelBase $model;

    /**
     * Set the model (Need to use this for PHP 7.4 since union types aren't supported)
     * @param ModelBase $model
     */
    protected function setModel(ModelBase $model)
    {
        $this->model = $model;
    }

    /**
     * Any fields marked with '*' in their data type are NOT to be a part of the response (e.g. passwordHash)
     * @param array $data
     * @param array $modelFields
     */
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
