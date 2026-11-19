<?php

namespace App\Http\Middleware;

use App\Models\Vendor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web');
        $vendors = $user?->vendors()
            ->wherePivot('status', 'active')
            ->wherePivot('role', 'owner')
            ->where('vendors.status', Vendor::STATUS_APPROVED)
            ->with('store')
            ->orderBy('vendors.id')
            ->get() ?? collect();

        if ($vendors->count() !== 1) {
            if ($vendors->count() > 1) {
                abort(403, 'Multiple vendor workspaces are not enabled for this account.');
            }

            $hasApplication = $user?->vendors()->exists();
            $route = $hasApplication ? 'website.account.seller.application' : 'website.account.seller.apply';

            return redirect()->route($route)->with('error', 'An approved seller account is required to access the vendor workspace.');
        }

        $request->attributes->set('vendor', $vendors->first());

        return $next($request);
    }
}
