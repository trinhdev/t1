<?php
namespace App\Http\Controllers\Api;

use App\Models\Order;
use GuzzleHttp\Client;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\HistoryTransaction;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\Writer\Ods\Content;

class PaymentController extends Controller
{
    public function paymentMethod() {
        $method = PaymentMethod::all();
        return response()->json($method);
    }
    public function get(Request $request)
    {

        try {
           
            $content = $request->content;
             // Loại bỏ "CUSTOMER "
            
            // Kiểm tra nếu mã đơn hàng bắt đầu bằng "ORD"
            $content = $request->content;
        
        // Tìm kiếm mã đơn hàng bắt đầu bằng "ORD" trong content
        preg_match('/ORD[0-9]+/', $content, $matches);

        // Kiểm tra nếu tìm thấy mã đơn hàng hợp lệ
        if (empty($matches)) {
            return response()->json(['error' => 'Mã đơn hàng không hợp lệ.'], 400);
        }

        // Lấy mã đơn hàng từ kết quả tìm kiếm
        $order_code = $matches[0];
            // Tìm đơn hàng theo mã
            $order = Order::where('order_code', $request->content)->firstOrFail();
    
            // Kiểm tra số tiền
            if ($request->money === $order->amount) {
                $payment = Payment::where('id', $order->payment_id)->first();
    
                // Kiểm tra trạng thái thanh toán trước khi cập nhật
                if ($payment->payment_status_id !== 6) {
                    $payment->update(['payment_status_id' => 6]);
                }
    
                // Lưu lịch sử giao dịch
                HistoryTransaction::create([
                    'phone' => $request->phone,
                    'money' => $request->money,
                    'type' => $request->type,
                    'gateway' => $request->gateway,
                    'payment_id' => $order->payment_id,
                    'txn_id' => $request->txn_id,
                    'content' => 'Giao dịch với mã đơn ' . $order->order_code,
                    'datetime' => $request->datetime,
                    'balance' => $request->balance,
                    'number' => $request->number,
                ]);
    
                return response()->json(['message' => 'Giao dịch thành công!'], 200);
            } else {
                return response()->json(['error' => 'Số tiền không khớp với giá trị đơn hàng'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi trong quá trình xử lý: ' . $e->getMessage()], 500);
        }
}

public function checkPaymentStatus($paymentId)
{
    $payment = Payment::find($paymentId);

    if ($payment->payment_status_id == 6) {
        $order = $payment->order; 
        if ($order) {
            $order->order_status_id = 7;
            $order->save();
        }

        return response()->json(['message' => 'Thanh toán thành công.']);
    } else {
        return response()->json(['message' => 'Thanh toán không thành công.']);
    }
}

public function sendOrderInfoToZalo($customerPhone, $order)
{
    // Lấy access token của Zalo Official Account
    $accessToken = 'YOUR_ZALO_ACCESS_TOKEN';  // Thay YOUR_ZALO_ACCESS_TOKEN bằng token thật của bạn
    
    // Định dạng thông tin đơn hàng bạn muốn gửi
    $message = "Thông tin đơn hàng:\n";
    $message .= "Mã đơn hàng: " . $order->order_code . "\n";
    $message .= "Sản phẩm: " . $order->product_name . "\n";
    $message .= "Số lượng: " . $order->quantity . "\n";
    $message .= "Tổng tiền: " . $order->total_amount . " VNĐ\n";

    // Gửi tin nhắn qua API Zalo
    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
    ])->post('https://openapi.zalo.me/v2.0/oa/message', [
        'access_token' => $accessToken,
        'to' => $customerPhone, // Số điện thoại của khách hàng
        'message' => $message,
    ]);

    // Kiểm tra phản hồi
    if ($response->successful()) {
        return response()->json(['status' => 'success', 'message' => 'Đã gửi tin nhắn qua Zalo']);
    } else {
        return response()->json(['status' => 'error', 'message' => 'Gửi tin nhắn thất bại']);
    }
}
            


    public function generateQRCode(Request $request)
    {
        $order = Order::where('order_code',$request->order_code)->first();
        // Lấy dữ liệu order từ request hoặc database
        // $orderCode = $request->input('order_code');  // lấy order_code từ request
        // $totalAmount = $request->input('total_amount');  // lấy total_amount từ request
        // Các tham số cần thiết để tạo mã QR
        
        $accountNo = "0326910271"; // Số tài khoản người nhận
        $accountName = "TRAN DAI NGHIA"; // Tên người nhận
        $acqId = "970422"; // Mã ngân hàng
        $addInfo = $order->order_code;  // Thông tin bổ sung (lấy từ order_code)
        
        $amount = $order->total_amount; 
        ; // Số tiền (lấy từ total_amount)
        $template = "compact";  // Chế độ template
        
        // URL của VietQR API
        $url = "https://api.vietqr.io/v2/generate";
        
        // Tạo HTTP client
        $client = new Client();
        
        // Gửi yêu cầu POST tới VietQR API
        $response = $client->post($url, [
            'json' => [
                'accountNo' => $accountNo,
                'accountName' => $accountName,
                'acqId' => $acqId,
                'addInfo' => $addInfo,
                'amount' => $amount,
                'template' => $template,
            ]
        ]);
        
        // Kiểm tra xem API trả về kết quả thành công không
        if ($response->getStatusCode() == 200) {
            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['data']['qrCode'])) {
                $qrCode = $data['data']['qrCode'];
            } else {
                return response()->json(['error' => 'QR Code không tồn tại']);
            }  // Lấy mã QR trả về
            
            return response()->json([
                'success' => true,
                'qrCode' => $qrCode
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error generating QR code'
            ]);
        }
    }

    public function generateQR(Request $request)
    {
        $order = Order::where('order_code',$request->order_code)->first();
        // Lấy thông tin từ request
        $accountNo = '0326910271'; // Số tài khoản
        $acqId = '970422'; // Mã ngân hàng
        $accountName = urlencode('TRAN DAI NGHIA'); // Tên tài khoản (URL encode để đảm bảo an toàn)
        $amount = $order->total_amount;
        
        $addInfo = $order->order_code; // Nội dung mô tả (order_code)

        // Tạo URL dựa trên các tham số đã cung cấp
        $qrUrl = "https://api.vietqr.io/image/{$acqId}-{$accountNo}-5mDSHQa.jpg?accountName={$accountName}&amount={$amount}&addInfo={$addInfo}";

        return response()->json([
            'status' => 'success',
            'qrUrl' => $qrUrl,
        ]);
    }


}