<?php

return [

    'storage' => [

        'disk'          => 'local',

        'printers_path' => 'star-cloud-prnt/printers',
    
        'queue_path'    => 'star-cloud-prnt/queue',

    ],

    'timeout'       => 60,  // seconds

    'supported_encordings' => [
        'application/vnd.star.line' => [
            'buffer_class' => \Onkbear\StarCloudPRNT\Buffers\LineBuffer::class,
            'extension' => 'slt',
        ],
        'application/vnd.star.linematrix' => [
            'buffer_class' => \Onkbear\StarCloudPRNT\Buffers\LineMatrixBuffer::class,
            'extension' => 'slm',
        ],
        'application/vnd.star.starprnt' => [
            'buffer_class' => \Onkbear\StarCloudPRNT\Buffers\StarPrntBuffer::class,
            'extension' => 'spt',
        ],
        'text/plain' => [
            'buffer_class' => \Onkbear\StarCloudPRNT\Buffers\TextPlainBuffer::class,
            'extension' => 'txt',
        ],
    ],
];
