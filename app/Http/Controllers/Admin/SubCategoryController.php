<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\SubCategory;

class SubCategoryController extends Controller
{
    //
    public function index(Request $request){
       $subCategories = SubCategory::select('sub_categories.*','categories.category as categoryName')
        ->latest('sub_categories.id')
        ->leftJoin('categories','categories.id','sub_categories.category_id');
        if (!empty($request->get('keyword'))) {
            $subCategories = $subCategories->where('sub_categories.name', 'like', '%' . $request->get('keyword') . '%');
            $subCategories = $subCategories->orWhere('categories.categories', 'like', '%' . $request->get('keyword') . '%');
        }

        $subCategories = $subCategories->paginate(10);

        return view('admin.subcategory.list',compact('subCategories'));
    }
// subcategory create
    public function create(){
        $categories = Category::orderBy('category','ASC')->get();
        return view('admin.subcategory.create', compact('categories'));
    }
// subcategory store
    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category' => 'required',
            'status' => 'required'
        ]);

        if($validator->passes()){
            $subCategories = new SubCategory();
            $subCategories->name = $request->name;
            $subCategories->slug = $request->slug;
            $subCategories->status = $request->status;
            $subCategories->showhome = $request->showhome;
            $subCategories->category_id = $request->category;
            $subCategories->save();

            $request->session()->flash('success', 'Sub Category added successfully');

            return response([
                'status' => true,
                'message' => 'Sub Category added successfully'
            ]);

        }else{
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function edit(Request $request, $id){
        $subCategory = SubCategory::find($id);
        if(empty($subCategory)){
            return redirect()->route('sub-categories.index');
        }
        
        $categories = Category::orderBy('category','ASC')->get();
        return view('admin.subcategory.edit', compact('categories','subCategory'));
    }

    //sub category update
    public function update(Request $request, $id){

        $subCategory = SubCategory::find($id);
        if(empty($subCategory)){
            $request->session()->flash('error', 'Sub Category not found');
            return response([
                'status' => false,
                'notFound' => true
            ]);
        }

            $validator = Validator::make($request->all(),[
                'name' => 'required',
                //'slug' => 'required|unique:sub_categories',
                'slug' => 'required|unique:sub_categories,slug,'.$subCategory->id.',id',
                'category' => 'required',
                'status' => 'required'
            ]);
    
            if($validator->passes()){
                
                $subCategory->name = $request->name;
                $subCategory->slug = $request->slug;
                $subCategory->status = $request->status;
                $subCategory->showhome = $request->showhome;
                $subCategory->category_id = $request->category;
                $subCategory->save();
    
                $request->session()->flash('success', 'Sub Category updated successfully');
    
                return response([
                    'status' => true,
                    'message' => 'Sub Category updated successfully'
                ]);
    
            }else{
                return response([
                    'status' => false,
                    'errors' => $validator->errors()
                ]);
            }
    }
    //Delete sub category
    public function destroy(Request $request, $id){
        $subCategory = SubCategory::find($id);
        if(empty($subCategory)){
            $request->session()->flash('error', ' sub Category not found');
            return response([
                'status' => false,
                'notFound' => true
            ]);
        }
        
        $subCategory->delete();

        $request->session()->flash('success','Sub Category deleted successfully');


        return response()->json([
            'status' => true,
            'message' => 'Sub Category deleted Successfully'
        ]);
    }

}
