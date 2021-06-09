<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Willow\Models\ModelBase;

abstract class ActionBase
{
    protected ModelBase $model;

    /**
     * Set the model
     * todo: remove this
     * @param ModelBase $model
     */
    protected function setModel(ModelBase $model)
    {
        $this->model = $model;
    }

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
