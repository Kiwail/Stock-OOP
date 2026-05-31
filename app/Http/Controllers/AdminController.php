<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockDocument;
use App\Models\User;
use App\Support\FirmaContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $firmaId = FirmaContext::firmaId();

        $users = User::query()
            ->whereHas('firmas', fn ($query) => $query->where('firma.id', $firmaId))
            ->with(['firmas' => fn ($query) => $query->where('firma.id', $firmaId)])
            ->orderBy('name')
            ->get();

        $stats = [
            'users' => $users->count(),
            'admins' => $users->filter(fn (User $user) => $this->roleValue($user) === UserRole::Admin->value)->count(),
            'operators' => $users->filter(fn (User $user) => $this->roleValue($user) === UserRole::Operator->value)->count(),
            'products' => Product::query()->where('deleted', false)->count(),
            'warehouses' => Stock::query()->where('firma_id', $firmaId)->where('deleted', false)->count(),
            'documents' => StockDocument::query()->where('firma_id', $firmaId)->where('deleted', false)->count(),
            'drafts' => StockDocument::query()
                ->where('firma_id', $firmaId)
                ->where('deleted', false)
                ->where('posted', false)
                ->where('cancelled', false)
                ->count(),
        ];

        return view('admin.index', compact('users', 'stats'));
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        $firma = FirmaContext::firma();
        abort_unless($firma && $user->firmas()->where('firma.id', $firma->id)->exists(), 404);

        $attributes = $request->validate([
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        $newRole = UserRole::from($attributes['role']);

        if ($newRole !== UserRole::Admin && $this->isLastAdmin($user->id, $firma->id)) {
            return back()->with('error', 'Nevar noņemt tiesības pēdējam administratoram.');
        }

        $firma->users()->updateExistingPivot($user->id, ['role' => $newRole->value]);

        return back()->with('success', 'Lietotāja tiesības atjauninātas.');
    }

    private function roleValue(User $user): ?string
    {
        return $user->firmas->first()?->pivot?->role;
    }

    private function isLastAdmin(int $userId, int $firmaId): bool
    {
        $adminCount = User::query()
            ->whereHas('firmas', fn ($query) => $query
                ->where('firma.id', $firmaId)
                ->where('firma_user.role', UserRole::Admin->value))
            ->count();

        $isCurrentAdmin = User::query()
            ->whereKey($userId)
            ->whereHas('firmas', fn ($query) => $query
                ->where('firma.id', $firmaId)
                ->where('firma_user.role', UserRole::Admin->value))
            ->exists();

        return $isCurrentAdmin && $adminCount <= 1;
    }
}
