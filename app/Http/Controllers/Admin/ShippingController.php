<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShippingCharge;
use App\Models\Country;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{
    //
    public function create(){

        $countries = Country::get();
        $shippingCharges = ShippingCharge::select('shipping_charges.*','countries.name')
                        ->leftJoin('countries','countries.id','shipping_charges.country_id')->get();
        $data['countries'] = $countries;
        $data['shippingCharges'] = $shippingCharges;
        return view('admin.shipping.create', $data);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'country' => 'required',
            'amount' => 'required|numeric'
        ]);

        if($validator->passes()){

            $count = ShippingCharge::where('country_id', $request->country)->count();
            if($count>0){
                session()->flash('error', 'Shipping already added');
                return response()->json([
                    'status' => true,
                ]);
            }

            $shipping = new ShippingCharge;
            $shipping->country_id= $request->country;
            $shipping->amount= $request->amount;
            $shipping->save();

            session()->flash('success', 'Shipping charge added successfully');

            return response()->json([
                'status' => true,
                'message' => 'Shipping charge added successfully'
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($id){

        $shippingCharge = ShippingCharge::find($id);

        $countries = Country::get();
        $data['countries'] = $countries;
        $data['shippingCharge'] = $shippingCharge;

        return view('admin.shipping.edit', $data);
    }

    public function update(Request $request, $id){
        
        $validator = Validator::make($request->all(),[
            'country' => 'required',
            'amount' => 'required|numeric'
        ]);

        if($validator->passes()){

            $shipping = ShippingCharge::find($id);
            $shipping->country_id= $request->country;
            $shipping->amount= $request->amount;
            $shipping->save();

            session()->flash('success', 'Shipping charge Updated successfully');

            return response()->json([
                'status' => true,
                'message' => 'Shipping charge Updated successfully'
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($id){

        $shippingCharges = ShippingCharge::find($id);
        if(empty($shippingCharges)){
            session()->flash('error', 'Shipping not found');
            return response()->json([
                'status' => true,
            ]);
        }
        

        $shippingCharges->delete();

        $request->session()->flash('success', 'Shipping deleted successfully');
        return response()->json([
            'status' => true,
        ]);
    }

}
