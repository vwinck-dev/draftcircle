<?php

declare(strict_types=1);

return [
    'accepted' => 'O campo :attribute deve ser aceito.',
    'array' => 'O campo :attribute deve ser uma lista.',
    'between' => ['numeric' => 'O campo :attribute deve estar entre :min e :max.', 'string' => 'O campo :attribute deve ter entre :min e :max caracteres.'],
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
    'confirmed' => 'A confirmação do campo :attribute não corresponde.',
    'date' => 'O campo :attribute deve ser uma data válida.',
    'email' => 'O campo :attribute deve ser um endereço de e-mail válido.',
    'exists' => 'O valor selecionado para :attribute é inválido.',
    'file' => 'O campo :attribute deve ser um arquivo.',
    'image' => 'O campo :attribute deve ser uma imagem.',
    'in' => 'O valor selecionado para :attribute é inválido.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'max' => ['numeric' => 'O campo :attribute não pode ser maior que :max.', 'file' => 'O campo :attribute não pode ser maior que :max quilobytes.', 'string' => 'O campo :attribute não pode ter mais que :max caracteres.', 'array' => 'O campo :attribute não pode ter mais que :max itens.'],
    'min' => ['numeric' => 'O campo :attribute deve ser pelo menos :min.', 'file' => 'O campo :attribute deve ter pelo menos :min quilobytes.', 'string' => 'O campo :attribute deve ter pelo menos :min caracteres.', 'array' => 'O campo :attribute deve ter pelo menos :min itens.'],
    'nullable' => 'O campo :attribute pode ser nulo.',
    'numeric' => 'O campo :attribute deve ser um número.',
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute deve ser um texto.',
    'unique' => 'O valor informado para :attribute já está em uso.',
    'uploaded' => 'Falha ao enviar o campo :attribute.',
    'url' => 'O campo :attribute deve ser uma URL válida.',
    'attributes' => [
        'name' => 'nome',
        'email' => 'e-mail',
        'password' => 'senha',
        'registration_number' => 'número de matrícula',
        'title' => 'título',
        'author' => 'autor',
        'description' => 'descrição',
    ],
];
