<?php

return [
    'driver' => env('SCOUT_DRIVER', 'meilisearch'),
    'prefix' => env('SCOUT_PREFIX', ''),
    'queue' => env('SCOUT_QUEUE', false),
    'after_commit' => true,
    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],
    'soft_delete' => false,
    'identify' => false,

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://127.0.0.1:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            'products' => [
                'searchableAttributes' => [
                    'name',
                    'category_label',
                    'short_description',
                    'description',
                    'category',
                    'availability',
                ],
                'filterableAttributes' => [
                    'category',
                    'availability',
                    'is_featured',
                ],
                'sortableAttributes' => [
                    'name',
                    'price',
                ],
            ],
        ],
    ],
];
