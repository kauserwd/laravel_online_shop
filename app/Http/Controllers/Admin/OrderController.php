<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;

class OrderController extends Controller
{
    public function index(Request $request){
    
        $orders = Order::latest('orders.created_at')->select('orders.*','users.name','users.email');
        $orders = $orders->leftjoin('users','users.id','orders.user_id');

        if($request->get('keyword')!=""){
            $orders = $orders->where('users.name','like','%'.$request->keyword.'%');
            $orders = $orders->orWhere('users.email','like','%'.$request->keyword.'%');
            $orders = $orders->orWhere('orders.id','like','%'.$request->keyword.'%');
        }

        $orders = $orders->paginate(10);

        $data['orders'] = $orders;
        return view('admin.order.list',$data);
    }
    public function details($id){
        $orders = Order::select('orders.*','countries.name as countryName')
                        ->where('orders.id',$id)
                        ->leftJoin('countries','countries.id','orders.country_id')
                        ->first();
        $orderItems = OrderItem::where('order_id',$id)->get();
        $data['orders']= $orders;
        $data['orderItems']= $orderItems;
        return view('admin.order.details',$data);
    }

    public function changeOrderStatus(Request $request, $orderId){
        $order = Order::find($orderId);
        $order->status = $request->status;
        $order->shipped_date = $request->shipped_date;
        $order->save();

        $message = 'Order Status Updated Successfully';
        session()->flash('success', $message);

            return response()->json([
                'status' => true,
                'message' => $message
            ]);
    }

    public function sendInvoiceEmail(Request $request, $orderId){
        orderEmail($orderId, $request->userType);

        $message = 'Order email sent Successfully';
        session()->flash('success', $message);

            return response()->json([
                'status' => true,
                'message' => $message
            ]);
    }
}
