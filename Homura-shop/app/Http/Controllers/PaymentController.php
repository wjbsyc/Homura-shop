<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Exceptions\InvalidRequestException;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class PaymentController extends Controller
{
    public function payByAlipay(Order $order, Request $request)
    {
        
        if(Auth::check()&&(Auth::id()==$order->user->id))
        {
        		
        	if ($order->paid_at || $order->closed) 
        	{
           		 throw new InvalidRequestException('订单状态不正确');
       		}

        // 调用支付宝的网页支付
      		  return app('alipay')->web([
          			'out_trade_no' => $order->no, 
            		'total_amount' => $order->total_amount, 
            		'subject'      => '支付 Homura Shop 的订单：'.$order->no, 
       				 ]);
    	}
		
		else
		{
			return redirect()->route('login');
		}
	}
	//前端回调
	 public function alipayReturn()
    {
        
        try {
            app('alipay')->verify();
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        return view('pages.success', ['msg' => '付款成功']);
    }

    // 服务器端回调
    public function alipayNotify()
    {
        $data = app('alipay')->verify();
        $order = Order::where('no', $data->out_trade_no)->first();
       
        if (!$order) 
        {
            return 'fail';
        }
        
        if ($order->paid_at) 
        {
          
            return app('alipay')->success();
        }

        $order->update([
            'paid_at'        => Carbon::now(), // 支付时间
            'payment_method' => 'alipay', // 支付方式
            'payment_no'     => $data->trade_no, // 支付宝订单号
        ]);
        $items = $order->items()->get();
      

        foreach ($items as $item) 
        {
        	$prod = $item->product;
        	$amount = $item->amount;
        	$prod->sold_count+=$amount;
        
        	$prod->save();
    	}	
        return app('alipay')->success();
    }
}
