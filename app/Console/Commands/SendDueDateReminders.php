<?php

// app/Console/Commands/SendDueDateReminders.php
namespace App\Console\Commands;

use App\Models\PartyUser;
use App\Notifications\DueDateReminderNotification;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendDueDateReminders extends Command
{
    protected $signature = 'reminders:due-dates';
    protected $description = 'Send reminders for party_users due_date at 10,5,4,3,2 days before and on the day';

    public function handle(): int
    {
        // Work in IST regardless of server tz
        $today = CarbonImmutable::today('Asia/Kolkata');

        // Days offsets you asked for (0 = today)
        $offsets = [0, 2, 3, 4, 5, 10];

        // Build the exact target dates
        $targetDates = collect($offsets)->map(fn($d) => $today->addDays($d)->toDateString())->all();

        // Only Active + not deleted (based on your enums)
        $users = PartyUser::query()
            ->whereNotNull('due_date')
            ->whereIn(DB::raw('DATE(due_date)'), $targetDates)
            ->where('status', 'Active')
            ->where('is_delete', 'No')
            ->get();

        foreach ($users as $u) {
            $daysLeft = $today->diffInDays(CarbonImmutable::parse($u->due_date, 'Asia/Kolkata'), false);
            if (in_array($daysLeft, $offsets, true)) {
                // Send a database notification (or mail/SMS if configured)
                $u->notify(new DueDateReminderNotification($daysLeft));
            }
        }

        $this->info("Processed {$users->count()} users.");
        return self::SUCCESS;
    }
}
