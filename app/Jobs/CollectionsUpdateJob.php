<?php

namespace App\Jobs;

use App\Models\Collection;
use App\Models\User;
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

class CollectionsUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        // $shopId = $shop->getId();

        // Log::info(json_encode("update collection job"));
        // Log::info(json_encode($shop));

        $user = User::where('name', $shop->name)->first();
        // sync_products($shop);

        $collection = Collection::updateOrCreate([
            'collection_id' => $payload->id
        ], [
            'collection_title' => $payload->title,
            'collection_handle' => $payload->handle,
            'collection_image' => "https://phpstack-1010162-3567467.cloudwaysapps.com/images/placeholder_logo.png",
            'user_id' => $user->id,
            'collection_created_at' => $payload->published_at
        ]);

        // Log::info(json_encode($collection));
        return true;
    }
}
