<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bistro;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bistro = $this->bistro($request);
        $products = $bistro->products()->orderBy('display_order')->orderBy('id')->get();

        return response()->json(['data' => $products->map(fn (Product $product): array => $this->productPayload($product)), 'meta' => [
            'categories' => $products->pluck('category')->filter()->unique()->values(),
        ]]);
    }

    public function store(Request $request): JsonResponse
    {
        $bistro = $this->bistro($request);
        $data = $this->validatedData($request);
        $data['slug'] = $this->uniqueSlug($bistro, $data['name']);
        $data['display_order'] = ((int) $bistro->products()->max('display_order')) + 1;
        $product = $bistro->products()->create($data);

        return response()->json(['data' => $this->productPayload($product)], 201);
    }

    public function update(Request $request, int $product): JsonResponse
    {
        $bistro = $this->bistro($request);
        $record = $bistro->products()->findOrFail($product);
        $record->fill($this->validatedData($request));
        $record->save();

        return response()->json(['data' => $this->productPayload($record->refresh())]);
    }

    public function destroy(Request $request, int $product): JsonResponse
    {
        $bistro = $this->bistro($request);
        $record = $bistro->products()->findOrFail($product);
        $this->deleteManagedImage($record->image_url, $bistro);
        $record->delete();

        return response()->json(['message' => 'Producto eliminado.']);
    }

    public function uploadImage(Request $request, int $product): JsonResponse
    {
        $bistro = $this->bistro($request);
        $record = $bistro->products()->findOrFail($product);
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $oldUrl = $record->image_url;
        $path = $validated['image']->storePublicly('menu/'.$bistro->slug, 'public');
        $record->forceFill(['image_url' => '/storage/'.$path])->save();

        $this->deleteManagedImage($oldUrl, $bistro);

        return response()->json(['data' => $this->productPayload($record->refresh())]);
    }

    private function bistro(Request $request): Bistro
    {
        $user = $request->user();
        abort_unless($user?->is_active && $user->bistro?->is_active, 403);

        return $user->bistro;
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'string', 'max:80'],
            'price_cop' => ['required', 'integer', 'min:0', 'max:100000000'],
            'available' => ['required', 'boolean'],
            'tag' => ['nullable', 'string', 'max:80'],
            'illustration' => ['nullable', 'string', 'max:32'],
        ]);
    }

    private function uniqueSlug(Bistro $bistro, string $name): string
    {
        $base = Str::slug($name) ?: 'producto';
        $slug = $base;
        $suffix = 2;
        while ($bistro->products()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function deleteManagedImage(?string $imageUrl, Bistro $bistro): void
    {
        if (is_string($imageUrl) && Str::startsWith($imageUrl, '/storage/menu/'.$bistro->slug.'/')) {
            Storage::disk('public')->delete(Str::after($imageUrl, '/storage/'));
        }
    }

    private function productPayload(Product $product): array
    {
        return [
            'id' => $product->id, 'name' => $product->name, 'category' => $product->category,
            'description' => $product->description, 'price_cop' => $product->price_cop,
            'available' => $product->available, 'image_url' => $product->image_url,
            'illustration' => $product->illustration, 'tag' => $product->tag,
        ];
    }
}
