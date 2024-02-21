<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DiscountCoupon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class DiscountCode extends Controller
{
    //
    public function index(Request $request){
        $discountCoupons = DiscountCoupon::latest();

        if(!empty($request->get('keyword'))){
            $discountCoupons = $discountCoupons->where('name', 'like','%'.$request->get('keyword').'%');
        }

        $discountCoupons = $discountCoupons->paginate(10);
        return view('admin.coupon.list',compact('discountCoupons'));
    }

    public function create(){
        return view('admin.coupon.create');
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(),[
            'code' => 'required',
            'type' => 'required',
            'discount_amount' => 'required|numeric',
            'status' => 'required'
            
        ]);

        if($validator->passes()){

            //starting date must be grater then current date
            if(!empty($request->starts_at)){
                $now = Carbon::now();
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->starts_at);

                //lte- less then equal method
                if($startAt->lte($now) == true){
                    return response()->json([
                        'status' => false,
                        'errors' => ['starts_at'=>'Start date can not be less then current date time']
                    ]);
                }
            }


            //expiry date must be grater then start date
            if(!empty($request->starts_at) && !empty($request->expires_at)){
                $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->expires_at);
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->starts_at);

                //gt- grater then equal method
                if($expiresAt->gt($startAt) == false){
                    return response()->json([
                        'status' => false,
                        'errors' => ['expires_at'=>'Start date can not be less then current date time']
                    ]);
                }
            }

            $discountCode= new DiscountCoupon;
            $discountCode->code= $request->code;
            $discountCode->name= $request->name;
            $discountCode->description= $request->description;
            $discountCode->max_uses= $request->max_uses;
            $discountCode->max_uses_user= $request->max_uses_user;
            $discountCode->type= $request->type;
            $discountCode->discount_amount= $request->discount_amount;
            $discountCode->min_amount= $request->min_amount;
            $discountCode->status= $request->status;
            $discountCode->starts_at= $request->starts_at;
            $discountCode->expires_at= $request->expires_at;
            $discountCode->save();

            $message = 'Discount price added successfully';
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
// edit coupons
    public function edit(Request $request, $id){
        $discountCoupon = DiscountCoupon::find($id);
        if($discountCoupon == null){
            $message = 'Record not found';
            session()->flash('error',$message);
            return redirect()->route('coupons.index');
        }
        $data['discountCoupon'] = $discountCoupon;
        return view('admin.coupon.edit',$data);
    }
// update coupons
    public function update(Request $request, $id){

        $discountCode = DiscountCoupon::find($id);

        if($discountCode == null){
            $message = 'Record not found';
            session()->flash('error',$message);
            return response()->json([
                'status' => true,
            ]);
        }

        $validator = Validator::make($request->all(),[
            'code' => 'required',
            'type' => 'required',
            'discount_amount' => 'required|numeric',
            'status' => 'required'
            
        ]);

        if($validator->passes()){

            //starting date must be grater then current date
            
            //expiry date must be grater then start date
            if(!empty($request->starts_at) && !empty($request->expires_at)){
                $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->expires_at);
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->starts_at);

                //gt- grater then equal method
                if($expiresAt->gt($startAt) == false){
                    return response()->json([
                        'status' => false,
                        'errors' => ['expires_at'=>'Start date can not be less then current date time']
                    ]);
                }
            }

            $discountCode->code= $request->code;
            $discountCode->name= $request->name;
            $discountCode->description= $request->description;
            $discountCode->max_uses= $request->max_uses;
            $discountCode->max_uses_user= $request->max_uses_user;
            $discountCode->type= $request->type;
            $discountCode->discount_amount= $request->discount_amount;
            $discountCode->min_amount= $request->min_amount;
            $discountCode->status= $request->status;
            $discountCode->starts_at= $request->starts_at;
            $discountCode->expires_at= $request->expires_at;
            $discountCode->save();

            $message = 'Discount price updated successfully';
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

    public function destroy(Request $request, $id){
        //
        $discountCode = DiscountCoupon::find($id);

        if($discountCode == null){
            $message = 'Record not found';
            session()->flash('error',$message);
            return response()->json([
                'status' => true,
            ]);
        }
        $discountCode->delete();

        $message = 'Discount Coupon deleted successfully';
            session()->flash('success',$message);
            return response()->json([
                'status' => true,
            ]);
    }
}
