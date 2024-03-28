<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Traits\ShopifyProductTrait;

class GetProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopifyProductTrait;
    protected $user , $product, $usdRate, $egpRate;
    /**
     * Create a new job instance.
     */
    public function __construct($user, $product, $usdRate, $egpRate)
    {
        //
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
        $this->storeWithDatabase($this->user, $this->product, $this->usdRate, $this->egpRate);
        return true;
    }
}
