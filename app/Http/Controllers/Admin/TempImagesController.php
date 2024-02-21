<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TempImage;
use Image;

class TempImagesController extends Controller
{
    //
    public function create(Request $request){
        $image = $request->image;

        if(!empty($image)){
            $ext = $image->getClientOriginalExtension();
            $newName = time().'.'.$ext;

            $tempImage = new TempImage();
            $tempImage->image = $newName;
            $tempImage->save();

            $image->move(public_path().'/temp',$newName);

            //Generate image thumbnail for create poroduct....


            $sourcePath = public_path().'/temp/'.$newName;
            $destPath = public_path().'/temp/thumb/'.$newName;
                $image = Image::make($sourcePath);
                //$img->resize(300, 200);
                $image->fit(300, 200, function ($constraint) {
                    $constraint->upsize();
                });
                $image->save($destPath);

           //Generate end section  image thumbnail for create poroduct....

            return response()->json([
                'status' => true,
                'image_id' => $tempImage->id,
                'ImagePath' => asset('/temp/thumb/'.$newName),
                'message' => 'Image added successfully'
            ]);

        }
    }
}
