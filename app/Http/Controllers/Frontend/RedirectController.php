<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $redirect = Redirect::query()->where('old_path', $request->getPathInfo())->first();

        abort_unless($redirect, 404);

        return redirect($redirect->new_path, $redirect->status_code);
    }
}
