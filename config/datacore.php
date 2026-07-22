<?php

return [

    'cleaning_url' => env('CLEANING_API_URL', 'https://leonardo-alexander-data-cleaning.hf.space/process'),

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

];
