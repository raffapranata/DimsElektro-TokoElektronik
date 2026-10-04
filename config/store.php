<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Low Stock Threshold
    |--------------------------------------------------------------------------
    |
    | Products at or below this stock level are listed on the admin dashboard
    | so they can be restocked before they sell out.
    |
    */

    'low_stock_threshold' => (int) env('STORE_LOW_STOCK_THRESHOLD', 5),

    /*
    |--------------------------------------------------------------------------
    | Shipping
    |--------------------------------------------------------------------------
    |
    | Flat shipping fee applied at checkout. Defaults to free shipping, which is
    | what the development seed data reflects.
    |
    */

    'shipping_cost' => (float) env('STORE_SHIPPING_COST', 0),

];
