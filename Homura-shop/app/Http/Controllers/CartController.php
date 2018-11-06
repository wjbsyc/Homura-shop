<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\CartItem;           
use App\Models\ProductSku;
use App\Models\Product;                     
class CartController extends Controller
{	

	public function index(Request $request)
    {	
    	if(Auth::check())
    	{
    		$user = Auth::user();
       		$cartItems = $user->cartItems()->with(['productSku.product'])->get();
       		$address = $user->addresses()->get();
        return view('cart.index', ['cartItems' => $cartItems,'addresses'=>$address]);
    	}
    	else
    	{
    		return redirect()->route('login');
    	}
    }
    public function add(Request $request)
    {	if(Auth::check())
    	{
        $user   = Auth::user();
        $skuId  = $request->input('sku_id');
        $amount = $request->input('amount');
        $request->flash();
        $data=$request->all();

        $messages=['sku_id.on_sale'=>'Not On Sale',
        			'sku_id.n_sold_out'=>'Sold out',
        			'sku_id.enough'=>'stock not enough',
    			];
            $v=Validator::make($data,[
            'sku_id' =>'required|exists:product_skus,id|on_sale|n_sold_out|enough:'.$amount,
            'amount' => 'required|integer|min:1',
            ],$messages);
            if ($v->fails()) {
            return response($v->errors()->all(),422);
     		}
     		else
     		{
        		if ($cart = $user->cartItems()->where('product_sku_id', $skuId)->first()) {
          			  $cart->update([
              		  'amount' => $cart->amount + $amount,
           			 ]);
        		} else {
          		  $cart = new CartItem(['amount' => $amount]);
           		 $cart->user()->associate($user);
           		 $cart->productSku()->associate($skuId);
           		 $cart->save();
      			  }

        		return [];
   			}
    	}
    	else
    	{
    		return redirect()->route('login');
    	}
    }
     public function remove(ProductSku $sku, Request $request)
    {
    	if(Auth::check())
    		{	 $request->flash();
    			 $user= Auth::user();
    			 $user->cartItems()->where('product_sku_id', $sku->id)->delete();
    			 return redirect()->back()->withInput();
    		}
    	else
    	{
    		return redirect()->route('login');
    	}	
       
    }
}
