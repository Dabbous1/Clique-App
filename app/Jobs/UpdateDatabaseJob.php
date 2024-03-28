<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Traits\ShopifyProductTrait;

class UpdateDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopifyProductTrait;
    protected $user,$product, $usdRate, $egpRate;
    /**
     * Create a new job instance.
     */
    public function __construct($user , $product, $usdRate, $egpRate)
    {
        $this->user = $user;
        $this->product = $product;
        $this->usdRate = $usdRate;
        $this->egpRate = $egpRate;
    }

    /**
     * Execute the job.
     */
    public function handle(): bool
    {
        $this->updateDatabase($this->product, $this->user, $this->usdRate, $this->egpRate);
        return true;
    }
}
