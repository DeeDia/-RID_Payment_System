<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;


 //This is the Inertia equivalent of View::share().
 
class HandleInertiaRequests extends Middleware
{
    
    protected $rootView = 'app';

    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            //password hash or id_number
            'auth' => [
                'user' => $request->user() ? [
                    'id'             => $request->user()->id,
                    'full_name'      => $request->user()->full_name,
                    'account_number' => $request->user()->account_number
                        ? '••••' . substr($request->user()->account_number, -4)
                        : null,
                    'employee_id'    => $request->user()->employee_id,
                    'role'           => $request->user()->role,
                ] : null,
            ],

            // Flash messages (success / error) forwarded to React components
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
        ]);
    }
}
