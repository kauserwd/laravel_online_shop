<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Page;

class PageController extends Controller
{
    //
    public function index(Request $request){
        $pages= Page::latest();

        if($request->keyword !=''){
            $pages = $pages->where('name','like','%'.$request->keyword.'%');
        }
        
        $pages= $pages->paginate(10);


        $data['pages'] = $pages;
        return view('admin.pages.list',$data);
    }
    public function create(){
        
        return view('admin.pages.create');
    }
    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required'
        ]);
            //validator passes and fails duivabei kora jay 
        if($validator->fails()){
            return response()->json([
                'status' => false,//passes er khetre true hobe  and fails er jonno false hobe
                'errors' => $validator->errors()
            ]);
        }

        $page = new Page;
        $page->name = $request->name;
        $page->slug = $request->slug;
        $page->content = $request->content;
        $page->save();

        $message = 'Your page created successfully.';
        session()->flash('success',$message);

        return response()->json([
            'status' => true,
            'message' => $message
        ]);

        
    }
    public function edit($id){
        $page = Page::find($id);

        if($page==null){
            $message = 'Page not found';
            session()->flash('error',$message);
            return redirect()->route('pages.index');
        }

        $data['page'] = $page;
        return view('admin.pages.edit',$data);
    }

    public function update(Request $request,$id){
        $page = Page::find($id);
        if($page == null){
            session()->flash('error','Page not found');
            return response()->json([
                'status' => true,
            ]);
        }

        
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required'
        ]);
            //validator passes and fails duivabei kora jay 
        if($validator->fails()){
            return response()->json([
                'status' => false,//passes er khetre true hobe  and fails er jonno false hobe
                'errors' => $validator->errors()
            ]);
        }

        $page->name = $request->name;
        $page->slug = $request->slug;
        $page->content = $request->content;
        $page->save();

        $message = 'Your page updated successfully.';
        session()->flash('success',$message);

        return response()->json([
            'status' => true,
            'message' => $message
        ]);
        
    }
    public function destroy($id){
        
        $page = Page::find($id);

        if($page == null){
            session()->flash('error','Page Not Found');

            return response()->json([
                'status' => true,
            ]);
        }  
        $page->delete();

        $message = 'Page deleted successfully';
        session()->flash('success',$message);

        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }
    
}
