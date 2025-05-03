<?php

return [
    'sources' => [
        'simply-hired' => [
            'rate_limit' => 100,
            'concurrent_jobs' => 5,
            'retry_after' => 60,
        ],
    ]
];
