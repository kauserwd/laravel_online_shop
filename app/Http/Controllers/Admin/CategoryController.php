<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;
use Image;

class CategoryController extends Controller
{
    public function index(){
        // $data['categories'] = $categories;
       /*  $categories = Category::where([
            ['name', '!=', Null],
            [function ($query) use ($request) {
                if (($keyword = $request->keyword)) {
                    $query->orWhere('name', 'LIKE', '%' . $keyword . '%')
                        ->get();
                }
            }]
        ])->paginate(6); */
        $categories = Category::latest()->paginate(10);
        return view('admin.category.list',compact('categories'));
    }


    public function create(){
        return view('admin.category.create');
    }

    // Category create 
  // Category store 
    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'category' => 'required',
            'slug' => 'required|unique:categories'
        ]);

        if($validator->passes()){
            $category = new Category();
            $category->category = $request->category;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showhome = $request->showhome;
            $category->save();

            //Save Image Here
            if(!empty($request->image_id)){
                $tempImage=TempImage::find($request->image_id);
                $extArray=explode('.',$tempImage->image);
                $ext=last($extArray);

                $newImageName=$category->id.'.'.$ext;
                $sPath=public_path().'/temp/'.$tempImage->image;
                $dPath=public_path().'/uploads/category/'.$newImageName;
                File::copy($sPath,$dPath);

                //genarate image thumbnail
                $dPath=public_path().'/uploads/category/thumb/'.$newImageName;
                $img = Image::make($sPath);
                //$img->resize(300, 200);
                $img->fit(300, 200, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($dPath);

                $category->image = $newImageName;
                $category->save();
            }

          
        
           // return redirect()->route('categories.index')->with('message','Category added successfully');
            $request->session()->flash('success', 'Category added successfully');

            return response()->json([
                'status' => true,
                'message' => 'Category added successfully'
            ]);
     

        } else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit(Request $request, $categoryId){
        $category = Category::find($categoryId);
        if(empty($category)){
            return redirect()->route('categories.index');
        }
        return view('admin.category.edit',compact('category'));
    }

    // Category Update

    public function update(Request $request, $categoryId){
        
        $category = Category::find($categoryId);
        if(empty($category)){
            $request->session()->flash('error', 'Category not found');
            return response()->json([
                'status' => false,
                'notFound'=> true,
                'message' => 'Category not found'
            ]);
        }
        
        
        $validator = Validator::make($request->all(),[
            'category' => 'required',
            'slug' => 'required|unique:categories,slug,'.$category->id.'id',
        ]);

        if($validator->passes()){
            
            $category->category = $request->category;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showhome = $request->showhome;
            $category->save();
            //this veriable used for delete old image when update 
            $oldImage = $category->image;

            //Save Image Here
            if(!empty($request->image_id)){
                $tempImage=TempImage::find($request->image_id);
                $extArray=explode('.',$tempImage->image);
                $ext=last($extArray);

                $newImageName=$category->id.'-'.time().'.'.$ext;
                $sPath=public_path().'/temp/'.$tempImage->image;
                $dPath=public_path().'/uploads/category/'.$newImageName;
                File::copy($sPath,$dPath);

                //genarate image thumbnail
                $dPath=public_path().'/uploads/category/thumb/'.$newImageName;
                $img = Image::make($sPath);
                //$img->resize(300, 200);
                $img->fit(300, 200, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($dPath);

                $category->image = $newImageName;
                $category->save();

                //Delete Old image
                File::delete(public_path().'/uploads/category/thumb/'.$oldImage);
                File::delete(public_path().'/uploads/category/'.$oldImage);
                File::delete(public_path().'/temp/'.$oldImage);
            }

          
        
           // return redirect()->route('categories.index')->with('message','Category added successfully');
            $request->session()->flash('success', 'Category updated successfully');

            return response()->json([
                'status' => true,
                'message' => 'Category updated successfully'
            ]);
     

        } else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy(Request $request, $categoryId){
        $category = Category::find($categoryId);
        if(empty($category)){
            $request->session()->flash('error', 'Category not found');
            return response()->json([
                'status' => true,
                'message' => 'Category not  found'
            ]);
        }
        //Delete Old image
        File::delete(public_path().'/uploads/category/thumb/'.$category->image);
        File::delete(public_path().'/uploads/category/'.$category->image);
        File::delete(public_path().'/temp'.$category->image);

        $category->delete();

        $request->session()->flash('success', 'Category deleted successfully');


        return response()->json([
            'status' => true,
            'message' => 'Category deleted Successfully'
        ]);
    }
}