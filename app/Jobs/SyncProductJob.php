<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Traits\ShopifyProductTrait;

class SyncProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopifyProductTrait;
    protected $product, $user;
    /**
     * Create a new job instance.
     */
    public function __construct($product, $user)
    {
        $this->product = $product;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): bool
    {
        $this->syncWithShopify($this->product, $this->user);
        return true;
    }
}
