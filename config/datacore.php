<?php

return [

    'cleaning_url' => env('CLEANING_API_URL', 'https://leonardo-alexander-data-cleaning.hf.space/process'),

    /*
     * Testing switch: skips manual identity review, so new accounts start verified
     * and any submitted verification is approved on the spot. Meant for usability
     * sessions where waiting on an admin would block the participant. Keep this
     * false in real deployments — it turns off identity checks entirely.
     */
    'auto_verify' => filter_var(env('AUTO_VERIFY_USERS', false), FILTER_VALIDATE_BOOLEAN),

    'cleaning_sync_limit' => (int) env('DATACORE_CLEANING_SYNC_LIMIT', 300),

    'clean2_fee' => (int) env('DATACORE_CLEAN2_FEE', 25000),

    'entry_reward' => (int) env('DATACORE_ENTRY_REWARD', 2000),

    'min_topup' => (int) env('DATACORE_MIN_TOPUP', 10000),

    'min_withdrawal' => (int) env('DATACORE_MIN_WITHDRAWAL', 50000),

    'max_topup' => (int) env('DATACORE_MAX_TOPUP', 10000000000),

    'max_price' => (int) env('DATACORE_MAX_PRICE', 10000000000),

    'platform_fee_rate' => (float) env('DATACORE_PLATFORM_FEE_RATE', 0.05),

    // IDR per 1 USD, used to display amounts when the user's currency is USD
    'usd_rate' => (float) env('DATACORE_USD_RATE', 16000),

    'admin' => [
        'name'     => env('ADMIN_NAME', 'Admin'),
        'email'    => env('ADMIN_EMAIL', 'admin@datacore.test'),
        'password' => env('ADMIN_PASSWORD') ?: null,
    ],

];
