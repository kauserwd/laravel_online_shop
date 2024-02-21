<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductImage;
use Illuminate\Support\Facades\File;
use Image;

class ProductImageController extends Controller
{
    //
    public function update(Request $request){

        $image = $request->image;
        $ext = $image->getClientOriginalExtension();
        $sPath = $image->getPathName();//auto image location dibe ai function

        $productImage = new ProductImage();
        $productImage->product_id = $request->product_id;
        $productImage->image = 'NULL';
        $productImage->save();

        $imageName = $request->product_id.'-'.$productImage->id.'-'.time().'.'.$ext;
        //product_id=4;product_image=>1
        //4-1-1122335566.jpg
        $productImage->image = $imageName;
        $productImage->save();


        //largeimage
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

        return response()->json([
            'status' => true,
            'image_id' => $productImage->id,
            'ImagePath' => asset('uploads/product/small/'.$productImage),
            'message' => 'Product image added Successfully'
        ]);
    }

    public function destroy(Request $request){
        $productImage = ProductImage::find($request->id);

        if(empty($productImage)){
            return response()->json([
                'status' => false,
                'message' => 'Image not Found'
            ]);
        }

        //delete image from folder
        File::delete( public_path('uploads/product/large/'.$productImage->image));
        File::delete( public_path('uploads/product/small/'.$productImage->image));
        
        $productImage->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product image delted Successfully'
        ]);
    }
}
