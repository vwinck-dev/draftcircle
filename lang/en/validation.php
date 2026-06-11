<?php

declare(strict_types=1);

return [
    'accepted' => 'The :attribute field must be accepted.',
    'array' => 'The :attribute field must be an array.',
    'between' => ['numeric' => 'The :attribute field must be between :min and :max.', 'string' => 'The :attribute field must be between :min and :max characters.'],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute field confirmation does not match.',
    'date' => 'The :attribute field must be a valid date.',
    'email' => 'The :attribute field must be a valid email address.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute field must be a file.',
    'image' => 'The :attribute field must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'integer' => 'The :attribute field must be an integer.',
    'max' => ['numeric' => 'The :attribute field must not be greater than :max.', 'file' => 'The :attribute field must not be greater than :max kilobytes.', 'string' => 'The :attribute field must not be greater than :max characters.', 'array' => 'The :attribute field must not have more than :max items.'],
    'min' => ['numeric' => 'The :attribute field must be at least :min.', 'file' => 'The :attribute field must be at least :min kilobytes.', 'string' => 'The :attribute field must be at least :min characters.', 'array' => 'The :attribute field must have at least :min items.'],
    'nullable' => 'The :attribute field may be null.',
    'numeric' => 'The :attribute field must be a number.',
    'required' => 'The :attribute field is required.',
    'string' => 'The :attribute field must be a string.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute field must be a valid URL.',
    'attributes' => [
        'name' => 'name',
        'email' => 'email',
        'password' => 'password',
        'registration_number' => 'registration number',
        'title' => 'title',
        'author' => 'author',
        'description' => 'description',
    ],
];
