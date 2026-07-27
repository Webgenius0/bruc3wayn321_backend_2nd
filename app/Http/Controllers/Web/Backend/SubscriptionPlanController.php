<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionPlanController extends Controller
{
    public function index(): Response
    {
        $projectId = config('services.revenuecat.project_id');
        $apiKey = config('services.revenuecat.secret_api_key');

        // dd(
        //     $projectId,
        //     $apiKey
        // );

        if (blank($projectId) || blank($apiKey)) {
            return Inertia::render('backend/subscription-plans/index', [
                'plans' => [],
                'connectionError' => 'RevenueCat is not configured. Add REVENUECAT_PROJECT_ID and REVENUECAT_SECRET_API_KEY to your environment.',
            ]);
        }

        try {
            $response = Http::baseUrl('https://api.revenuecat.com/v2')
                ->acceptJson()
                ->withToken($apiKey)
                ->timeout(15)
                ->get("/projects/{$projectId}/offerings", [
                    'limit' => 100,
                    'expand' => ['items.package', 'items.package.product'],
                ]);

            if ($response->failed()) {
                report("RevenueCat offerings request failed with status {$response->status()}.");

                return Inertia::render('backend/subscription-plans/index', [
                    'plans' => [],
                    'connectionError' => 'Unable to retrieve plans from RevenueCat. Verify the project ID and API-key permissions.',
                ]);
            }

            return Inertia::render('backend/subscription-plans/index', [
                'plans' => collect($response->json('items', []))
                    ->flatMap(fn(array $offering) => $this->plansFromOffering($offering))
                    ->values()
                    ->all(),
                'connectionError' => null,
            ]);
        } catch (ConnectionException $exception) {
            report($exception);

            return Inertia::render('backend/subscription-plans/index', [
                'plans' => [],
                'connectionError' => 'RevenueCat could not be reached. Please try again shortly.',
            ]);
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function plansFromOffering(array $offering): array
    {
        $metadata = Arr::get($offering, 'metadata', []);
        $features = Arr::get($metadata, 'features', []);

        if (is_string($features)) {
            $features = array_filter(array_map('trim', explode(',', $features)));
        }

        return collect(Arr::get($offering, 'packages.items', []))
            ->map(function (array $package) use ($offering, $metadata, $features): array {
                $products = Arr::get($package, 'products.items', []);
                $product = Arr::get($products, '0.product', []);
                $price = Arr::get($product, 'indicative_price');
                $micros = (int) Arr::get($price, 'amount_micros', 0);

                return [
                    'id' => $package['id'],
                    'name' => $package['display_name'] ?: $offering['display_name'] ?: $package['lookup_key'],
                    'price' => $micros > 0 ? number_format($micros / 1_000_000, 2) : null,
                    'currency' => Arr::get($price, 'currency'),
                    'duration' => $this->duration($package['lookup_key'] ?? '', $product),
                    'type' => Arr::get($product, 'type', 'subscription'),
                    'bonus' => Arr::get($metadata, 'bonus'),
                    'features' => is_array($features)
                        ? array_values(array_map('strval', $features))
                        : [],
                    'is_active' => ($product['state'] ?? 'active') === 'active',
                    'offering_name' => $offering['display_name'] ?? $offering['id'],
                    'product_identifier' => Arr::get($product, 'store_identifier'),
                ];
            })
            ->all();
    }

    private function duration(string $lookupKey, array $product): string
    {
        $period = Arr::get($product, 'subscription.duration') ?? Arr::get($product, 'subscription.period');

        if (is_string($period) && $period !== '') {
            return $period;
        }

        return match (strtolower($lookupKey)) {
            '$rc_weekly', 'weekly' => 'Weekly',
            '$rc_monthly', 'monthly' => 'Monthly',
            '$rc_two_month', 'two_month' => 'Every 2 months',
            '$rc_three_month', 'three_month' => 'Every 3 months',
            '$rc_six_month', 'six_month' => 'Every 6 months',
            '$rc_annual', 'annual', 'yearly' => 'Yearly',
            '$rc_lifetime', 'lifetime' => 'Lifetime',
            default => $lookupKey ?: '-',
        };
    }
}
