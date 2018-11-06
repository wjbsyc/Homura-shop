<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProdectSku;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Exceptions\InvalidRequestException;
use Illuminate\Support\Facades\Auth;
use App\Models\CouponCode;
class ProductsController extends Controller
{
    public function index(Request $request)
    {
    	$prod = Product::where('on_sale',true);
		$search = $request->input('search', '');
    	if ($search) {
            $like = '%'.$search.'%';
            // select * from products where on_sale = 1 and ( title like xxx or description like xxx )
            $prod->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }

        $order = $request->input('order', '');
        if ($order) {
            
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
               
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    $prod->orderBy($m[1], $m[2]);
                }
            }
        }
        $products = $prod->paginate(8);

    	return view('products.index',['products'=>$products,
    		'filters'  => [
                'search' => $search,
                'order'  => $order,
            ],
        ]);
    }
    public function show(Product $product, Request $request)
    {
       
        if (!$product->on_sale) {
            throw new InvalidRequestException('Not On Sale!!!');
        }
        $favorite = false;
        if(Auth::check())
       	{
       		$favorite = boolval(Auth::user()->favoriteProducts()->find($product->id));
       	}
        $coupons = $product->coupons()->where('enabled',true)->get();


        return view('products.show', ['product' => $product,'favorite'=>$favorite,'coupons'=>$coupons]);
    }
    public function favor(Product $product, Request $request)
    {	if(Auth::check())
    	{
    	$request->flash();
        $user=Auth::user();
        if (!$user->favoriteProducts()->find($product->id)) 
        {
       		 $user->favoriteProducts()->attach($product);
   		}
        return redirect()->back()->withInput();
    	}
    	else
    	{
    		return redirect()->route('login');
    	}
    }

    public function disfavor(Product $product, Request $request)
    {	
    	if(Auth::check()){
        $user = Auth::user();
        $user->favoriteProducts()->detach($product);

        return redirect()->back()->withInput();
    	}
    	else
    	{
			return redirect()->route('login');
    	}
    }
    public function favorites(Request $request)
    {	
    	if(Auth::check())
    	{
        $products = $request->user()->favoriteProducts()->paginate(16);

        return view('products.favorites', ['products' => $products]);
    	}
    	else
    	{
    		return redirect()->route('login');
    	}
    }
}
