<?php

namespace App\Jobs;

use App\Http\Traits\ShopifyProductTrait;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VirtualOptions\Color;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use stdClass;
use Osiset\ShopifyApp\Contracts\Commands\Shop as IShopCommand;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;
use Osiset\ShopifyApp\Actions\CancelCurrentPlan;

class ProductsUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopifyProductTrait;

    /**
     * Shop's myshopify domain
     *
     * @var ShopDomain|string
     */
    public $domain;

    /**
     * The webhook data
     *
     * @var object
     */
    public $data;
    public $timeout = 600;
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @param string   $shopDomain The shop's myshopify domain.
     * @param stdClass $data       The webhook data (JSON decoded).
     *
     * @return void
     */
    public function __construct(string $domain, $data)
    {
        $this->domain = $domain;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @param IShopCommand      $shopCommand             The commands for shops.
     * @param IShopQuery        $shopQuery               The querier for shops.
     * @param CancelCurrentPlan $cancelCurrentPlanAction The action for cancelling the current plan.
     *
     * @return bool
     */
    public function handle(IShopCommand $shopCommand, IShopQuery $shopQuery, CancelCurrentPlan $cancelCurrentPlanAction): bool
    {
        $this->domain = ShopDomain::fromNative($this->domain);
        $shop = $shopQuery->getByDomain($this->domain);
        $payload = $this->data;
        $user = User::where('name', $shop->name)->first();
        $this->syncWithDatabase($user, $payload);
        return true;
    }
}
