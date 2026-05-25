<?php

namespace App\Support;

class StockStatus
{
    /**
     * @return array{0: string, 1: string} [badge class, label]
     */
    public static function forQuantity(float $qty): array
    {
        if ($qty >= 50) {
            return ['ok', 'Normā'];
        }

        if ($qty >= 10) {
            return ['warn', 'Pārbaudīt'];
        }

        return ['low', 'Maz'];
    }
}
