<?php
declare(strict_types=1);

namespace Willow\Controllers\TableAlias;

use Respect\Validation\Validator as V;
use Willow\Controllers\ValidatorBase;
use Willow\Middleware\ResponseBody;
use Willow\Models\TableAlias;

class TableAliasWriteValidator extends ValidatorBase
{
    /**
     * We override the processValidation placing our own validations for the given model
     *
     * @param ResponseBody $responseBody
     * @param array $parsedRequest
     */
    protected function processValidation(ResponseBody &$responseBody, array &$parsedRequest)
    {
        // Iterate all the model fields
        foreach(TableAlias::FIELDS as $field => $dataType) {
            // Is the model field NOT in the request?
            if (!v::key($field)->validate($parsedRequest)) {
                // Any dataType proceeded with an * are protected fields and can not be changed (e.g. password_hash)
                if ($dataType{0} === '*') {
                    continue;
                }

                // If the request is missing this field so register it as optional
                $responseBody->registerParam('optional', $field, $dataType);
            } else {
                // If Datatype is proceeded with an * it means the field is protected and can not be changed (e.g. password_hash)
                if ($dataType{1} === '*') {
                    $responseBody->registerParam('invalid', $field, null);
                }

                // Place your other validations for the existing request here
                // For example:
                // $parseValue = $parsedRequest[$field];
                // if ($field === 'last_name') {
                //    if (!V::length(1, 50)->validate($parseValue)) {
                //       $this->registerParam('invalid', $field, $dataType);
                //    }
                // }
            }
        }
    }
}