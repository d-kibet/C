<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/sms/webhook/delivery', // Roberms SMS delivery webhook
        '/api/mpesa/callback',   // Safaricom M-Pesa STK Push callback
    ];
}
