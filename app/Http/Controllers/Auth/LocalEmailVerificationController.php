<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocalEmailVerificationController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $localLogMailer = app()->environment('local') && config('mail.default') === 'log';

        abort_unless(app()->environment('testing') || $localLogMailer, 404);

        $user = $request->user();

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        $route = $user->isInternal() ? 'internal.dashboard' : 'customer.orders';

        return redirect()->route($route)->with('status', 'Testovací e-mail bol lokálne overený.');
    }
}
