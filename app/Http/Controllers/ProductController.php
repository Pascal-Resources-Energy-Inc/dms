<?php

namespace App\Http\Controllers;

use App\Product;
use App\Item;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    public function index()
    {
        $user = auth()->user()->id;
        $items = Item::where(function ($query) {
            $query->where('for_ad', '!=', 1)
                ->orWhereNull('for_ad');
        })->orderBy('item')->get();
        
        $products = Product::with('item')->where('ad_user_id', $user)->get();
        $maxNewProducts = 5;
        $newProductCount = $products->where('is_new', 1)->where('status', 'Activate')->count();
        $bundleableProducts = $products->filter(function ($product) {
            $bundleProductIds = $product->bundle_product_ids ?? [];

            return empty($bundleProductIds) && (!$product->item || ($product->item->item_type ?? 'product') === 'product');
        });

        return view('products.index', compact('products', 'items', 'bundleableProducts', 'maxNewProducts', 'newProductCount'));
    }

    // public function create()
    // {
    //     $users = auth()->user();

    //     $items = Item::get();
    //     $productItems = Product::where('ad_user_id', $users->id)->get();
    //     return view('products.create', compact('items', 'productItems'));
    // }

    public function create()
    {
        $userId = auth()->id();
        $maxAdProducts = 5;

        $items = Item::where(function ($query) {
            $query->where('for_ad', '!=', 1)
                ->orWhereNull('for_ad');
        })->get();
        
        // KEY FIX: index products by item_id
        $productItems = Product::where('ad_user_id', $userId)
            ->get()
            ->keyBy('item_id');

        return view('products.create', compact('items', 'productItems', 'maxAdProducts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'selected_item_type' => 'required|in:product,bundle',
            'item_id' => 'nullable|integer|exists:items,id',
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'mega_dealer_price' => 'nullable|numeric|min:0',
            'dealer_price' => 'nullable|numeric|min:0',
            'client_price' => 'nullable|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:Activate,Deactivate',
            // 'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bundle_product_ids' => 'required_if:selected_item_type,bundle|array',
            'bundle_product_ids.*' => 'integer|exists:products,id',
        ]);

        DB::beginTransaction();

        try {
            $userId = auth()->id();
            $selectedType = $validated['selected_item_type'];
            $maxNewProducts = 5;
            $newProductCount = Product::where('ad_user_id', $userId)
                ->where('is_new', 1)
                ->where('status', 'Activate')
                ->count();

            if ($newProductCount >= $maxNewProducts) {
                DB::rollBack();

                return back()
                    ->withInput()
                    ->withErrors(['product_limit' => 'You can only create up to 5 new products per AD user.']);
            }

            $item = !empty($validated['item_id']) ? Item::findOrFail($validated['item_id']) : null;

            if ($item && ($item->item_type ?? 'product') !== $selectedType) {
                DB::rollBack();

                return back()
                    ->withInput()
                    ->withErrors(['item_id' => 'The selected item does not match the selected add type.']);
            }

            $bundleProductIds = collect($request->input('bundle_product_ids', []))
                ->map(function ($productId) {
                    return (int) $productId;
                })
                ->filter()
                ->unique()
                ->values();
            $bundleProducts = collect();

            if ($selectedType === 'bundle') {
                $bundleProducts = Product::where('ad_user_id', $userId)
                    ->where(function ($query) {
                        $query->whereDoesntHave('item')
                            ->orWhereHas('item', function ($itemQuery) {
                                $itemQuery->where('item_type', 'product')
                                    ->orWhereNull('item_type');
                            });
                    })
                    ->when(Schema::hasColumn('products', 'bundle_product_ids'), function ($query) {
                        $query->where(function ($bundleQuery) {
                            $bundleQuery->whereNull('bundle_product_ids')
                                ->orWhere('bundle_product_ids', '')
                                ->orWhere('bundle_product_ids', '[]');
                        });
                    })
                    ->whereIn('id', $bundleProductIds)
                    ->get();

                if ($bundleProductIds->isEmpty() || $bundleProducts->count() !== $bundleProductIds->count()) {
                    DB::rollBack();

                    return back()
                        ->withInput()
                        ->withErrors(['bundle_product_ids' => 'Please select valid existing products for this bundle.']);
                }

            }

            $existingProduct = null;
            $price = $validated['price'];
            $sku = $validated['sku'];

            $skuExists = Product::where('sku', $sku)
                ->exists();

            if ($skuExists) {
                DB::rollBack();

                return back()
                    ->withInput()
                    ->withErrors(['sku' => 'The SKU has already been taken.']);
            }

            $data = [
                'item_id' => $item ? $item->id : null,
                'product_name' => $validated['product_name'],
                'description' => $validated['description'] ?? null,
                'sku' => $sku,
                'price' => $price,
                'dealer_price' => $price,
                'mega_dealer_price' => $price,
                'deposit' => $validated['deposit'] ?? null,
                'ad_user_id' => $userId,
                'status' => $validated['status'] ?? 'Activate',
                'customer_points' => $item->customer_points ?? 1,
                'dealer_points' => $item->dealer_points ?? 1,
                'product_image' => $item->item_image ?? null,
                'is_new' => 1,
            ];

            if (Schema::hasColumn('products', 'item_type')) {
                $data['item_type'] = $selectedType;
            }

            if (Schema::hasColumn('products', 'bundle_product_ids')) {
                $data['bundle_product_ids'] = $selectedType === 'bundle' ? $bundleProductIds->all() : null;
            }

            if ($selectedType === 'bundle' && empty($data['product_image'])) {
                $selectedProductWithImage = $bundleProducts->first(function ($product) {
                    return !empty($product->product_image);
                });

                if ($selectedProductWithImage) {
                    $data['product_image'] = $selectedProductWithImage->product_image;
                }
            }

            if (Schema::hasColumn('products', 'client_price')) {
                $data['client_price'] = $validated['client_price'] ?? $price;
            }

            if (Schema::hasColumn('products', 'dealer_price')) {
                $data['dealer_price'] = $validated['dealer_price'] ?? $price;
            }

            if (Schema::hasColumn('products', 'mega_dealer_price')) {
                $data['mega_dealer_price'] = $validated['mega_dealer_price'] ?? $price;
            }

            // if (Schema::hasColumn('products', 'dealer_price')) {
            //     $data['dealer_price'] = $validated['dealer_price'] ?? null;
            // }

            // if (Schema::hasColumn('products', 'mega_dealer_price')) {
            //     $data['mega_dealer_price'] = $validated['mega_dealer_price'] ?? null;
            // }

            if ($request->hasFile('product_image')) {
                $image = $request->file('product_image');
                $imageName = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
                $path = public_path('uploads/products');

                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }

                $image->move($path, $imageName);
                $data['product_image'] = $imageName;
            }

            if ($selectedType === 'bundle') {
                $combinedImage = $this->createBundleImage($data['product_image'], $bundleProducts);

                if ($combinedImage) {
                    $data['product_image'] = $combinedImage;
                }
            }

            if ($existingProduct) {
                $existingProduct->update($data);
            } else {
                Product::create($data);
            }

            DB::commit();

            return redirect()
                ->route('products')
                ->with('success', $existingProduct ? 'Product updated successfully!' : 'Product created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function createBundleImage($mainImage, $bundleProducts)
    {
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }

        $imageNames = collect([$mainImage])
            ->merge($bundleProducts->pluck('product_image'))
            ->filter()
            ->unique()
            ->take(4)
            ->values();

        if ($imageNames->count() < 2) {
            return $imageNames->first();
        }

        $sources = $imageNames->map(function ($imageName) {
            return public_path('uploads/products/' . $imageName);
        })->filter(function ($path) {
            return file_exists($path);
        })->values();

        if ($sources->count() < 2) {
            return $imageNames->first();
        }

        $validSources = $sources->filter(function ($path) {
            $source = $this->makeImageResource($path);

            if (!$source) {
                return false;
            }

            imagedestroy($source);

            return true;
        })->values();

        if ($validSources->count() < 2) {
            return $imageNames->first();
        }

        $canvasSize = 900;
        $canvas = imagecreatetruecolor($canvasSize, $canvasSize);
        $background = imagecolorallocate($canvas, 248, 250, 252);
        $card = imagecolorallocate($canvas, 255, 255, 255);
        $border = imagecolorallocate($canvas, 226, 232, 240);
        $green = imagecolorallocate($canvas, 21, 128, 61);
        $paleGreen = imagecolorallocate($canvas, 240, 253, 244);
        $shadow = imagecolorallocate($canvas, 203, 213, 225);
        $tileBackground = imagecolorallocate($canvas, 248, 250, 252);

        imagefill($canvas, 0, 0, $background);
        imagefilledrectangle($canvas, 34, 34, 874, 874, $shadow);
        imagefilledrectangle($canvas, 26, 26, 866, 866, $card);
        imagerectangle($canvas, 26, 26, 866, 866, $border);
        imagefilledrectangle($canvas, 58, 58, 834, 834, $paleGreen);
        imagerectangle($canvas, 58, 58, 834, 834, $border);

        $layouts = [
            2 => [
                [86, 132, 342, 636],
                [472, 132, 342, 636],
            ],
            3 => [
                [86, 132, 342, 636],
                [472, 132, 342, 300],
                [472, 468, 342, 300],
            ],
            4 => [
                [86, 132, 342, 300],
                [472, 132, 342, 300],
                [86, 468, 342, 300],
                [472, 468, 342, 300],
            ],
        ];
        $layout = $layouts[min($validSources->count(), 4)];

        foreach ($validSources->take(4)->values() as $index => $path) {
            $source = $this->makeImageResource($path);

            if (!$source) {
                continue;
            }

            [$x, $y, $width, $height] = $layout[$index];
            imagefilledrectangle($canvas, $x, $y, $x + $width, $y + $height, $tileBackground);
            imagerectangle($canvas, $x, $y, $x + $width, $y + $height, $border);
            $this->drawImageContain($canvas, $source, $x + 18, $y + 18, $width - 36, $height - 36, $card);

            imagedestroy($source);
        }

        imagefilledellipse($canvas, 786, 768, 72, 72, $green);
        imagefilledrectangle($canvas, 768, 750, 804, 786, $green);
        imagerectangle($canvas, 768, 750, 804, 786, $card);

        $fileName = uniqid('bundle_', true) . '.jpg';
        $path = public_path('uploads/products/' . $fileName);

        imagejpeg($canvas, $path, 90);
        imagedestroy($canvas);

        return $fileName;
    }

    public function drawImageCover($canvas, $source, $x, $y, $width, $height)
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $sourceRatio = $sourceWidth / $sourceHeight;
        $targetRatio = $width / $height;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) ($sourceHeight * $targetRatio);
            $cropX = (int) (($sourceWidth - $cropWidth) / 2);
            $cropY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) ($sourceWidth / $targetRatio);
            $cropX = 0;
            $cropY = (int) (($sourceHeight - $cropHeight) / 2);
        }

        imagecopyresampled($canvas, $source, $x, $y, $cropX, $cropY, $width, $height, $cropWidth, $cropHeight);
    }

    public function drawImageContain($canvas, $source, $x, $y, $width, $height, $backgroundColor)
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            return;
        }

        imagefilledrectangle($canvas, $x, $y, $x + $width, $y + $height, $backgroundColor);

        $scale = min($width / $sourceWidth, $height / $sourceHeight);
        $targetWidth = (int) floor($sourceWidth * $scale);
        $targetHeight = (int) floor($sourceHeight * $scale);
        $targetX = $x + (int) floor(($width - $targetWidth) / 2);
        $targetY = $y + (int) floor(($height - $targetHeight) / 2);

        imagecopyresampled($canvas, $source, $targetX, $targetY, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
    }

    public function makeImageResource($path)
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'jfif']) && function_exists('imagecreatefromjpeg')) {
            return @imagecreatefromjpeg($path);
        }

        if ($extension === 'png' && function_exists('imagecreatefrompng')) {
            return @imagecreatefrompng($path);
        }

        if ($extension === 'gif' && function_exists('imagecreatefromgif')) {
            return @imagecreatefromgif($path);
        }

        return null;
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'selected_item_type' => 'nullable|in:product,bundle',
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:100|unique:products,sku,' . $id,
            'price' => 'required|numeric|min:0',
            'mega_dealer_price' => 'nullable|numeric|min:0',
            'dealer_price' => 'nullable|numeric|min:0',
            'client_price' => 'nullable|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:Activate,Deactivate',
            // 'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // 'bundle_product_ids' => 'nullable|array',
            // 'bundle_product_ids.*' => 'integer|exists:products,id',
        ]);

        $selectedType = $validated['selected_item_type'] ?? 'product';
        $submittedBundleProductIds = $request->has('bundle_product_ids');
        $bundleProductIds = collect($submittedBundleProductIds ? $request->input('bundle_product_ids', []) : ($product->bundle_product_ids ?? []))
            ->map(function ($productId) {
                return (int) $productId;
            })
            ->filter()
            ->reject(function ($productId) use ($product) {
                return $productId === (int) $product->id;
            })
            ->unique()
            ->values();
        $bundleProducts = collect();

        if ($selectedType === 'bundle') {
            $bundleProducts = Product::where('ad_user_id', $product->ad_user_id)
                ->where('id', '!=', $product->id)
                ->when(Schema::hasColumn('products', 'bundle_product_ids'), function ($query) {
                    $query->where(function ($bundleQuery) {
                        $bundleQuery->whereNull('bundle_product_ids')
                            ->orWhere('bundle_product_ids', '')
                            ->orWhere('bundle_product_ids', '[]');
                    });
                })
                ->whereIn('id', $bundleProductIds)
                ->get();

            // if ($bundleProductIds->isEmpty() || $bundleProducts->count() !== $bundleProductIds->count()) {
            //     return response()->json([
            //         'message' => 'Please select valid existing products for this bundle.',
            //         'errors' => [
            //             'bundle_product_ids' => ['Please select valid existing products for this bundle.'],
            //         ],
            //     ], 422);
            // }
        }

        $data = [
            'product_name' => $validated['product_name'],
            'description' => $validated['description'] ?? null,
            'sku' => $validated['sku'],
            'price' => $validated['price'],
            'deposit' => $validated['deposit'] ?? null,
            'status' => $validated['status'] ?? 'Activate',
        ];

        if (Schema::hasColumn('products', 'item_type')) {
            $data['item_type'] = $selectedType;
        }

        if (Schema::hasColumn('products', 'bundle_product_ids')) {
            $data['bundle_product_ids'] = $selectedType === 'bundle' ? $bundleProductIds->all() : null;
        }

        if (Schema::hasColumn('products', 'client_price')) {
            $data['client_price'] = $validated['client_price'] ?? $validated['price'];
        }

        if (Schema::hasColumn('products', 'dealer_price')) {
            $data['dealer_price'] = $validated['dealer_price'] ?? $validated['price'];
        }

        if (Schema::hasColumn('products', 'mega_dealer_price')) {
            $data['mega_dealer_price'] = $validated['mega_dealer_price'] ?? $validated['price'];
        }

        if ($request->hasFile('product_image')) {

            if ($product->product_image && file_exists(public_path('uploads/products/'.$product->product_image))) {
                unlink(public_path('uploads/products/'.$product->product_image));
            }

            $image = $request->file('product_image');
            $name = time().'_'.$image->getClientOriginalName();

            $image->move(public_path('uploads/products'), $name);

            $data['product_image'] = $name;
        }

        if ($selectedType === 'bundle' && ($submittedBundleProductIds || $request->hasFile('product_image'))) {
            $combinedImage = $this->createBundleImage($data['product_image'] ?? null, $bundleProducts);

            if ($combinedImage) {
                $data['product_image'] = $combinedImage;
            }
        }

        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully!'
        ]);
    }

    public function destroy(Product $product)
    {
        if ($product->product_image && file_exists(public_path('uploads/products/' . $product->product_image))) {
            unlink(public_path('uploads/products/' . $product->product_image));
        }

        $product->delete();

        return redirect()->route('products')->with('success', 'Product deleted successfully!');
    }

    // public function storeBulk(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {
    //         foreach ($request->items as $item) {

    //             $data = [
    //                 'product_name' => $item['name'],
    //                 'description' => $item['description'] ?? null,
    //                 'price' => $item['price'],
    //                 // 'discount' => $item['discount'] ?? 0,
    //                 // 'final_price' => max(0, $item['price'] - ($item['discount'] ?? 0)),
    //                 'sku' => $item['sku'],
    //                 'ad_user_id' => auth()->user()->id, // ✅ FIXED
    //                 'status' => 'Activate',
    //                 'dealer_points' => 1,
    //                 'customer_points' => 1
    //             ];

    //             // UPDATE OR CREATE
    //             Product::updateOrCreate(
    //                 ['id' => $item['id'] ?? null],
    //                 $data
    //             );
    //         }

    //         DB::commit();

    //         return back()->with('success', '✅ Products saved successfully!');

    //     } catch (\Exception $e) {
    //         DB::rollback();

    //         return back()->with('error', '❌ Error: ' . $e->getMessage());
    //     }
    // }

    public function storeBulk(Request $request)
    {
        $userId = auth()->id();

        DB::beginTransaction();

        try {
            $itemIds = collect($request->input('items', []))
                ->pluck('item_id')
                ->filter()
                ->map(function ($itemId) {
                    return (int) $itemId;
                })
                ->unique()
                ->values();
            $itemsById = Item::whereIn('id', $itemIds)->get()->keyBy('id');

            foreach ($request->input('items', []) as $item) {
                if (empty($item['item_id'])) {
                    continue;
                }

                $masterItem = $itemsById->get((int) $item['item_id']);

                if (!$masterItem) {
                    continue;
                }

                // find existing product by item_id + user
                $product = Product::where('item_id', $masterItem->id)
                    ->where('ad_user_id', $userId)
                    ->first();

                if (empty($item['selected'])) {
                    if ($product) {
                        $product->update(['status' => 'Deactivate']);
                    }

                    continue;
                }

                $clientPrice = $item['client_price'] ?? $item['price'] ?? $masterItem->price ?? 0;
                $sku = !empty($item['sku']) ? $item['sku'] : ('AD' . $userId . '-ITEM' . $masterItem->id);

                $data = [
                    'item_id' => $item['item_id'], // ✅ FIXED
                    'product_name' => $masterItem->item,
                    'description' => $masterItem->item_description,
                    'price' => $clientPrice,
                    'sku' => $sku,
                    'ad_user_id' => $userId,
                    'status' => 'Activate',
                    'dealer_points' => $masterItem->dealer_points ?? 1,
                    'customer_points' => $masterItem->customer_points ?? 1
                ];

                $data['item_id'] = $masterItem->id;

                if (Schema::hasColumn('products', 'mega_dealer_price')) {
                    $data['mega_dealer_price'] = $item['mega_dealer_price'] ?? null;
                }

                if (Schema::hasColumn('products', 'dealer_price')) {
                    $data['dealer_price'] = $item['dealer_price'] ?? null;
                }

                if (Schema::hasColumn('products', 'client_price')) {
                    $data['client_price'] = $clientPrice;
                }

                if ($product) {
                    $product->update($data);
                } else {
                    // ✅ include image from item table
                    $data['product_image'] = $masterItem->item_image;

                    Product::create($data);
                }
            }

            DB::commit();

            Alert::success('Success', 'Products saved successfully!');

            return back();

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }
}
