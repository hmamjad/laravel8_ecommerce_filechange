<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cart;
use App\Models\Product;
//  use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\DB;
use Auth;

class CartController extends Controller
{
    // addTocart
    public function addTocartQV(Request $request)
    {
        // 3 way to retrive data from database
        // $product = DB::table('products')->where('id',$request->id)->first();
        // $product = Product::where('id',$request->id)->first();
        $product = Product::find($request->id);

        Cart::add([
            'id' => $product->id,                 //$request->id
            'name' => $product->name,
            'qty' => $request->qty,
            'price' => $request->price,
            'weight' => '1',
            'options' => [
                'size' => $request->size,
                'color' => $request->color,
                'thumbnail' => $product->thumbnail,
            ],
        ]);


        return response()->json('product added on cart!');
    }



    // My Cart
    public function mycart()
    {
        $content = Cart::content();
        // dd($content);
        return view('frontend.cart.cart', compact('content'));
    }

    // Remove Cart Product

    public function RemoveProduct($rowId)
    {
        Cart::remove($rowId);

        return response()->json('Success');
    }

    // Update Cart qty
    public function UpdateQty($rowId, $qty)
    {
        Cart::update($rowId, ['qty' => $qty]);
        // Cart::update($rowId, $qty);
        return response()->json('Successfully Updated');
    }

    // Update Cart color
    public function UpdateColor($rowId, $color)
    {
        $product = Cart::get($rowId);
        $thumbnail = $product->options->thumbnail;
        $size = $product->options->size;

        Cart::update($rowId, ['options'  => ['color' => $color, 'thumbnail' => $thumbnail, 'size' => $size]]);

        return response()->json('Successfully Updated');
    }


    // Update Cart color
    public function UpdateSize($rowId, $size)
    {
        $product = Cart::get($rowId);
        $thumbnail = $product->options->thumbnail;
        $color = $product->options->color;

        Cart::update($rowId, ['options'  => ['color' => $color, 'thumbnail' => $thumbnail, 'size' => $size]]);

        return response()->json('Successfully Updated');
    }

    // Cart Empty
    public function EmptyCart()
    {
        Cart::destroy();
        $notification = array('messege' => 'Cart Item Clear !', 'alert-type' => 'success');
        return redirect()->to('/')->with($notification);
    }


    // Wishlist Add
    public function AddWishlist($id)
    {

        if(Auth::check()){
            $check=DB::table('wishlists')->where('product_id',$id)->where('user_id',Auth::id())->first();
               if ($check) {
                     $notification=array('messege' => 'Already have it on your wishlist !', 'alert-type' => 'error');
                     return redirect()->back()->with($notification); 
               }else{
                    $data=array();
                    $data['product_id']=$id;
                    $data['user_id']=Auth::id();
                    $data['date']=date('d , F Y');
                    DB::table('wishlists')->insert($data);
                    $notification=array('messege' => 'Product added on wishlist!', 'alert-type' => 'success');
                    return redirect()->back()->with($notification); 
               }
        }

        $notification=array('messege' => 'Login Your Account!', 'alert-type' => 'error');
        return redirect()->back()->with($notification);  
    }




    // wishlist show
    public function wishlist()
    {

        if (Auth::check()) {
            $wishlist = DB::table('wishlists')->leftJoin('products', 'wishlists.product_id', 'products.id')->select('products.name', 'products.thumbnail', 'products.slug', 'wishlists.*')->where('wishlists.user_id', Auth::id())->get();
            return view('frontend.cart.wishlist', compact('wishlist'));
        }

        $notifications = array('messege' => 'At first Login your Acount', 'alert-type' => 'success');
        return redirect()->back()->with($notifications);
    }

    // Clearwishlist
    public function Clearwishlist()
    {
        DB::table('wishlists')->where('user_id', Auth::id())->delete();
        $notifications = array('messege' => 'Wishlist Cleared', 'alert-type' => 'success');
        return redirect()->back()->with($notifications);
    }
    // WishlistProductdelete
    public function WishlistProductdelete($id)
    {
        DB::table('wishlists')->where('id', $id)->delete();
        $notifications = array('messege' => 'Successfully Deleted', 'alert-type' => 'success');
        return redirect()->back()->with($notifications);
    }
}
