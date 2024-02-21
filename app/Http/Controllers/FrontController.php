<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\Page;
use Illuminate\Support\Facades\Auth;

class FrontController extends Controller
{
    //
    public function index(){

        $products= Product::where('is_featured','Yes')->orderBy('id','DESC')
                                                    ->where('status',1)
                                                    ->take(8)->get();
        $latestproducts= Product::orderBy('id','DESC')
                                ->where('status',1)
                                ->take(8)->get();
        $data['featuredProducts'] = $products;
        $data['latestProducts'] = $latestproducts;
        //compact('products')
        return view('front.home',$data);
    }
    
    public function addToWishlist(Request $request){
        if(Auth::check()==false){
            session(['url.intended'=>url()->previous()]);
            return response()->json([
                'status'=> false
            ]);
        }
        $product = Product::where('id',$request->id)->first();
        if($product == null){
            return response()->json([
                'status'=> true,
                'message'=>'<div class="alert alert-danger">Product not found.</div>'
            ]);
        }
        // same product jeno bar bar add na hoy sei jonno

        Wishlist::updateOrCreate(
            [
                'user_id'=> Auth::user()->id,
                'product_id'=> $request->id,
            ],
            [
                'user_id'=> Auth::user()->id,
                'product_id'=> $request->id,
            ]
        );
        //$wishlist = new Wishlist;
        //$wishlist->user_id= Auth::user()->id;
        //$wishlist->product_id= $request->id;
        //$wishlist->save();

        return response()->json([
            'status'=> true,
            'message'=>'<div class="alert alert-success"><strong>"'.$product->title.'"</strong>added in your wishlist</div>'
        ]);
    }

    public function page($slug){
        $page = Page::where('slug',$slug)->first();
        if($page == null){
            abort(404);
            
            return response()->json([
                'page' => $page
            ]);
    
        }
        
        $data['page'] = $page;
        return view('front.page',$data);
    }
}
