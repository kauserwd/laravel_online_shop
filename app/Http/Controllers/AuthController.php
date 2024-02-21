<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\CustomerAddress;
use App\Models\Country;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //user registration
    public function login(){
        return view('front.account.login');
    }
    public function register(){
        return view('front.account.register');
    }

    public function processRegister(Request $request){

        $validator = Validator::make($request->all(),[
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:5|confirmed'
        ]);

        if($validator->passes()){

            $user = new User;
            $user->name= $request->name;
            $user->email= $request->email;
            $user->password= Hash::make($request->password);
            $user->save();

            session()->flash('success','You have been registerd successfully.');

            return response()->json([
                'status' => true,
                'message' => 'Registration successful'
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

    }
  
    public function authenticate(Request $request){
        //user login
        $validator = validator::make($request->all(),[

            'email'=> 'required|email',
            'password'=> 'required'

        ]);
        if($validator->passes()){

            if(Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))){
                
                if(session()->has('url.intended')){
                    return redirect(session()->get('url.intended'));
                }
            
                return redirect()->route('account.profile');
            }else{
                return redirect()->route('account.login')
                ->withInput($request->only('email'))
                ->with('error','Either Email/Password is incorrect');
            }

        }else{
            return redirect()->route('account.login')
            ->withErrors($validator)
            ->withInput($request->only('email'));
        }
    }

    public function profile(){

        $userId = Auth::user()->id;

        $countries = Country::orderBy('name','ASC')->get();

        $user = User::where('id',Auth::user()->id)->first();

        $address = CustomerAddress::where('user_id',$userId)->first();

        $data['user'] = $user;
        $data['countries'] = $countries;
        $data['address'] = $address;
        return view('front.account.profile',$data);
    }

    public function updateProfile(Request $request){
        $userId= Auth::user()->id;
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$userId.',id',
            'phone' => 'required'
        ]);

        if($validator->passes()){

            $user = User::find($userId);
            $user->name=$request->name;
            $user->email=$request->email;
            $user->phone=$request->phone;
            $user->save();

            $message = 'Your profile updated successfully';
            session()->flash('success',$message);

            return response()->json([
                'status' => true,
                'message' => $message
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]); 
        }

    }
    public function updateAddress(Request $request){
        $userId= Auth::user()->id;

        $validator = Validator::make($request->all(),[

            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'country_id' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'phone' => 'required'
            
        ]);

        if($validator->passes()){

            CustomerAddress::updateOrCreate(
                ['user_id' => $userId],
    
                [
                    'user_id' => $userId,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'country_id' => $request->country_id,
                    'address' => $request->address,
                    'appartment' => $request->appartment,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip' => $request->zip,
                    
                ],
            );

            $message = 'Your profile updated successfully';
            session()->flash('success',$message);

            return response()->json([
                'status' => true,
                'message' => $message
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]); 
        }

    }

    public function logout(){
        Auth::logout();
        return redirect()->route('account.login')->with('success','You successfully logged out!');
    }

    public function order(){

        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->orderBy('created_at','DESC')->get();
        
        $data['orders'] = $orders;

        return view('front.account.order',$data);
    }

    public function orderDetails($id){
        
        $data = [];
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)->where('id',$id)->first();
        
        $data['order'] = $order;

        $orderItems = OrderItem::where('order_id',$id)->get();
        $orderItemsCount = OrderItem::where('order_id',$id)->count();
        $data['orderItems'] = $orderItems;
        $data['orderItemsCount'] = $orderItemsCount;

        return view('front.account.orderdetails',$data);
    }

    public function wishlist(){
        $wishlists = Wishlist::where('user_id', Auth::user()->id)->with('product')->get();
        
        $data['wishlists'] = $wishlists;
        return view('front.account.wishlist',$data);
    }

    public function removeProductFromWishlist(Request $request){
        $wishlist = Wishlist::where('user_id', Auth::user()->id)
                                ->where('product_id', $request->id)->first();
        if($wishlist == null){
            session()->flash('error','Product already removed');
            return response()->json([
                'status' => true,
            ]);
        }else{
            $wishlist = Wishlist::where('user_id', Auth::user()->id)
            ->where('product_id', $request->id)->delete();

            session()->flash('success','Product removed successfully');

            return response()->json([
                'status' => true,
            ]);
        }
    }

    public function showChangePassword(){
        return view('front.account.changepassword');
    }

    public function processChangePassword(Request $request){
        $validator = Validator::make($request->all(),[

            'old_password' => 'required',
            'new_password' => 'required|min:5',
            'confirm_password' => 'required|same:new_password' 
            
        ]);

        if($validator->passes()){
            $user = User::select('id','password')->where('id',Auth::user()->id)->first();

            if(Hash::check($request->old_password, $user->password)){
                session()->flash('error','Your old passeord is incorrect,please try again.');
                return response()->json([
                    'status' => true,
                ]);
            }

            User::where('id',$user->id)->update([
                'password' =>Hash::make($request->new_password)
            ]);

            session()->flash('success','You have successfully change your password');

            return response()->json([
                'status' => true,
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
}
