<?php

namespace App\Http\Requests;

/**
 * Product updates deliberately accept the same payload as creation: there is no
 * unique column to exclude, and keeping one rule set stops the two paths from
 * drifting apart.
 */
class UpdateProductRequest extends StoreProductRequest
{
    //
}
