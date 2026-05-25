<?php

namespace App\Http\Controllers;

use App\Services\StockOverviewService;
use App\Support\FirmaContext;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private StockOverviewService $overview)
    {
    }

    public function index(): View
    {
        $firmaId = FirmaContext::firmaId();
        $stats = $this->overview->statsForFirma($firmaId);
        $mainStock = $this->overview->mainStock($firmaId);
        $balances = $this->overview->topBalances($firmaId, $mainStock?->id);
        $recentDocuments = $this->overview->recentDocuments($firmaId);

        return view('dashboard', compact('stats', 'mainStock', 'balances', 'recentDocuments'));
    }
}
