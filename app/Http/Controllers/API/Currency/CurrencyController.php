<?php

namespace App\Http\Controllers\API\Currency;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Services\ExchangeRateService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    use ApiResponse;

    public function index(
        Request $request,
        ExchangeRateService $exchangeRateService
    ) {
        // dd(auth()->user()->currency);
        $base = $request->get('base', auth()->user()->currency ?? "USD");
        $currencies = Currency::where('is_active', true)
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('code', 'LIKE', "%{$request->search}%")
                        ->orWhere('name', 'LIKE', "%{$request->search}%");
                });
            })
            ->orderBy('code')
            ->get();

        $rates = $exchangeRateService->getRates($base);

        $conversionRates = $rates['conversion_rates'] ?? [];
        // dd($rates);
        $data = $currencies->map(function ($currency) use ($conversionRates) {
            return [
                'id' => $currency->id,
                'code' => $currency->code,
                'name' => $currency->name,
                'symbol' => $currency->symbol,
                'flag_url' => $currency->flag_url,
                'rate' => $conversionRates[$currency->code] ?? null
            ];
        });

        return $this->successResponse('Currency list', $data);
    }

    public function convert(
        Request $request,
        ExchangeRateService $exchangeRateService
    ) {

        $validated = $request->validate([
            'from_currency' => ['required'],
            'to_currency' => ['required'],
            'amount' => ['required', 'numeric', 'min:0']
        ]);

        $result = $exchangeRateService->convert(
            $validated['from_currency'],
            $validated['to_currency'],
            $validated['amount']
        );

        // dd($result)

        $data = [
            'from_currency' => $validated['from_currency'],
            'to_currency' => $validated['to_currency'],
            'rate' => $result['rate'],
            'input_amount' => $validated['amount'],
            'converted_amount' => $result['amount']
        ];


        return $this->successResponse('Successfully converted', $data);
    }
}
