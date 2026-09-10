<?php

namespace App\Core;

final class Validator
{
    public static function user(array $data, bool $passwordRequired = true): array
    {
        $errors = [];

        foreach (['first_name', 'last_name', 'email', 'role_id'] as $field) {
            if (($data[$field] ?? '') === '') {
                $errors[$field] = 'This field is required.';
            }
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Use a valid email address.';
        }

        if ($passwordRequired && strlen((string)($data['password'] ?? '')) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if (!$passwordRequired && ($data['password'] ?? '') !== '' && strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        return $errors;
    }
}
