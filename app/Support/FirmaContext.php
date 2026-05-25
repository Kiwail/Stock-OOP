<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\Firma;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FirmaContext
{
    public static function firma(): ?Firma
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return null;
        }

        $firmaId = session('firma_id');

        if ($firmaId) {
            return $user->firmas()->where('firma.id', $firmaId)->first();
        }

        $firma = $user->firmas()->first();

        if ($firma) {
            session(['firma_id' => $firma->id]);
        }

        return $firma;
    }

    public static function role(): ?UserRole
    {
        $firma = self::firma();
        $user = Auth::user();

        if (! $firma || ! $user instanceof User) {
            return null;
        }

        $role = $user->firmas()->where('firma.id', $firma->id)->first()?->pivot?->role;

        return $role ? UserRole::tryFrom($role) : null;
    }

    public static function isAdmin(): bool
    {
        return self::role() === UserRole::Admin;
    }

    public static function firmaId(): ?int
    {
        return self::firma()?->id;
    }
}
