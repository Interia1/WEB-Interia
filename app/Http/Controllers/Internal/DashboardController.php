<?php

namespace App\Http\Controllers\Internal;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('internal.dashboard', [
            'productCount' => Product::count(),
            'customerCount' => User::where('role', UserRole::Customer)->count(),
            'workerCount' => User::whereIn('role', [
                UserRole::ContentManager,
                UserRole::Administrator,
            ])->count(),
        ]);
    }
}
