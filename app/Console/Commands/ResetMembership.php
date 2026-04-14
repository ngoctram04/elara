<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ResetMembership extends Command
{
    protected $signature = 'membership:reset {--force-test : Giả lập ngày 01/01/2027 để demo}';
    protected $description = 'Reset hạng thành viên vào đầu năm';

    public function handle(): int
    {
        $now = now();

        // TEST: giả lập năm 2027 php artisan membership:reset --force-test
        // if ($this->option('force-test')) {
        //     $now = now()->setDate(2027, 1, 1);
        //     $this->info('Đang chạy chế độ TEST: giả lập ngày 01/01/2027.');
        // }

        // Chỉ chạy vào ngày 01/01 php artisan membership:reset
        if ($now->month != 1 || $now->day != 1) {
            $this->info('Hôm nay không phải ngày 01/01, bỏ qua reset hạng thành viên.');
            return self::SUCCESS;
        }

        // Reset user chưa được cập nhật năm mới
        $updated = User::query()
            ->where('membership_year', '<', $now->year)
            ->update([
                'yearly_spent'    => 0,
                'member_level'    => 'bronze',
                'membership_year' => $now->year,
            ]);

        $this->info("Đã reset hạng thành viên cho {$updated} người dùng.");

        return self::SUCCESS;
    }
}