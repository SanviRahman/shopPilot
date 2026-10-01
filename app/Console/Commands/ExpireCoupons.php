<?php

namespace App\Console\Commands;

use App\Services\CouponService;
use Illuminate\Console\Command;

class ExpireCoupons extends Command
{
    protected $signature = 'coupons:expire';
    protected $description = 'Mark active coupons as expired when their end date has passed';

    public function handle(CouponService $couponService): int
    {
        $count = $couponService->expirePastActiveCoupons();
        $this->info(sprintf('%d coupon(s) marked as expired.', $count));
        return self::SUCCESS;
    }
}
