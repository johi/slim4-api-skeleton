<?php
declare(strict_types=1);

namespace App\Application\Validation;

use JsonSchema\Validator;

class AppValidator
{
    const SCHEMA_BASE_PATH = '/api/schema/';

    public static function validate($data, $schemaFile): array
    {
        $errors = [];
        $validator = new Validator();
        $dataObject = json_decode(json_encode($data));
        $validator->validate($dataObject, (object)['$ref' => 'file://' . self::SCHEMA_BASE_PATH . $schemaFile]);
        if (!$validator->isValid()) {
            foreach ($validator->getErrors() as $error) {
                $errors[$error['property']] = $error['message'];
            }
        }
        return $errors;
    }
}