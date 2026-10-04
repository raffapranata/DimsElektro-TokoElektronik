<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Provides $this->authorize(...) so controllers can enforce policies.
    use AuthorizesRequests;
}
