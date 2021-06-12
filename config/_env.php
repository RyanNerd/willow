<?php
declare(strict_types=1);

use Dotenv\Dotenv;

const RULE_SET = [
    'DB_DRIVER' => ['required', 'notEmpty', 'allowedValues' => ['mysql', 'pgsql', 'sqlsrv', 'sqlite']],
    'DB_HOST' => ['required', 'notEmpty'],
    'DB_PORT' => ['required', 'notEmpty'],
    'DB_NAME' => ['required', 'notEmpty'],
    'DB_USER' => ['required', 'notEmpty'],
    'DB_PASSWORD' => ['required', 'notEmpty'],
    'DISPLAY_ERROR_DETAILS' => ['required', 'notEmpty'],
    'PRODUCTION' => ['allowedValues' => ['true', 'false']]
];

return [
    'ENV' => function () {
        $dotEnv = Dotenv::createImmutable(__DIR__ . '/../');
        $env = $dotEnv->load();

        // Assert that all keys in the RULE_SET are of type string
        assert(
            count(array_keys(RULE_SET)) ===
            count(array_filter(
                RULE_SET,
                function ($k) {
                    return is_string($k);
                },
                ARRAY_FILTER_USE_KEY
            )),
            'Invalid ruleset'
        );

        foreach (RULE_SET as $param => $item) {
            assert(is_array($item), 'Invalid ruleset');
            foreach ($item as $k => $v) {
                if (is_int($k)) {
                    assert(is_string($v), 'Invalid ruleset');
                    assert(in_array($v, ['notEmpty', 'isInteger', 'isBoolean', 'required']), 'Invalid ruleset');
                    if ($v === 'required') {
                        $dotEnv->required($param);
                    } else {
                        $dotEnv->ifPresent($param)->$v();
                    }
                    continue;
                }

                assert(is_string($k), 'Invalid ruleset');
                assert(in_array($k, ['allowedValues', 'allowedRegexValues']));
                switch ($k) {
                    case 'allowedValues':
                        assert(is_array($v), 'Invalid ruleset');
                        $dotEnv->ifPresent($param)->allowedValues($v);
                        break;

                    case 'allowedRegexValues':
                        assert(is_array($v) || is_string($v), 'Invalid ruleset');
                        if (!is_array($v)) {
                            $v = [$v];
                        }
                        array_map(function (string $exp) use ($dotEnv, $param) {
                            $dotEnv->ifPresent($param)->allowedRegexValues($exp);
                        }, $v);
                        break;
                }
            }
        }
        return $env;
    }
];
