<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TempImagesController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\autosub\PostSubCategoryController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\DiscountCode;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PageController;

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/* Route::get('/', function () {
    return view('welcome');
});
 */
Route::get('/', [FrontController::class,'index'])->name('front.home');
//firste ;/shop use kore product show korbo then slug ba url set korbo 
Route::get('/shop/{categorySlug?}/{subCategorySlug?}', [ShopController::class,'index'])->name('front.shop');
Route::get('/product/{slug}', [ShopController::class,'product'])->name('front.product');
Route::get('/cart', [CartController::class,'cart'])->name('front.cart');
Route::post('/add-to-cart', [CartController::class,'addToCart'])->name('front.addToCart');
Route::post('/update-cart', [CartController::class,'updateCart'])->name('front.updateCart');
Route::post('/delete-item', [CartController::class,'deleteItem'])->name('front.deleteItem.cart');
Route::get('/checkout', [CartController::class,'checkout'])->name('front.checkout');
Route::post('/process-checkout', [CartController::class,'processCheckout'])->name('front.processCheckout');
Route::get('/thanks/{orderId}', [CartController::class,'thankyou'])->name('front.thankyou');
Route::post('/get-order-summery', [CartController::class,'getOrderSummery'])->name('front.getOrderSummery');
Route::post('/apply-discount', [CartController::class,'applyDiscount'])->name('front.applyDiscount');
Route::post('/remove-discount', [CartController::class,'removeCoupon'])->name('front.removeCoupon');
Route::post('/add-to-wishlist', [FrontController::class,'addToWishlist'])->name('front.addToWishlist');
Route::get('/page/{slug}', [FrontController::class,'page'])->name('front.page');




Route::post('/process-register', [AuthController::class,'processRegister'])->name('account.processRegister');
Route::get('/login', [AuthController::class,'login'])->name('account.login');

// User middle ware

Route::group(['prefix' => 'account'], function(){
    Route::group(['middleware'=>'guest'], function(){
        Route::get('/register', [AuthController::class,'register'])->name('account.register');
        Route::post('/process-register', [AuthController::class,'processRegister'])->name('account.processRegister');

        Route::get('/login', [AuthController::class,'login'])->name('account.login');
        Route::post('/login', [AuthController::class,'authenticate'])->name('account.authenticate');
    });

    //password protectd page aikhane hobe
    Route::group(['middleware' => 'auth'], function(){
        Route::get('/profile', [AuthController::class,'profile'])->name('account.profile');
        Route::post('/update-profile', [AuthController::class,'updateProfile'])->name('account.updateProfile');
        Route::post('/update-address', [AuthController::class,'updateAddress'])->name('account.updateAddress');
        Route::get('/change-password', [AuthController::class,'showChangePassword'])->name('account.showChangePassword');
        Route::post('/process-change-password', [AuthController::class,'processChangePassword'])->name('account.processChangePassword');
        //order  
        Route::get('/my-order', [AuthController::class,'order'])->name('account.order');
        Route::get('/order-details/{orderId}', [AuthController::class,'orderDetails'])->name('account.orderdetails');
        //wishlist 
        Route::get('/my-wishlist', [AuthController::class,'wishlist'])->name('account.wishlist'); 
        Route::post('/remove-product-from-wishlist', [AuthController::class,'removeProductFromWishlist'])->name('account.removeProductFromWishlist'); 
        Route::get('/logout', [AuthController::class,'logout'])->name('account.logout');
    });
});

// Admin pannel middle ware
Route::group(['prefix' => 'admin'], function(){

    Route::group(['middleware'=>'admin.guest'], function(){

        Route::get('/login',[AdminLoginController::class,'index'])->name('admin.login');
        Route::post('/authenticate',[AdminLoginController::class,'authenticate'])->name('admin.authenticate');

    });

    Route::group(['middleware' => 'admin.auth'], function(){
        Route::get('/dashboard', [HomeController::class,'index'])->name('admin.dashboard');
        Route::get('/logout', [HomeController::class,'logout'])->name('admin.logout');
    
    /* category route strat from here */    
        //Category Routes
        Route::get('/categories', [CategoryController::class,'index'])->name('categories.index');
        // Route::get('/categories', [CategoryController::class,'search'])->name('categories.search');
        Route::get('/categories/create', [CategoryController::class,'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class,'store'])->name('categories.store');
        //categories.edit
        Route::get('/categories/{category}/edit', [CategoryController::class,'edit'])->name('categories.edit');
        Route::post('/categories/{category}', [CategoryController::class,'update'])->name('categories.update');
        //categories.delete
        Route::delete('/categories/{category}', [CategoryController::class,'destroy'])->name('categories.destroy');


        //temp-images.create
        Route::post('/upload-temp-image', [TempImagesController::class,'create'])->name('temp-images.create');
    
        Route::get('/getSlug', function(Request $request){
            $slug = '';
            if(!empty($request->title)){
                $slug = Str::slug($request->title);
            }
            return response()->json([
                'status'=>true,
                'slug'=>$slug
            ]);
        })->name('getSlug');


        
/* subcategory route strat from here */
        //Subcategory Route
        Route::get('/sub-categories', [SubCategoryController::class,'index'])->name('sub-categories.index');
        Route::get('/sub-categories/create', [SubCategoryController::class,'create'])->name('sub-categories.create');
        Route::post('/sub-categories', [SubCategoryController::class,'store'])->name('sub-categories.store');

        //sub-categories.update
        Route::get('/sub-categories/{subCategory}/edit', [SubCategoryController::class,'edit'])->name('sub-categories.edit');
        Route::post('/sub-categories/{subCategory}', [SubCategoryController::class,'update'])->name('sub-categories.update');
        
        //sub-categories.delete
        Route::delete('/sub-categories/{subCategory}', [SubCategoryController::class,'destroy'])->name('sub-categories.destroy');

/* brand route strat from here */

        //Brands Routes
        Route::get('/brands', [BrandController::class,'index'])->name('brands.index');
        Route::get('/brands/create', [BrandController::class,'create'])->name('brands.create');
        Route::post('/brands', [BrandController::class,'store'])->name('brands.store');

        //Brands.update
        Route::get('/brands/{brand}/edit', [BrandController::class,'edit'])->name('brands.edit');
        Route::put('/brands/{brand}', [BrandController::class,'update'])->name('brands.update');

        //Brands.delete
        Route::delete('/brands/{brand}', [BrandController::class,'destroy'])->name('brands.destroy');


/* product route strat from here */

        //Products Route
        Route::get('/products', [ProductController::class,'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class,'create'])->name('products.create');
        Route::post('/products', [ProductController::class,'store'])->name('products.store');
        Route::delete('/products/{product}', [ProductController::class,'destroy'])->name('products.destroy');
        Route::get('/get-products', [ProductController::class,'getProducts'])->name('products.getProducts');


        //Brands.update
        Route::get('/products/{products}/edit', [ProductController::class,'edit'])->name('products.edit');
        Route::put('/products/{products}', [ProductController::class,'update'])->name('products.update');

        
        // subcategory auto show wnen subcategory selected
        Route::get('/product-subcategories', [PostSubCategoryController::class,'index'])->name('product-subcategories.index');
        Route::post('/product-images/update', [ProductImageController::class,'update'])->name('product-images.update');
        Route::delete('/product-images', [ProductImageController::class,'destroy'])->name('product-images.destroy');


/* Shipping route strat from here */

        Route::get('/shipping/create', [ShippingController::class,'create'])->name('shipping.create');
        Route::post('/shipping', [ShippingController::class,'store'])->name('shipping.store');

        //shipping.update
        Route::get('/shipping/{id}', [ShippingController::class,'edit'])->name('shipping.edit');
        Route::put('/shipping/{id}', [ShippingController::class,'update'])->name('shipping.update');

        //shipping.delete
        Route::delete('/shipping/{id}', [ShippingController::class,'destroy'])->name('shipping.destroy');

/* Discount Coupon Code route strat from here */

        Route::get('/coupons', [DiscountCode::class,'index'])->name('coupons.index');
        Route::get('/coupons/create', [DiscountCode::class,'create'])->name('coupons.create');
        Route::post('/coupons', [DiscountCode::class,'store'])->name('coupons.store');
        Route::get('/coupons/{coupon}/edit', [DiscountCode::class,'edit'])->name('coupons.edit');
        Route::put('/coupons/{coupon}', [DiscountCode::class,'update'])->name('coupons.update');
        Route::delete('/coupons/{coupon}', [DiscountCode::class,'destroy'])->name('coupons.destroy');
 /* order  route strat from here */       
        Route::get('/orders', [OrderController::class,'index'])->name('orders.index');
        Route::get('/orders/{id}', [OrderController::class,'details'])->name('orders.details');
        Route::post('/order/change-status{id}', [OrderController::class,'changeOrderStatus'])->name('orders.changeOrderStatus');
// send invoice email

        Route::post('/order/send-email{id}', [OrderController::class,'sendInvoiceEmail'])->name('orders.sendInvoiceEmail');

// user route 

        Route::get('/users', [UserController::class,'index'])->name('users.index');
        Route::get('/users/create', [UserController::class,'create'])->name('users.create');
        Route::post('/users', [UserController::class,'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class,'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class,'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class,'destroy'])->name('users.destroy');

 // Page route 

        Route::get('/pages', [PageController::class,'index'])->name('pages.index');
        Route::get('/pages/create', [PageController::class,'create'])->name('pages.create');
        Route::post('/pages', [PageController::class,'store'])->name('pages.store');
        Route::get('/pages/{pages}/edit', [PageController::class,'edit'])->name('pages.edit');
        Route::put('/pages/{page}', [PageController::class,'update'])->name('pages.update');
        Route::delete('/pages/{page}', [PageController::class,'destroy'])->name('pages.destroy');


    });


});