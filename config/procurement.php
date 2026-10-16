<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Indent Approval Defaults
    |--------------------------------------------------------------------------
    |
    | Approval levels are no longer hardcoded. Configure them under
    | Settings → Approval Workflow (approval_workflows table). The number of
    | sequences is unlimited and can differ per indent category.
    |
    | default_approval_levels is kept only as a legacy fallback for older
    | code paths that still reference it.
    |
    */
    'default_approval_levels' => 3,

    'approvals' => [
        // The person who raised the indent cannot approve it.
        'prevent_self_approval' => env('PROCUREMENT_PREVENT_SELF_APPROVAL', true),
        // Each sequence in the current cycle must be approved by a different person.
        'require_distinct_approvers' => env('PROCUREMENT_DISTINCT_APPROVERS', true),
    ],

    'notifications' => [
        'database' => env('PROCUREMENT_DATABASE_NOTIFICATIONS', true),
        'mail' => env('PROCUREMENT_MAIL_NOTIFICATIONS', false),
    ],

    'document_prefixes' => [
        'indent' => 'IDR',
        'fan' => 'FAN',
        'pr' => 'PR',
        'rfq' => 'RFQ',
        'po' => 'PO',
        'msu_po' => 'MSUPO',
        'grn' => 'GRN',
        'payreq' => 'PAY',
    ],

    'document_sequence_padding' => 4,

    'grn_document_types' => [
        'invoice',
        'delivery_challan',
        'photo',
        'inspection',
        'other',
    ],

    /*
    | Only this email may create GRNs (store login).
    */
    'grn_create_email' => env('PROCUREMENT_GRN_CREATE_EMAIL', 'store123@gmail.com'),
];
