<?php

namespace App\Http\Controllers;

use App\Item;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                if ($request->type === 'ad') {
                    $query->where('for_ad', 1);
                } elseif ($request->type === 'regular') {
                    $query->where(function ($inner) {
                        $inner->where('for_ad', 0)->orWhereNull('for_ad');
                    });
                }
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($inner) use ($search) {
                    $inner->where('item', 'like', '%' . $search . '%')
                        ->orWhere('item_description', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('item')
            ->get();

        return view('items.index', compact('items'));
    }

    public function store(Request $request)
    {
        Item::create($this->validatedData($request));

        Alert::success('Item Created', 'Item saved successfully.');

        if ($request->input('redirect_to') === 'products.create') {
            return redirect()->route('products.create');
        }

        return redirect()->route('items');
    }

    public function update(Request $request, Item $item)
    {
        $data = $this->validatedData($request, $item);

        if ($request->hasFile('item_image') && $item->item_image) {
            $this->deleteImage($item->item_image);
        }

        $item->update($data);

        Alert::success('Item Updated', 'Item updated successfully.');

        return redirect()->route('items');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        Alert::success('Item Deleted', 'Item deleted successfully.');

        return redirect()->route('items');
    }

    private function validatedData(Request $request, Item $item = null)
    {
        $data = $request->validate([
            'item' => 'required|string|max:255',
            'item_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'dealer_price' => 'required|numeric|min:0',
            'md_price' => 'required|numeric|min:0',
            'dprice' => 'required|numeric|min:0',
            'dealer_points' => 'nullable|integer|min:0',
            'customer_points' => 'nullable|integer|min:0',
            'status' => 'required|in:Activate,Deactivate',
            'for_ad' => 'nullable|boolean',
            'item_type' => 'required|in:product,bundle',
            'stove_kit_color_availability' => 'nullable|array',
            'stove_kit_color_availability.*' => 'nullable|boolean',
            'item_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
        ]);

        $data['dealer_points'] = $data['dealer_points'] ?? 0;
        $data['customer_points'] = $data['customer_points'] ?? 0;
        // $data['for_ad'] = $request->boolean('for_ad') ? 1 : 0;
        $data['for_ad'] = $request->has('for_ad') ? 1 : 0;
        $data['item_type'] = $data['item_type'] ?? 'product';
        $data['stove_kit_color_availability'] = null;

        if (strpos(strtolower(trim((string) $data['item'])), 'gaz lite stove kit') !== false) {
            $selectedColors = $request->input('stove_kit_color_availability', []);
            $data['stove_kit_color_availability'] = collect(Item::STOVE_KIT_COLORS)
                ->mapWithKeys(function ($label, $color) use ($selectedColors) {
                    return [$color => (bool) data_get($selectedColors, $color, false)];
                })
                ->all();
        }

        if ($request->hasFile('item_image')) {
            $data['item_image'] = $this->storeImage($request);
        } elseif ($item) {
            unset($data['item_image']);
        }

        return $data;
    }

    private function storeImage(Request $request)
    {
        $image = $request->file('item_image');
        $imageName = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
        $path = public_path('uploads/products');

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $image->move($path, $imageName);

        return $imageName;
    }

    private function deleteImage($imageName)
    {
        $path = public_path('uploads/products/' . $imageName);

        if (file_exists($path)) {
            unlink($path);
        }
    }
}
