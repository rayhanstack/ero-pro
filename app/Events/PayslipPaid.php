<?php

namespace App\Events;

use App\Models\Payslip;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayslipPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     * Hook: When a payslip is marked as paid, listeners can create a Finance Expense transaction.
     */
    public function __construct(
        public Payslip $payslip
    ) {}
}
