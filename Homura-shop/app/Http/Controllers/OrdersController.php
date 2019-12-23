<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserAddress;
use Auth;
use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InvalidRequestException;
use Validator;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\OrderItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Models\CouponCode;
use Illuminate\Support\Facades\Log;
use App\Jobs\CloseOrder;

class OrdersController extends Controller
{
   public function cancelorder(Order $order,Request $request)
   {
        if(Auth::check()&&(Auth::id()==$order->user->id))
         {
            
            if ($order->paid_at) {
               //
            }
            else{
            //\DB::transaction(function() {
                // 将订单的 closed 字段标记为 true，即关闭订单
                $order->closed = true;
                $order->save();
                // 循环遍历订单中的商品 SKU，将订单中的数量加回到 SKU 的库存中去
                foreach ($order->items as $item) {
                    $item->productSku->addStock($item->amount);
                }
            //    });
            }
            return redirect()->route('orders.show',['order'=>$order->id]);
         }
   }
   public function store(Request $request)
    {
        	$user  = Auth::user();
        
            $data = $request->all();
             $messages=['items.*.sku_id.on_sale'=>'Not On Sale',
        			'items.*.sku_id.n_sold_out'=>'Sold out',
        			'items.*.sku_id.isenough'=>'stock not enough',
    			];
            $v=Validator::make($data,[
            'address_id' =>['required',Rule::exists('user_addresses', 'id')->where('user_id', $user->id)],
            'items' => 'required|array',
            'items.*.sku_id' => ['on_sale','n_sold_out',Rule::exists('product_skus','id'),'isenough'],
           							
            ],$messages);
            if ($v->fails()) {
            return response($v->errors()->all(),422);
     		}

            $address = UserAddress::find($request->input('address_id'));
            // 更新此地址的最后使用时间
            $address->update(['last_used_at' => Carbon::now()]);
            // 创建一个订单

            $order   = new Order([
                'address'      => [ // 将地址信息放入订单中
                    'address'       => $address->full_address,
                    'zip'           => $address->zip,
                    'contact_name'  => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark'       => $request->input('remark'),
                'total_amount' => 0,
            ]);
            // 订单关联到当前用户
            $order->user()->associate($user);
            // 写入数据库
            $order->save();

            $totalAmount = 0;
            $items       = $request->input('items');
            $list = collect([]);
            // 遍历用户提交的 SKU
            DB::beginTransaction();
            foreach ($items as $data) {
            	
                $sku  = ProductSku::find($data['sku_id']);
                $product = $sku->product();
                
                // 创建一个 OrderItem 并直接与当前订单关联
                $item = new OrderItem;
                $item->amount = $data['amount'];
                $item->price  = $sku->price;
                $item->order()->associate($order);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save(); 
                //$list->push(['id'=>$sku->product->id,'price' => $sku->price * $data['amount']]);
              	 if ($sku->decreaseStock($data['amount']) <= 0) {
      					throw new InvalidRequestException(($product->title).'库存不足');
   				 }

                $coupons = $sku->product->coupons()->where('enabled',true)->where('min_amount','<',$sku->price * $data['amount'])->get();
                $discount = 0 ;
                foreach ($coupons as $c) {
                	if($c->type=='percent') 
                		{
                			$tmp = $sku->price * $data['amount'] * ($c->value/100.00);
                		}
                	else 
                		{
                			$tmp=$c->value;
                		}
                	if ($tmp>$discount)
                		{ 
                			$discount = $tmp;
                		}

                }
                $dis = $discount > 0 ? $discount:0;
                $totalAmount += ($sku->price * $data['amount']-$dis);
            }
			DB::commit();
            // 更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            // 将下单的商品从购物车中移除
            $skuIds = collect($request->input('items'))->pluck('sku_id');
            $user->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
            $now =  Carbon::now();
            $startjob =(new CloseOrder($order))->delay($now->addMinutes(30));
            dispatch($startjob);
            //$this->dispatch(new CloseOrder($order,30));
        return $order;
    }
     public function index(Request $request)
    {	
    	if(Auth::check())
       	{
       	 $orders = Order::with(['items.product', 'items.productSku'])->where('user_id',Auth::id())->orderBy('created_at', 'desc')->paginate(16);

        return view('orders.index', ['orders' => $orders]);
    	}
    
    	else
    	{
    		return redirect()->route('login');
    	}
    }
    public function findbyid(Request $request)
    {
        if(Auth::check())
        {
            $data = $request->all();
            $orderid = $data['search'];
             $orders = Order::with(['items.product', 'items.productSku'])->where('user_id',Auth::id())->where('no', 'like', "%".$orderid."%")->orderBy('created_at', 'desc')->paginate(16);
              return view('orders.index', ['orders' => $orders]);
        }
    }
    public function show(Order $order, Request $request)
    {	
    	if(Auth::check()&&Auth::id()==$order->user->id)
    		{
    			 return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    		}
       else
       {
       	return redirect()->route('login');
       }
    }
    public function received(Order $order, Request $request)
    {
        
       if(Auth::check()&&Auth::id()==$order->user->id)
       	{
        // 判断订单的发货状态是否为已发货
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRequestException('发货状态不正确');
        }

        // 更新发货状态为已收到
        $order->update(['ship_status' => Order::SHIP_STATUS_RECEIVED]);

        // 返回原页面
        return redirect()->back();
    	}
    	else
    	{
    		 return redirect()->route('login');
    	}
    }
}
