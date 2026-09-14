<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use App\Models\User;
use App\Notifications\SubscriptionDueReminder;
use App\Notifications\SubscriptionOverdue;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessSubscriptions extends Command
{
    protected $signature = 'subscription:process';

    protected $description = 'Proses billing subscription merchant: buat tagihan bulanan, tandai overdue, dan kirim reminder.';

    public function handle(): void
    {
        $svc = app(SubscriptionService::class);

        $overdueMarked = $svc->markExpiredPending();
        $this->info("Tagihan yang menjadi overdue: {$overdueMarked}");

        foreach (Merchant::where('billing_plan', 'subscription')->get() as $merchant) {
            $svc->ensureCurrentBill($merchant);
        }

        $this->sendOverdueNotifications($svc);
        $this->sendDueReminders($svc);

        $this->info('Selesai.');
    }

    private function sendOverdueNotifications(SubscriptionService $svc): void
    {
        $overdueMerchants = Merchant::where('billing_plan', 'subscription')
            ->where(fn ($q) => $q->whereNull('subscription_until')->orWhere('subscription_until', '<', now()))
            ->get();

        foreach ($overdueMerchants as $merchant) {
            foreach ($this->merchantUsers($merchant) as $user) {
                if ($this->notifiedToday($user, 'subscription_overdue')) {
                    continue;
                }
                $user->notify(new SubscriptionOverdue($merchant));
            }
        }
    }

    private function sendDueReminders(SubscriptionService $svc): void
    {
        $merchants = Merchant::where('billing_plan', 'subscription')->get();

        foreach ($merchants as $merchant) {
            $bill = $svc->currentBill($merchant);
            if (!$bill || $bill->isPaid()) {
                continue;
            }

            $daysLeft = (int) now()->startOfDay()->diffInDays(Carbon::parse($bill->period_end)->startOfDay());

            if (!in_array($daysLeft, [7, 3, 1, 0], true)) {
                continue;
            }

            foreach ($this->merchantUsers($merchant) as $user) {
                if ($this->notifiedToday($user, 'subscription_due_reminder')) {
                    continue;
                }
                $user->notify(new SubscriptionDueReminder(
                    $merchant,
                    Carbon::parse($bill->period_end)->format('d M Y'),
                    number_format((float) $bill->amount, 0, ',', '.')
                ));
            }
        }
    }

    private function merchantUsers(Merchant $merchant): array
    {
        return User::whereIn('id', app(SubscriptionService::class)->getAccountUserIds($merchant))->get()->all();
    }

    private function notifiedToday(User $user, string $type): bool
    {
        return $user->notifications()
            ->where('data->type', $type)
            ->whereDate('created_at', today())
            ->exists();
    }
}