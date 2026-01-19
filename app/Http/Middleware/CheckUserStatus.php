<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user is suspended
            if (isset($user->status) && $user->status === 'suspended') {
                // Log the user out
                Auth::logout();

                // Invalidate the session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect to login with error message
                $notification = array(
                    'message' => 'Your account has been suspended. Please contact the administrator.',
                    'alert-type' => 'error'
                );

                return redirect()->route('login')->with($notification);
            }
        }

        return $next($request);
    }
}
