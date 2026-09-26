<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protected course media may only be loaded by the page's own players and
 * viewers. Browsers label every request with Sec-Fetch-Dest; a request typed
 * into the address bar, opened in a new tab, or put in a frame/embed is
 * refused. This is a deterrent, not DRM: a determined user can still copy a
 * request from the developer tools.
 */
class BlockDirectMediaAccess
{
    private const BLOCKED_DESTINATIONS = ['document', 'iframe', 'frame', 'embed', 'object'];

    public function handle(Request $request, Closure $next): Response
    {
        $destination = strtolower((string) $request->header('Sec-Fetch-Dest'));

        abort_if(in_array($destination, self::BLOCKED_DESTINATIONS, true), 403);

        return $next($request);
    }
}
