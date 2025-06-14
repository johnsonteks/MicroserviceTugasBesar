<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return new ProductResource($products, 'Success', 'List of products');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'stock' => 'required',
        ]);

        if ($validator->fails()) {
            return new ProductResource(null, 'Failed', $validator->errors());
        }

        $data = $request->all();
        $product = Product::create($data);

        return new ProductResource($product, 'Success', 'Product created successfully');
    }

    public function show($id)
    {
        $product = Product::find($id);
        if ($product) {
            return new ProductResource($product, 'Success', 'Product found');
        } else {
            return new ProductResource(null, 'Failed', 'Product not found');
        }
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if ($product) {
            $validator = Validator::make($request->all(), [
                'code' => 'sometimes|required',
                'name' => 'sometimes|required',
                'description' => 'sometimes|required',
                'price' => 'sometimes|required',
                'stock' => 'sometimes|required',
            ]);

            if ($validator->fails()) {
                return new ProductResource(null, 'Failed', $validator->errors());
            }

            $data = $request->only(['code', 'name', 'description', 'price', 'stock']);
            $product->update($data);

            return new ProductResource($product->fresh(), 'Success', 'Product updated successfully');
        } else {
            return new ProductResource(null, 'Failed', 'Product not found');
        }
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->delete();
            return new ProductResource($product, 'Success', 'Product deleted successfully');
        } else {
            return new ProductResource(null, 'Failed', 'Product not found');
        }
    }

    public function updateStock(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'product_quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return new ProductResource(null, 'Failed', $validator->errors());
        }

        sleep(5);
        $product = Product::find($id);
        if ($product) {
            $productQuantity = $request->product_quantity;
            $updatedStock = $product->stock + $productQuantity; // Menambah stock
            $product->stock = $updatedStock;
            $product->save();

            return new ProductResource($product, 'Success', 'Product stock increased successfully');
        } else {
            return new ProductResource(null, 'Failed', 'Product not found');
        }
    }
    public function kurangStock(Request $request, $id)
    {
        // Tambahkan validasi
        $validator = Validator::make($request->all(), [
            'product_quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return new ProductResource(null, 'Failed', $validator->errors());
        }

        sleep(5);
        $product = Product::find($id);
        if ($product) {
            $productQuantity = $request->product_quantity;
            
            // Validasi stock cukup
            if ($product->stock < $productQuantity) {
                return new ProductResource(null, 'Failed', 'Stock tidak mencukupi');
            }

            $updatedStock = $product->stock - $productQuantity;
            $product->stock = $updatedStock;
            $product->save();

            return new ProductResource($product, 'Success', 'Product stock reduced successfully');
        } else {
            return new ProductResource(null, 'Failed', 'Product not found');
        }
    }
}
