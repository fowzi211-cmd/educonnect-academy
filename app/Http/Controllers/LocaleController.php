<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch the interface language and persist it for the session
     * (and on the user's profile, so it follows them across devices).
     */
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $supported = array_keys(config('platform.locales'));

        abort_unless(in_array($locale, $supported, true), 404);

        $request->session()->put('locale', $locale);

        if ($user = $request->user()) {
            $user->profile()->updateOrCreate([], ['preferred_language' => $locale]);
        }

        return back();
    }
}
