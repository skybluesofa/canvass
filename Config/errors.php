<?php
return [
    [
        'message' => "There is no Starting Address set. The map may not work properly until this setting is updated.",
        'type' => 'warning',
        'when_on' => ['is_active'],
        'fields_should_not_be_empty' => ['starting_address'],
        'one_field_should_be_filled' => [],
        'tab' => '',
    ],
    [
        'message' => "There is no Bing Maps API Key set. The map will not work properly until this setting is updated.",
        'type' => 'error',
        'when_on' => ['is_active'],
        'fields_should_not_be_empty' => ['bing_api_key'],
        'one_field_should_be_filled' => [],
        'tab' => '',
    ],
];
