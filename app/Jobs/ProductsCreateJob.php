<?php

namespace App\Jobs;

use stdClass;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Bus\Queueable;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use App\Http\Traits\ShopifyProductTrait;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Osiset\ShopifyApp\Actions\CancelCurrentPlan;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;
use Osiset\ShopifyApp\Contracts\Commands\Shop as IShopCommand;

class ProductsCreateJob implements ShouldQueue
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
        $rates = Http::get('http://data.fixer.io/api/latest?access_key=42e27abfba793b7bd010a85b484d8dce&base=EUR&symbols=USD');
        $rates = $rates->json();
        $usdRate = $rates['rates']['USD'];
        $rates = Http::get('http://data.fixer.io/api/latest?access_key=42e27abfba793b7bd010a85b484d8dce&base=EUR&symbols=EGP');
        $rates = $rates->json();
        $eurRate = 1 / $usdRate;
        $egpRate = $eurRate * $rates['rates']['EGP'];
        $this->domain = ShopDomain::fromNative($this->domain);
        $shop = $shopQuery->getByDomain($this->domain);
        $payload = $this->data;
        $user = User::where('name', $shop->name)->first();
        $this->storeWithDatabase($user, $payload, $usdRate, $egpRate);
        return true;
    }
}
