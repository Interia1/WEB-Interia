<?php

return [
    'accepted' => 'Pole :attribute musí byť prijaté.',
    'alpha_dash' => 'Pole :attribute môže obsahovať iba písmená, čísla, pomlčky a podčiarkovníky.',
    'boolean' => 'Pole :attribute musí mať hodnotu áno alebo nie.',
    'confirmed' => 'Potvrdenie poľa :attribute sa nezhoduje.',
    'email' => 'Pole :attribute musí obsahovať platnú e-mailovú adresu.',
    'max' => [
        'numeric' => 'Pole :attribute nesmie byť väčšie ako :max.',
        'string' => 'Pole :attribute môže obsahovať najviac :max znakov.',
    ],
    'min' => [
        'numeric' => 'Pole :attribute musí byť najmenej :min.',
        'string' => 'Pole :attribute musí obsahovať najmenej :min znakov.',
    ],
    'numeric' => 'Pole :attribute musí byť číslo.',
    'required' => 'Pole :attribute je povinné.',
    'size' => [
        'string' => 'Pole :attribute musí obsahovať presne :size znaky.',
    ],
    'string' => 'Pole :attribute musí byť text.',
    'unique' => 'Táto hodnota poľa :attribute sa už používa.',

    'custom' => [
        'email' => [
            'unique' => 'Účet s týmto e-mailom už existuje.',
        ],
    ],

    'attributes' => [
        'name' => 'meno a priezvisko',
        'email' => 'e-mail',
        'password' => 'heslo',
        'password_confirmation' => 'potvrdenie hesla',
        'gdpr_consent' => 'súhlas so spracovaním osobných údajov',
        'terms_accepted' => 'súhlas s obchodnými podmienkami',
        'marketing_consent' => 'marketingový súhlas',
        'role' => 'rola',
        'slug' => 'slug',
        'category' => 'kód kategórie',
        'category_label' => 'názov kategórie',
        'price' => 'cena',
        'currency' => 'mena',
        'availability' => 'dostupnosť',
        'short_description' => 'krátky popis',
        'description' => 'podrobný popis',
        'image_path' => 'cesta k obrázku',
    ],
];