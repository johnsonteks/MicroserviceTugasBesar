<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Jobs\UpdateProductStock;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::all();
        return new OrderResource($orders, 'Success', 'List of orders');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'user_id' => 'required',
            'status' => 'required',
            'total_price' => 'required',
            'quantity' => 'required',
        ]);

        if ($validator->fails()) {
            return new OrderResource(null, 'Failed', $validator->errors());
        }

        // 1. Insert Data Order
        $data = $request->all();
        $product = Order::create([
            'id' => Str::uuid(),
            'code' => 'OR-'.Str::random(8),
            'product_id' => $data['product_id'],
            'user_id' => $data['user_id'],
            'status' => $data['status'],
            'total_price' => $data['total_price'],
            'quantity' => $data['quantity'],
        ]);

        // 2. Update Stock ke Product Service (Synchronous) - Using container name
        // Http::post(env('PRODUCT_SERVICE_URL', 'http://product_app:8000').'/api/products/'.$request->product_id.'/update-stock', [
        //     'product_quantity' => $request->quantity,
        // ]);

        // OR from outside Docker network (localhost)
        // Http::post('http://localhost:8002/api/products/'.$request->product_id.'/update-stock', [
        //     'product_quantity' => $request->quantity,
        // ]);


        // 2. Update Stock ke Product Service melalui qeueu (RabbitMQ)
        UpdateProductStock::dispatch($request->product_id, $request->quantity)
            ->onQueue('stock-update') 
            ->delay(now()->addSeconds(5)); // Delay 1 detik

        return new OrderResource($product, 'Success', 'Order created successfully');
    }

    public function show($id)
    {
        $order = Order::find($id);
        if ($order) {
            $data = $order->toArray();

            try {
                // Get the product details (consume) - Using container communication
                $productResponse = Http::timeout(10)->get(env('PRODUCT_SERVICE_URL', 'http://product_app:8000').'/api/products/'.$order->product_id);
                $data['product'] = $productResponse->successful() ? $productResponse->json()['data'] : null;

                // Get the user details (consume) - Using container communication
                $userResponse = Http::timeout(10)->get(env('USER_SERVICE_URL', 'http://user_app:8000').'/api/users/'.$order->user_id);
                $data['user'] = $userResponse->successful() ? $userResponse->json()['data'] : null;

            } catch (\Exception $e) {
                // Fallback to localhost if container communication fails
                try {
                    $productResponse = Http::timeout(10)->get('http://localhost:8002/api/products/'.$order->product_id);
                    $data['product'] = $productResponse->successful() ? $productResponse->json()['data'] : null;

                    $userResponse = Http::timeout(10)->get('http://localhost:8001/api/users/'.$order->user_id);
                    $data['user'] = $userResponse->successful() ? $userResponse->json()['data'] : null;

                } catch (\Exception $e) {
                    $data['product'] = null;
                    $data['user'] = null;
                    $data['error'] = 'Unable to fetch related data: ' . $e->getMessage();
                }
            }

            return new OrderResource($data, 'Success', 'Order found');
        } else {
            return new OrderResource(null, 'Failed', 'Order not found');
        }
    }

    public function getByUser($id)
    {
        $orders = Order::where('user_id', $id)->get();
        if ($orders->count() > 0) {
            foreach ($orders as $index => $order) {
                try {
                    // Get the product details (consume) - Using container communication
                    $productResponse = Http::timeout(10)->get(env('PRODUCT_SERVICE_URL', 'http://product_app:8000').'/api/products/'.$order->product_id);
                    $orders[$index]['product'] = $productResponse->successful() ? $productResponse->json()['data'] : null;

                    // Get the user details (consume) - Using container communication  
                    $userResponse = Http::timeout(10)->get(env('USER_SERVICE_URL', 'http://user_app:8000').'/api/users/'.$order->user_id);
                    $orders[$index]['user'] = $userResponse->successful() ? $userResponse->json()['data'] : null;

                } catch (\Exception $e) {
                    // Fallback to localhost if container communication fails
                    try {
                        $productResponse = Http::timeout(10)->get('http://localhost:8002/api/products/'.$order->product_id);
                        $orders[$index]['product'] = $productResponse->successful() ? $productResponse->json()['data'] : null;

                        $userResponse = Http::timeout(10)->get('http://localhost:8001/api/users/'.$order->user_id);
                        $orders[$index]['user'] = $userResponse->successful() ? $userResponse->json()['data'] : null;

                    } catch (\Exception $e) {
                        $orders[$index]['product'] = null;
                        $orders[$index]['user'] = null;
                        $orders[$index]['error'] = 'Unable to fetch related data';
                    }
                }
            }

            return new OrderResource($orders, 'Success', 'Orders found');
        } else {
            return new OrderResource(null, 'Failed', 'Orders not found');
        }
    }
}