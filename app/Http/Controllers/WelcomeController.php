<?php

namespace App\Http\Controllers;

use App\Services\StockOverviewService;
use App\Support\FirmaContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __construct(private StockOverviewService $overview)
    {
    }

    public function index(): View
    {
        $firma = FirmaContext::firma() ?? $this->overview->showcaseFirma();
        $firmaId = $firma?->id;

        $stats = $this->overview->statsForFirma($firmaId);
        $mainStock = $this->overview->mainStock($firmaId);
        $balances = $this->overview->topBalances($firmaId, $mainStock?->id);
        $recentDocuments = $this->overview->recentDocuments($firmaId);

        return view('welcome', compact('stats', 'mainStock', 'balances', 'recentDocuments', 'firma'));
    }
}
