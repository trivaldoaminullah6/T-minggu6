<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'nullable|integer',
            'sku'         => 'required|string|max:100|unique:products,sku',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.jpg';

            // Inisialisasi Intervention Image Manager dengan GD Driver
            $manager = new ImageManager(Driver::class);
            if (method_exists($manager, 'read')) {
                $image = $manager->read($file->getRealPath())
                                 ->cover(800, 800)
                                 ->toJpeg(85);
            } else {
                $img = $manager->decodePath($file->getRealPath())
                               ->cover(800, 800);
                $image = $img->encode(new JpegEncoder(85));
            }

            // Simpan gambar ke storage/app/public/products
            Storage::disk('public')->put('products/' . $filename, (string) $image);
            $data['image'] = 'products/' . $filename;
        }

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $product = Product::findOrFail($id);
        return view('products.show', compact('product'));
    }

    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'nullable|integer',
            'sku'         => 'required|string|max:100|unique:products,sku,' . $product->id,
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.jpg';

            $manager = new ImageManager(Driver::class);
            if (method_exists($manager, 'read')) {
                $image = $manager->read($file->getRealPath())
                                 ->cover(800, 800)
                                 ->toJpeg(85);
            } else {
                $img = $manager->decodePath($file->getRealPath())
                               ->cover(800, 800);
                $image = $img->encode(new JpegEncoder(85));
            }

            Storage::disk('public')->put('products/' . $filename, (string) $image);
            $data['image'] = 'products/' . $filename;
        }

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
