<?php

return [

    /*
     * Server-side MIME allow-list. Validated against the *actual* file
     * content (finfo), never the client-supplied extension or the
     * browser-reported Content-Type alone.
     */
    'allowed_mime_types' => [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'text/csv' => 'csv',
    ],

    'max_size_kb' => 10240, // 10MB

];
