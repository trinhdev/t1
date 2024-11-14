<?php
namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Stock;
use App\Models\Status;
use App\Models\Payment;
use App\Models\Products;
use App\Models\Shipping;

use App\Models\OrderDetail;
use App\Models\ProductUnits;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function test()
    {
        $orders = Order::all();
        dd($orders);

    return response()->json($orders); // Trả về các đơn hàng
    }
        
    public function createOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer',
            'items.*.unit_code' => 'required|string',
            'payment_method' => 'required|string', // 'COD' or 'online'
        ]);
    
        DB::beginTransaction();
        $customer = auth()->user();
    
        try {
            // 1. Create Order
            $order = Order::create([
                'customer_id' => $customer->id,
                'order_code' => 'ORD' . time(),
                'order_status_id' => Status::where('status_code', 'pending')
                     ->where('type', 'order')
                     ->first()
                     ->id,
                'total_amount' => $request->total_amount,
                'voucher_id' => $request->voucher_id ?? null,
                'shipping_address' => $customer->address ?? $request->address,
            ]);
    
            // 2. Add Items to OrderDetail and Update Stock
            foreach ($request->items as $item) {
                $productUnit = ProductUnits::where('product_id', $item['product_id'])->firstOrFail();
                $quantity = $item['quantity'];
    
                if ($productUnit->level < 3) {
                    $quantity *= $productUnit->exchangerate;
                }
    
                $stock = Stock::where('product_id', $item['product_id'])->first();
                if (!$stock || $stock->quantity < $quantity) {
                    return response()->json(['message' => 'Không đủ số lượng trong kho'], 400);
                }
    
                // Decrement stock
                $stock->decrement('quantity', $quantity);
    
                // Calculate total item price
                $itemTotalPrice = $productUnit->price * $item['quantity'];
            
    
                // Create OrderDetail
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $productUnit->price,
                    'total_price' => $itemTotalPrice,
                ]);
            }
    
            // Update total amount on the order
          
    
            // 3. Handle Payment
            if ($request->payment_method == 'COD') {
                // Nếu phương thức thanh toán là COD, không cần tạo bản ghi thanh toán
                $paymentStatus = Status::where('status_code', 'pending')
                    ->where('type', 'payment')
                    ->first()
                    ->id;
    
                // Tạo payment với status là "pending"
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'amount' => $request->total_amount,
                    'payment_method' => 'COD', // Thanh toán khi nhận hàng
                    'payment_date' => null, // Không có ngày thanh toán
                    'payment_status_id' => $paymentStatus,
                ]);
            } else {
                // Xử lý thanh toán trực tuyến (online)
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'amount' => $request->total_amount,
                    'payment_method' => $request->payment_method,
                    'payment_date' => now(),
                    'payment_status_id' => Status::where('status_code', 'pending')
                         ->where('type', 'payment')
                         ->first()
                         ->id,
                ]);
            }
    
            // Update payment_id on the order
            $order->update(['payment_id' => $payment->id]);
    
            DB::commit();
    
            // Trả về thông tin bổ sung sau khi tạo đơn hàng thành công
            return response()->json([
                'message' => 'Tạo đơn hàng thành công',
                'order_code' => $order->order_code,
                'payment_id' => $payment->id,
                'customer' => [
                    'name' => $customer->name,
                    'address' => $customer->address,
                    'phone' => $customer->phone, // Giả sử `phone` là tên cột trong bảng `users`
                ],
                'order_status' => $order->orderStatus->status_name,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xác thực.',
                'errors' => $e->validator->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Tạo đơn hàng không thành công', 'error' => $e->getMessage()], 500);
        }
    }


    public function cancelOrder($orderId)
        {
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json(['message' => 'Đơn hàng không tồn tại.'], 404);
            }

            // Xóa mềm đơn hàng
            $order->delete();

            return response()->json(['message' => 'Đơn hàng đã được hủy thành công.']);
        }

        public function restoreOrder($orderId)
        {
            $order = Order::withTrashed()->find($orderId);
        
            if (!$order) {
                return response()->json(['message' => 'Đơn hàng không tồn tại.'], 404);
            }
            $order->restore();
        
            return response()->json(['message' => 'Đơn hàng đã được khôi phục thành công.']);
        }

        
        public function getOrderWithDetails($orderId)
{
    // Lấy đơn hàng cùng với chi tiết, thông tin khách hàng, trạng thái đơn hàng và phương thức thanh toán
    $order = Order::with(['orderDetails', 'customers', 'orderStatus', 'payment'])
                  ->where('id', $orderId)
                  ->first();

    if (!$order) {
        return response()->json([
            'status' => 'error',
            'message' => 'Không tìm thấy đơn hàng.'
        ], 404);
    }

    // Trả về dữ liệu đơn hàng, chi tiết đơn hàng, thông tin khách hàng và phương thức thanh toán
    return response()->json([
        'status' => 'success',
        'order' => [
            'order_code' => $order->order_code,
            'customer_id' => $order->customer_id,
            'status' => $order->orderStatus->status_name,
            'total_amount' => $order->total_amount,
            'shipping_address' => $order->shipping_address,
            'payment_method' => $order->payment->payment_method
        ],
        'customer' => [
            'name' => $order->customers->name,
            'address' => $order->customers->address,
            'phone' => $order->customers->phone
        ],
        'order_details' => $order->orderDetails,
      
    ], 200);
}

        





        public function showOrderHistory($customerId)
{
    $orders = Order::where('customer_id', $customerId)
        ->with(['orderDetails.product', 'orderStatus', 'payment.paymentStatus']) // Nạp cả `orderStatus` của `payment`
        ->orderBy('created_at', 'desc')
        ->get();

    if ($orders->isEmpty()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Không tìm thấy đơn hàng nào cho khách hàng này.'
        ], 404);
    }

    // Trả về thông tin lịch sử đơn hàng bao gồm chi tiết đơn hàng, trạng thái và trạng thái thanh toán
    return response()->json([
        'status' => 'success',
        'orders' => $orders->map(function ($order) {
            return [
                'order_code' => $order->order_code,
                'created_at' => $order->created_at,
                'total_amount' => $order->total_amount,
                'status' => $order->orderStatus->status_name, // Trạng thái đơn hàng
                'paymen_method' => $order->payment->payment_method,
                'payment_status' => $order->payment->paymentStatus->status_name ?? 'Chưa xác định', // Trạng thái thanh toán từ `orderStatus` của `payment`
                'order_details' => $order->orderDetails->map(function ($detail) {
                    return [
                        'product_name' => $detail->product->name,
                        'quantity' => $detail->quantity,
                        'price' => $detail->price,
                        'total_price' => $detail->total_price
                    ];
                })
            ];
        })
    ], 200);
}




}
