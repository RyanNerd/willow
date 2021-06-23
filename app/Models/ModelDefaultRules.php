<?php
declare(strict_types=1);

namespace Willow\Models;

use Willow\Middleware\ResponseBody;

class ModelDefaultRules
{
    /**
     * Allowed request parameters that are not column names
     */
    private const WHITE_LIST = ['id', 'api_key'];

    /**
     * @param ResponseBody $responseBody
     * @param array $modelColumnAttributes
     * @return ResponseBody
     */
    public function __invoke(ResponseBody $responseBody, array $modelColumnAttributes): ResponseBody {
        $parsedRequest = $responseBody->getParsedRequest();

        // Required
        $responseBody = $this->checkRequiredParameters(($responseBody), $modelColumnAttributes, $parsedRequest);
        if ($responseBody->hasMissingRequiredOrInvalid()) {
            return $responseBody;
        }

        // Not Null
        $responseBody = $this->checkNotNullRequests(($responseBody), $modelColumnAttributes, $parsedRequest);
        if ($responseBody->hasMissingRequiredOrInvalid()) {
            return $responseBody;
        }

        // Unexpected parameters
        $responseBody =
            $this->checkRequestHasNoUnexpectedParameters(($responseBody), $modelColumnAttributes, $parsedRequest);

        // Max length
        $responseBody
            = $this->checkRequestMaxLength(($responseBody), $modelColumnAttributes);
        return $responseBody;
    }

    /**
     * The expectation is that all column names will be present in the request,
     * register a required response if any are missing.
     * @param ResponseBody $responseBody
     * @param array $modelColumnAttributes
     * @param array $parsedRequest
     * @return ResponseBody
     */
    private function checkRequiredParameters(
        ResponseBody $responseBody,
        array $modelColumnAttributes,
        array $parsedRequest
    ): ResponseBody {
        $outLiars = array_diff_key($modelColumnAttributes, $parsedRequest);
        if (count($outLiars) > 0) {
            foreach ($outLiars as $columnName => $fieldAttributes) {
                $flags = $fieldAttributes['Flags'];

                // Ignore column exemptions
                if ($flags === null || in_array('CE', $flags)) {
                    continue;
                }
                $responseBody
                    ->registerParam('required', $columnName, $fieldAttributes['Type'], "$columnName is required");
            }
        }
        return $responseBody;
    }

    private function checkNotNullRequests(
        ResponseBody $responseBody,
        array $modelColumnAttributes,
        array $parsedRequest
    ) : ResponseBody {
        foreach ($modelColumnAttributes as $columnName => $fieldAttributes) {
            $flags = $fieldAttributes['Flags'];

            // If there are no flags or if there's a column exception then skip
            if ($flags === null || in_array('CE', $flags)) {
                continue;
            }

            // Do we have a Not Null flag?
            if (in_array('NN', $flags)) {
                // Do we not have a Primary Key flag?
                if (!in_array('PK', $flags)) {
                    $value = $parsedRequest[$columnName];
                    if ($value === null) {
                        $responseBody
                            ->registerParam(
                                'invalid',
                                $columnName,
                                $fieldAttributes['Type'],
                                "$columnName cannot be null, $value given"
                            );
                    }
                }
            }
        }
        return $responseBody;
    }

    /**
     * Check that there are aren't any extraneous request parameters
     * @param ResponseBody $responseBody
     * @param array $modelColumnAttributes
     * @param array $parsedRequest
     * @return ResponseBody
     */
    private function checkRequestHasNoUnexpectedParameters(
        ResponseBody $responseBody,
        array $modelColumnAttributes,
        array $parsedRequest
    ): ResponseBody {
        // Get any request parameters that are not in the model column names
        $outLiars = array_diff_key($parsedRequest, $modelColumnAttributes);
        if (count($outLiars) > 0) {
            // For each request parameter (key)
            foreach ($outLiars as $oLiar => $v) {
                // Is the key in the WHITE_LIST? If not register that the requested parameter/key is invalid.
                if (!in_array($oLiar, self::WHITE_LIST)) {
                    $responseBody->registerParam('invalid', $oLiar, null, "Unrecognized parameter: $oLiar");
                }
            }
        }
        return $responseBody;
    }

    /**
     * For any column attribute value for length check that the request parmeter value is under the length
     * @param ResponseBody $responseBody
     * @param array $modelColumnAttributes
     * @return ResponseBody
     */
    private function checkRequestMaxLength(ResponseBody $responseBody, array $modelColumnAttributes): ResponseBody {
        $parsedRequest = $responseBody->getParsedRequest();
        foreach ($modelColumnAttributes as $columnName => $fieldAttributes) {
            // Does the parameter exist in the request?
            if (key_exists($columnName, $parsedRequest)) {
                // Get the max length from the column attribute
                $len = $fieldAttributes['Length'];
                // Is the max length not null?
                if ($len !== null) {
                    // Get the request parameter value
                    $value = $parsedRequest[$columnName];
                    // Is the request not null?
                    if ($value !== null) {
                        $columnType = $fieldAttributes['Type'];

                        // If the column type is string then compare the length of the string to the max length
                        if ($columnType === 'string') {
                            // Does the request value exceed the maximum value?
                            if (strlen($value) > $len) {
                                $responseBody
                                    ->registerParam(
                                        'invalid',
                                        $columnName,
                                        'string',
                                        "$columnName exceeded max length of $len"
                                    );
                            }
                        }

                        // If the column type is int then compare the actual request value to the max length
                        if ($columnType === 'int') {
                            // Does the request value exceed the max length?
                            if ($value > $len) {
                                $responseBody
                                    ->registerParam(
                                        'invalid',
                                        $columnName,
                                        'int',
                                        "$columnName exceeded max length of $len"
                                    );
                            }
                        }
                    }
                }
            }
        }
        return $responseBody;
    }
}
