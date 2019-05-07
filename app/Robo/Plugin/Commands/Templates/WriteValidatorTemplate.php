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
     * @throws \ReflectionException
     */
    protected function processValidation(ResponseBody &$responseBody, array &$parsedRequest)
    {
        // Get a associative array of all the fields for the model and their data type
        $modelFields = $this->getModelFields(TableAlias::class);

        // Go through all the model fields (from the model docblock)
        foreach($modelFields as $field => $dataType) {
            // Is the model field NOT in the request?
            if (!v::key($field)->validate($parsedRequest)) {
                // If the property/field is required in the request then:
                // $responseBody->registerParam('required', $field, $dataType);
                $responseBody->registerParam('optional', $field, $dataType);
            } else {
                // Validations for existing request
                // For example:
                // $parseValue = $parsedRequest[$field];
                // if ($property === 'last_name') {
                //    if (!V::length(1, 50)->validate($parseValue)) {
                //       $this->registerParam('invalid', $field, $dataType);
                //    }
                // }
            }
        }
    }
}