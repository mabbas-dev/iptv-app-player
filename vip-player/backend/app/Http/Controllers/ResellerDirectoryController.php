<?php

namespace App\Http\Controllers;

use App\Models\Reseller;
use Illuminate\View\View;

class ResellerDirectoryController extends Controller
{
    public function index(): View
    {
        $resellers = Reseller::query()
            ->where('status', Reseller::STATUS_ACTIVE)
            ->where('show_in_directory', true)
            ->whereNotNull('store_name')
            ->orderBy('store_name')
            ->get();

        return view('resellers.index', compact('resellers'));
    }

    public function show(string $slug): View
    {
        $reseller = Reseller::query()
            ->where('store_slug', $slug)
            ->where('status', Reseller::STATUS_ACTIVE)
            ->where('show_in_directory', true)
            ->firstOrFail();

        return view('resellers.show', compact('reseller'));
    }
}
