<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\Product;
use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\ShippingCharge;
use App\Models\DiscountCoupon;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;


class CartController extends Controller
{
    //Cart page add korbe
    public function addToCart(Request $request){
        $product = Product::with('product_images')->find($request->id);
        if($product == null){
            return response()->json([
                'status'=> false,
                'message'=> 'Product no Found'
            ]);

        }

        if(Cart::count() > 0){
            // product found in cart
            // chek if this product already in the cart
            // return as message that product already added in cart
            //if product not found in cart then add product in cart 

            $cartContent = Cart::content();
            $productAlreadyExist = false;
            foreach($cartContent as $item){
                if($item->id == $product->id){
                    $productAlreadyExist = true;
                }
            }
            if($productAlreadyExist == false){
                Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);

                $status = true;
                $message = '<strong>'.$product->title.'</strong> Added in your cart successfully';
                session()->flash('success', $message);
            }else{
                $status = false;
                $message = $product->title.'Already added in cart';   
            }

        }else{
            //cart is empty
            //Cart::add('293ad', 'Product 1', 1, 9.99);
            Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);

            $status = true;
            $message = '<strong>'.$product->title.'</strong> Added in your cart successfully';
            session()->flash('success', $message);
        } 
        
        return response()->json([
            'status' => $status,
            'message' => $message

        ]);
    }


    //Cart page load korbe
    public function cart(){

        $cartContent = Cart::content();
        $data['cartContent'] = $cartContent;
        return view('front.cart',$data);
    }

    public function updateCart(Request $request){
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);
        $product = Product::find($itemInfo->id);

        //check track qty
        if($product->track_qty == 'yes'){
            if($qty <= $product->qty){
                Cart::update($rowId,$qty);
                $message = 'Cart update successfully';
                $status = true;
                session()->flash('success', $message);
            }else{
                $message = 'Requested qty('.$qty.') not available in stock!';
                $status = false;
                session()->flash('error', $message);
            }
        }else{
            Cart::update($rowId, $qty);
            $message = 'Cart update successfully';
            $status = true;
            session()->flash('success', $message);
        } 
        return response()->json([
            'status' => $status,
            'message' => $message

        ]);
    }

    public function deleteItem(Request $request){

        $itemInfo = Cart::get($request->rowId);
        
        if($itemInfo == null){
        $message = 'Item not Found in Cart';
        session()->flash('error', $message);

            return response()->json([
                'status'=> false,
                'message'=> $message
            ]);

        }

        Cart::remove($request->rowId);
        $message = 'Item remove from cart Successfully.';

        session()->flash('success', $message);

        return response()->json([
            'status' => true,
            'message' => $message

        ]);
    }

    public function checkout(){

        $discount =0;//initialize discount;

        // if cart is empty redirect to cart page
        //cart page product thakei kebol checkout page access korte parbe 
        // jodi cart empty thake tobe ai condition kaj korbe and aita must be add korte hobe 

        if(Cart::count() == 0){
            return redirect()->route('front.cart');
        }

        // if user is not logged in  redirect to login page

        if(Auth::check()== false){
            // current url e jabe user je page theke login korbe login korer por oi same page jabe 
            //aijonno intended function use kora hoyeche
            if(!session()->has('url.intended')){
                session(['url.intended' => url()->current()]);
            }
            
            return redirect()->route('account.login');
        }

        $customerAddress = CustomerAddress::where('user_id',Auth::user()->id)->first();

        session()->forget('url.intended');

        //load country
        $countries = Country::orderBY('name','ASC')->get();

        //Apply Discount Here
        $subTotal = Cart::subtotal(2,'.','');
        if(session()->has('code')){
            $couponCode = session()->get('code');

            if($couponCode->type == 'percent'){
                $discount = ($couponCode->discount_amount/100)*$subTotal;
            }else{
                $discount = $couponCode->discount_amount;
            }
        }
        //calculate shipping here

        if($customerAddress != ''){
            //$userCountry = $customerAddress->country_id;

            $shippingInfo = ShippingCharge::where('country_id', $customerAddress->country_id)->first();
    
            //echo $shippingInfo->amount;
            $subTotal = Cart::subtotal(2,'.','');
            $totalQty = 0;
            $totalShippingCharge = 0;
            $grandTotal = 0;
            foreach(Cart::content() as $item){
                $totalQty +=$item->qty;
            }
            
            $totalShippingCharge = $totalQty*$shippingInfo->amount;
            $grandTotal = ($subTotal-$discount)+$totalShippingCharge;
    
        }else{
            $grandTotal = ($subTotal-$discount);
            $totalShippingCharge =0;
        }

        
        return view('front.checkout',[
            'countries'=> $countries,
            'customerAddress' => $customerAddress,
            'discount' => $discount,
            'totalShippingCharge'=>$totalShippingCharge,
            'grandTotal'=>$grandTotal
        ]);
    }

    //process checkout
    public function processCheckout(Request $request){
        // step one form validation
        $validator = Validator::make($request->all(),[

            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'country' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'phone' => 'required'
            
        ]);

        if($validator->fails()){
            
            return response()->json([
                'message' => 'check your errors',
                'status' => true, 
                'errors' => $validator->errors()
            ]);

        }

        // step 2 - save user address

        $user = Auth::user();

        CustomerAddress::updateOrCreate(
            ['user_id' => $user->id],

            [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'country_id' => $request->country,
                'address' => $request->address,
                'appartment' => $request->appartment,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                
            ],
        );

        // step 3 - save data in order table 

        if(($request->payment_method == 'cod')){

            $discountCodeId = NULL;
            $promoCode = '';
            $shipping = 0;
            $discount = 0;
            $subTotal = Cart::subtotal(2, '.', '');
            //$grandTotal = $subTotal+$shipping;

             //Apply Discount Here
              if(session()->has('code')){
                $couponCode = session()->get('code');
    
                if($couponCode->type == 'percent'){
                    $discount = ($couponCode->discount_amount/100)*$subTotal;
                }else{
                    $discount = $couponCode->discount_amount;
                }

                $discountCodeId = $couponCode->id;
                $promoCode = $couponCode->code;
            }

            //calculate shipping

            $shippingInfo = ShippingCharge::where('country_id',$request->country)->first();

            $totalQty = 0;
            foreach(Cart::content() as $item){
                $totalQty +=$item->qty;
            }

            if($shippingInfo != null){

                $shipping = $totalQty*$shippingInfo->amount;
                $grandTotal = ($subTotal-$discount)+$shipping;
            }else{
                $shippingInfo = ShippingCharge::where('country_id','rest_of_world')->first();
                $shipping = $totalQty*$shippingInfo->amount;
                $grandTotal = ($subTotal-$discount)+$shipping;
            }

           

            $order = new Order;
            $order->subtotal =  $subTotal;
            $order->shipping =  $shipping;
            $order->grand_total =  $grandTotal;
            $order->discount =  $discount;
            $order->coupon_code_id=  $discountCodeId;
            $order->coupon_code =  $promoCode;
            $order->payment_status =  'not paid';
            $order->status =  'pending';
            $order->user_id =  $user->id;
            $order->first_name =  $request->first_name;
            $order->last_name =  $request->last_name;
            $order->email =  $request->email;
            $order->phone =  $request->phone;
            $order->country_id =  $request->country;
            $order->address =  $request->address;
            $order->appartment =  $request->appartment;
            $order->city =  $request->city;
            $order->state =  $request->state;
            $order->zip =  $request->zip;
            $order->notes =  $request->notes;
            $order->save();
            
            // step 4 - order items in order items table 
            foreach(Cart::content() as $item){
                $orderItem = new OrderItem;
                $orderItem->product_id = $item->id;
                $orderItem->order_id = $order->id;
                $orderItem->name = $item->name;
                $orderItem->qty = $item->qty;
                $orderItem->price = $item->price;
                $orderItem->total = $item->price*$item->qty;
                $orderItem->save();

                //update product stock
                $productData = Product::find($item->id);
                if($productData->track_qty == 'yes'){
                    $currentQty = $productData->qty;
                    $updatedQty = $currentQty - $item->qty;
                    $productData->qty = $updatedQty;
                    $productData->save();
                }
                
            }

            // send order email
            orderEmail($order->id,'customer');

            session()->flash('success','You have successfully placed your order.');

            Cart::destroy();
        

            return response()->json([
                'message' => 'Order save successfully.',
                'orderId' => $order->id,
                'status' => true, 
            ]);
            


        }else{
            //
        }
    }

    public function thankyou($id){
        //
        return view('front.layouts.thanks',[
            'id' => $id
        ]);
    }

    //using ajax return order summary when change country

    public function getOrderSummery(Request $request){
        $subTotal = Cart::subtotal(2,'.','');
        $discount = 0;
        $discountString ='';

        //Apply Discount Here
        if(session()->has('code')){
            $couponCode = session()->get('code');

            if($couponCode->type == 'percent'){
                $discount = ($couponCode->discount_amount/100)*$subTotal;
            }else{
                $discount = $couponCode->discount_amount;
            }

            $discountString = '<div class=" mt-4" id="discount-response">
            <strong> '.session()->get('code')->code.'</strong>
            <a class="btn btn-sm btn-danger" id="remove-discount"><i class="fa fa-times"></i></a>
            </div>';
        }
        //Apply Discount end Here
        if($request->country_id > 0 ){

            $shippingInfo = ShippingCharge::where('country_id',$request->country_id)->first();

            $totalQty = 0;
            foreach(Cart::content() as $item){
                $totalQty +=$item->qty;
            }

            if($shippingInfo != null){

                $shippingCharge = $totalQty*$shippingInfo->amount;
                $grandTotal = ($subTotal-$discount)+$shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal,2),
                    'discount' => number_format($discount,2),
                    'discountString' => $discountString, 
                    'shippingCharge' => number_format($shippingCharge,2),  
                ]);
            }else{
                $shippingInfo = ShippingCharge::where('country_id','rest_of_world')->first();
                $shippingCharge = $totalQty*$shippingInfo->amount;
                $grandTotal = ($subTotal-$discount)+$shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal,2),
                    'discount' => number_format($discount,2),
                    'discountString' => $discountString, 
                    'shippingCharge' => number_format($shippingCharge,2),  
                ]);

            }
        }else{
            return response()->json([
                'status' => true,
                'grandTotal' => number_format(($subTotal-$discount),2),
                'discount' => number_format($discount,2), 
                'discountString' => $discountString,
                'shippingCharge' => number_format(0,2),  
            ]);
        }
    }
/// apply discount coupon

    public function applyDiscount(Request $request){

        $couponCode = DiscountCoupon::where('code',$request->code)->first();

        if($couponCode == null){
            return response()->json([
                'status' => false, 
                'message' => 'Invalid discount coupon', 
            ]);
        }

        // check if coupon start date is valid of not

        $now = Carbon::now();
        //$now->format('Y-m-d H:i:s');
         //starting date must be grater then current date
            if($couponCode->starts_at != ""){
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s',$couponCode->starts_at);

            //lte- less then method
            if($now->lt($startDate)){
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Discount Coupon',
                ]);
            }
        }


        if($couponCode->expires_at != ""){
            $endDate = Carbon::createFromFormat('Y-m-d H:i:s',$couponCode->expires_at);

            //lte- grater then  method
            if($now->gt($endDate)){
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Discount Coupon',
                ]);
            }
        }
        //coupon code count Max uses check 
        if($couponCode->max_uses > 0){
            $couponUsed = Order::where('coupon_code_id', $couponCode->id)->count();
            if($couponUsed >= $couponCode->max_uses){
                return response()->json([
                    'status' => false,
                    'message' => 'Coupon Code use limit exided',
                ]);
            }
        }
        
        //coupon code count Max uses user check 
        if($couponCode->max_uses_user > 0){
            $couponUsedByUser = Order::where(['coupon_code_id'=> $couponCode->id, 'user_id'=> Auth::user()->id])->count();
            if($couponUsedByUser >= $couponCode->max_uses_user){
                return response()->json([
                    'status' => false,
                    'message' => 'You already use coupon code.',
                ]);
            }
        }
        
        $subTotal = Cart::subtotal(2,'.','');
        //min amount condition check
        if($couponCode->min_amount > 0){
            if($subTotal < $couponCode->min_amount){
                return response()->json([
                    'status' => false,
                    'message' => 'Your min amount must be '.$couponCode->min_amount.'.tk',
                ]);
            }
        }
        //coupon code count 
        session()->put('code',$couponCode);
        return $this->getOrderSummery($request);

    }

    //delete coupon
    public function removeCoupon(Request $request){
        session()->forget('code');
        return $this->getOrderSummery($request);
    }
}
