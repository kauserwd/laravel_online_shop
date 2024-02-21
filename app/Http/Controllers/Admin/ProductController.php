<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;
use Image;

class ProductController extends Controller
{
    //
    public function index(Request $request){
        //$products = Product::latest('id')->with('product_images')->paginate(10);
        $products = Product::latest('id')->with('product_images');
        
        if($request->get('keyword')!= ""){
            $products = $products->where('title','like','%'.$request->keyword.'%');
        }
        
        $products = $products->paginate(10);
        return view('admin.products.list',compact('products'));
    }
// create product

    public function create(){
        $categories= Category::orderBy('category','ASC')->get();
        $brands= Brand::orderBy('name','ASC')->get();
        return view('admin.products.create',compact('categories','brands'));
    }

    public function store(Request $request){
        
            $rules = [
            'title' => 'required',
            'slug' => 'required|unique:products',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No',
            ];
            
            if(!empty($request->track_qty) && $request->track_qty =='Yes'){
                $rules['qty'] = 'required|numeric';
            }

            $validator = Validator::make($request->all(),$rules);

            if($validator->passes()){
            
                $product = new Product();
                $product->title = $request->title;
                $product->slug = $request->slug;
                $product->description = $request->description;
                $product->price = $request->price;
                $product->compare_price = $request->compare_price;
                $product->sku = $request->sku;
                $product->barcode = $request->barcode;
                $product->track_qty = $request->track_qty;
                $product->qty = $request->qty;
                $product->status = $request->status;
                $product->category_id = $request->category;
                $product->sub_category_id = $request->sub_category;
                $product->brand_id = $request->brand;
                $product->is_featured = $request->is_featured;
                $product->shipping_returns = $request->shipping_returns;
                $product->short_description = $request->short_description;
                $product->related_products = (!empty($request->related_products)) ? implode(',', $request->related_products) : '';
                $product->save();

                //Save image gallary...

                if(!empty($request->image_array)){
                    foreach ($request->image_array as $temp_image_id){

                        $tempImageInfo=TempImage::find($temp_image_id);
                        $extArray = explode('.',$tempImageInfo->image);
                        $ext = last($extArray);//like jpg.gif.png ect

                        $productImage = new ProductImage();
                        $productImage->product_id = $product->id;
                        $productImage->image = 'NULL';
                        $productImage->save();

                        $imageName = $product->id.'-'.$productImage->id.'-'.time().'.'.$ext;
                        //product_id=4;product_image=>1
                        //4-1-1122335566.jpg
                        $productImage->image = $imageName;
                        $productImage->save();

                        //generate Image thumbnail
                        //largeimage
                        $sPath = public_path().'/temp/'.$tempImageInfo->image;
                        $dPath = public_path().'/uploads/product/large/'.$imageName;
                            $image = Image::make($sPath);
                            //$img->resize(300, 200);
                            $image->resize(1100, null, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                            $image->save($dPath);


                        //smallimage
                        $dPath = public_path().'/uploads/product/small/'.$imageName;
                            $image = Image::make($sPath);
                            //$img->resize(300, 200);
                            $image->fit(600, 360);
                            $image->save($dPath);
                    }
                }
                $request->session()->flash('success','Rproduct Added Successfully');

                return response()->json([
                    'status' => true,
                    'message' => 'Product added Successfully'
                ]);

            }else{
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ]);
            }
    }

    public function edit(Request $request, $id){
        $product = Product::find($id);
        //fetch subcategory id from ajax
        $subCategories = SubCategory::where('category_id', $product->category_id)->get();

        //Fetch product images start
        $productImages = ProductImage::where('product_id',$product->id)->get();
        //Fetch product images start
        if(empty($product)){
            return redirect()->route('products.index');
        }
        // Fetch Related product
        $relatedProduct=[];
        if($product->related_products != ''){
            $productsArray = explode(',',$product->related_products);
            $relatedProduct = Product::whereIn('id', $productsArray )->get();
        }

        $categories= Category::orderBy('category','ASC')->get();
        $brands= Brand::orderBy('name','ASC')->get();
        return view('admin.products.edit', compact('categories','brands','product','subCategories','productImages','relatedProduct'));
    }
    
    public function update(Request $request, $id){


        $product = Product::find($id);


        $rules = [
            'title' => 'required',
            'slug' => 'required|unique:products,slug,'.$product->id.',id',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products,sku,'.$product->id.',id',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No',
            ];
            
            if(!empty($request->track_qty) && $request->track_qty =='Yes'){
                $rules['qty'] = 'required|numeric';
            }

            $validator = Validator::make($request->all(),$rules);

            if($validator->passes()){
            
                $product->title = $request->title;
                $product->slug = $request->slug;
                $product->description = $request->description;
                $product->price = $request->price;
                $product->compare_price = $request->compare_price;
                $product->sku = $request->sku;
                $product->barcode = $request->barcode;
                $product->track_qty = $request->track_qty;
                $product->qty = $request->qty;
                $product->status = $request->status;
                $product->category_id = $request->category;
                $product->sub_category_id = $request->sub_category;
                $product->brand_id = $request->brand;
                $product->is_featured = $request->is_featured;
                $product->shipping_returns = $request->shipping_returns;
                $product->short_description = $request->short_description;
                $product->related_products = (!empty($request->related_products)) ? implode(',', $request->related_products) : '';
                $product->save();


                $request->session()->flash('success','Rproduct Updated Successfully');

                return response()->json([
                    'status' => true,
                    'message' => 'Product Updated Successfully'
                ]);

            }else{
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ]);
        }
    }
// delete product
    public function destroy(Request $request, $id){
        $product = Product::find($id);

        if(empty($product)){
            $request->session()->flash('error','Product Not Found');

            return response()->json([
                'status' => false,
                'notFound'=> true
            ]);
        }

        $productImages = ProductImage::where('product_id',$id)->get();
        if(!empty($productImages)){
            foreach($productImages as $productImage){
            //delete image from folder
            File::delete(public_path('uploads/product/large/'.$productImage->image));
            File::delete(public_path('uploads/product/small/'.$productImage->image));
            }
            ProductImage::where('product_id',$id)->delete();
       }
        $product->delete();

        $request->session()->flash('success','Rproduct Deleted Successfully');

        return response()->json([
            'status' => true,
            'message' => 'Product image deleted Successfully'
        ]);
    }

    public function getProducts(Request $request){
        if($request->term != ""){
            $products = Product::where('title', 'like', '%'.$request->term.'%')->get();

            if($products != ""){
                foreach($products as $product){
                    $tempProduct[] = array('id'=> $product->id, 'text'=> $product->title);
                }
            }
        }

        return response()->json([
            'tags'=> $tempProduct,
            'status' => true
            
        ]);
    }
}
