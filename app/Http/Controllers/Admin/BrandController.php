<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Brand;

class BrandController extends Controller
{
    //Brand list
    public function index(Request $request){
        $brands = Brand::latest('id');

        if($request->get('keyword')){
            $brands = $brands->where('name', 'like', '%'.$request->get('keyword'). '%');
        }

        $brands = $brands->paginate(10);

        return view('admin.brands.list',compact('brands'));
    }
    // brand create
    public function create(){
        return view('admin.brands.create');
    }
    // brand store
    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands',
            'status' => 'required'
    ]);

        if($validator->passes()){
            $brand = new Brand();
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            $request->session()->flash('success', 'Brand added successfully');

            return response()->json([
                'status' => true,
                'message' => 'Brand added successfully'
            ]);

        } else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    

    }

    // brand edit

    public function edit(Request $request, $id){
        $brand = Brand::find($id);
        if(empty($brand)){
            $request->session()->flash('error', 'Brand not found');
            return redirect()->route('brands.index');
        }
        return view('admin.brands.edit',compact('brand'));
    }

        // brand update
    public function update(Request $request, $id){

        $brand = Brand::find($id);
        if(empty($brand)){
            $request->session()->flash('error', 'No Brands found');
            return response([
                'status' => false,
                'notFound' => true
            ]);
        }

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,'.$brand->id.'id',
        ]);

        if($validator->passes()){

            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;

            $brand->save();

            $request->session()->flash('success', 'Brand updated successfully');

            return response()->json([
                'status' => true,
                'message' => 'Brand updated successfully'
            ]);

        } else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

    }

    //Delete Brand
    public function destroy(Request $request, $id){
        $brand = Brand::find($id);
        if(empty($brand)){
            $request->session()->flash('error', ' No Brand found');
            return response([
                'status' => false,
                'notFound' => true
            ]);
        }
        
        $brand->delete();

        $request->session()->flash('success','Brand deleted successfully');


        return response()->json([
            'status' => true,
            'message' => 'Brand deleted Successfully'
        ]);
    }

}
