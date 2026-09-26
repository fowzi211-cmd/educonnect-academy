<?php

namespace App\Livewire\Admin\Finance;

use App\Models\Refund;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class RevenueReport extends Component
{
    public string $startDate;

    public string $endDate;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function render()
    {
        $range = [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay(),
        ];

        $paidTransactions = Transaction::query()
            ->where('transactions.status', Transaction::STATUS_SUCCEEDED)
            ->whereBetween('transactions.paid_at', $range);

        $grossByCurrency = (clone $paidTransactions)
            ->selectRaw('transactions.currency as currency, sum(transactions.amount) as total, count(*) as count')
            ->groupBy('transactions.currency')
            ->get();

        $refundedByCurrency = Refund::query()
            ->whereBetween('processed_at', $range)
            ->join('transactions', 'transactions.id', '=', 'refunds.transaction_id')
            ->selectRaw('transactions.currency as currency, sum(refunds.amount) as total')
            ->groupBy('transactions.currency')
            ->get()
            ->keyBy('currency');

        $revenueByCurrency = $grossByCurrency->map(fn ($row) => (object) [
            'currency' => $row->currency,
            'gross' => (float) $row->total,
            'refunded' => (float) ($refundedByCurrency[$row->currency]->total ?? 0),
            'net' => (float) $row->total - (float) ($refundedByCurrency[$row->currency]->total ?? 0),
            'count' => $row->count,
        ]);

        $byCourse = (clone $paidTransactions)
            ->join('courses', 'courses.id', '=', 'transactions.course_id')
            ->selectRaw('courses.id, courses.title, courses.currency, sum(transactions.amount) as total, count(*) as count')
            ->groupBy('courses.id', 'courses.title', 'courses.currency')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('livewire.admin.finance.revenue-report', [
            'revenueByCurrency' => $revenueByCurrency,
            'byCourse' => $byCourse,
            'newSubscriptions' => (clone $paidTransactions)->count(),
        ]);
    }
}
