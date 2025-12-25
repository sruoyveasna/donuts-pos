<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = ['name','unit','low_alert_qty','current_qty','last_restocked_at'];

    protected $casts = [
        'low_alert_qty'     => 'decimal:3',
        'current_qty'       => 'decimal:3',
        'last_restocked_at' => 'datetime',
    ];

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    // Helpers
    public function isLow(): bool
    {
        return (float)$this->current_qty <= (float)($this->low_alert_qty ?? 0);
    }

    public function addStock(float $qty, ?string $note = null, ?int $userId = null): void
    {
        $this->adjustStock($qty, 'restock', $note, $userId);
        $this->forceFill(['last_restocked_at' => now()])->save();
    }

    public function useStock(float $qty, ?string $note = null, ?int $userId = null): void
    {
        $this->adjustStock(-abs($qty), 'consume', $note, $userId);
    }

    public function adjustStock(float $delta, string $reason = 'adjust', ?string $note = null, ?int $userId = null): void
    {
        $new = max(0, (float)$this->current_qty + $delta);
        $this->update(['current_qty' => $new]);

        if (class_exists(\App\Models\InventoryMovement::class)) {
            $this->movements()->create([
                'delta_qty' => $delta,
                'reason'    => $reason,
                'note'      => $note,
                'user_id'   => $userId,
            ]);
        }
    }
}
