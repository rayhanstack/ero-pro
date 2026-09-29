<?php

namespace App\Models;

use App\Enums\InvoiceStatusEnum;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'invoice_number',
        'client_id',
        'project_id',
        'currency_id',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_type',
        'discount_value',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'status',
        'notes',
        'terms',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'status' => InvoiceStatusEnum::class,
    ];

    public function getTaxAttribute(): float
    {
        return (float) ($this->attributes['tax_amount'] ?? 0.00);
    }

    public function getDiscountAttribute(): float
    {
        return (float) ($this->attributes['discount_amount'] ?? $this->attributes['discount_value'] ?? 0.00);
    }

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class, 'invoice_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'invoice_id');
    }

    /* -------------------------------------------------------------------------- */
    /*                                   Scopes                                   */
    /* -------------------------------------------------------------------------- */

    public function scopeStatus(Builder $query, string|InvoiceStatusEnum $status): Builder
    {
        $val = $status instanceof InvoiceStatusEnum ? $status->value : $status;

        return $query->where('status', $val);
    }

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForProject(Builder $query, int $projectId): Builder
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('client', function (Builder $clientQ) use ($search) {
                    $clientQ->where('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%");
                });
        });
    }

    /* -------------------------------------------------------------------------- */
    /*                               Helpers                                      */
    /* -------------------------------------------------------------------------- */

    /**
     * Check if invoice is currently overdue.
     */
    public function isOverdue(): bool
    {
        if ($this->status === InvoiceStatusEnum::PAID || $this->status === InvoiceStatusEnum::CANCELLED) {
            return false;
        }

        return $this->due_date && $this->due_date->isPast() && (float) $this->due_amount > 0;
    }

    /**
     * Recalculate invoice totals and balance due.
     */
    public function recalculateTotals(): void
    {
        if ($this->items()->exists()) {
            $subtotal = 0.00;
            foreach ($this->items as $item) {
                $subtotal += (float) ($item->total_amount ?? ($item->quantity * $item->unit_price));
            }

            $taxRate = (float) $this->tax_rate;
            $taxAmount = $taxRate > 0 ? round(($subtotal * $taxRate) / 100, 2) : (float) $this->tax_amount;

            $discountValue = (float) $this->discount_value;
            $discountAmount = 0.00;
            if ($this->discount_type === 'percent' && $discountValue > 0) {
                $discountAmount = round(($subtotal * $discountValue) / 100, 2);
            } elseif ($discountValue > 0) {
                $discountAmount = min($subtotal, $discountValue);
            } else {
                $discountAmount = (float) $this->discount_amount;
            }

            $totalAmount = max(0, round($subtotal + $taxAmount - $discountAmount, 2));
        } else {
            $subtotal = (float) $this->subtotal;
            $taxAmount = (float) $this->tax_amount;
            $discountAmount = (float) $this->discount_amount;
            $totalAmount = (float) ($this->total_amount > 0 ? $this->total_amount : max(0, $subtotal + $taxAmount - $discountAmount));
        }

        $paidAmount = (float) $this->payments()->sum('amount');
        $dueAmount = max(0, round($totalAmount - $paidAmount, 2));

        $status = $this->status;
        if ($paidAmount >= $totalAmount && $totalAmount > 0) {
            $status = InvoiceStatusEnum::PAID;
        } elseif ($paidAmount > 0 && $dueAmount > 0) {
            $status = InvoiceStatusEnum::PARTIAL;
        } elseif ($status === InvoiceStatusEnum::PAID && $dueAmount > 0) {
            $status = InvoiceStatusEnum::SENT;
        }

        $this->updateQuietly([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'due_amount' => $dueAmount,
            'status' => $status,
        ]);
    }
}
