<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bistro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BistroSettingsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->settings($this->bistro($request))]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'delivery_enabled' => ['required', 'boolean'],
            'delivery_fee_cop' => ['required', 'integer', 'min:0', 'max:100000000'],
            'delivery_neighborhoods' => ['required', 'array', 'max:100'],
            'delivery_neighborhoods.*' => ['required', 'string', 'max:120'],
            'pickup_enabled' => ['required', 'boolean'],
            'pickup_address' => ['nullable', 'string', 'max:255'],
        ]);

        $neighborhoods = collect($data['delivery_neighborhoods'])
            ->map(fn (string $name): string => trim($name))
            ->filter()
            ->unique(fn (string $name): string => mb_strtolower($name))
            ->values();

        if ($data['delivery_enabled'] && $neighborhoods->isEmpty()) {
            throw ValidationException::withMessages(['delivery_neighborhoods' => 'Agrega al menos un barrio para habilitar domicilios.']);
        }

        $pickupAddress = trim((string) ($data['pickup_address'] ?? ''));
        if ($data['pickup_enabled'] && $pickupAddress === '') {
            throw ValidationException::withMessages(['pickup_address' => 'Indica la dirección para habilitar la recogida en el local.']);
        }

        $bistro = $this->bistro($request);
        $bistro->fill([
            'delivery_enabled' => $data['delivery_enabled'],
            'delivery_fee_cop' => $data['delivery_fee_cop'],
            'delivery_neighborhoods' => $neighborhoods->all(),
            'pickup_enabled' => $data['pickup_enabled'],
            'pickup_address' => $pickupAddress !== '' ? $pickupAddress : null,
        ])->save();

        return response()->json(['data' => $this->settings($bistro->refresh())]);
    }

    private function bistro(Request $request): Bistro
    {
        $user = $request->user();
        abort_unless($user?->is_active && $user->bistro?->is_active, 403);

        return $user->bistro;
    }

    private function settings(Bistro $bistro): array
    {
        return [
            'delivery_enabled' => $bistro->delivery_enabled,
            'delivery_fee_cop' => $bistro->delivery_fee_cop,
            'delivery_neighborhoods' => $bistro->delivery_neighborhoods ?? [],
            'pickup_enabled' => $bistro->pickup_enabled,
            'pickup_address' => $bistro->pickup_address,
            'is_demo' => $bistro->is_demo,
        ];
    }
}
