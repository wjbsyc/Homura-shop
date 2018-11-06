<?php

namespace App\Providers;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\ProductSku;
use Monolog\Logger;
use Yansongda\Pay\Pay;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
        //要被验证的属性名称 $attribute，属性的值 $value，传入验证规则的参数数组 $parameters，及 Validator 实例。
       Validator::extend('on_sale', function ($attribute, $value) {
            //return $value == 'foo';
            $sku = ProductSku::find($value);
            $onsale = $sku->product->on_sale;
            return $onsale;
        });
        Validator::extend('n_sold_out', function ($attribute, $value) {
            //return $value == 'foo';
            $sku = ProductSku::find($value);
            $stock = $sku->stock;
            return $stock;
        });
        Validator::extend('enough', function ($attribute, $value, $parameters) {
            //return $value == 'foo';
            $sku = ProductSku::find($value);
            return $sku->stock >= $parameters[0] ;
            
        });
        Validator::extend('isenough', function ($attribute, $value) use ($request) {
            preg_match('/items\.(\d+)\.sku_id/', $attribute, $kid);
                    $index  = $kid[1];

                    // 根据索引找到用户所提交的购买数量
                    $amount = $request->input('items')[$index]['amount'];
                    $sku = ProductSku::find($value);
                    return $sku->stock >= $amount ;
            
        });
         Validator::extend('range', function ($attribute, $value) use ($request) {
          $coupons = $request->coupons;
          foreach ($coupons as $coupon) {
             if($coupon['type']=='percent'&&$coupon['value']>100)
             {
                return false;
             }
          }
          return true;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('alipay', function () {
            $config = config('pay.alipay');
            $config['notify_url'] = 'http://xxxxxxxx/shop/Homura-shop/Homura-shop/public/payment/alipay/notify';
            $config['return_url'] = route('payment.alipay.return');
            // 判断当前项目运行环境是否为线上环境
            if (app()->environment() !== 'production') {
                $config['mode']         = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            // 调用 Yansongda\Pay 来创建一个支付宝支付对象
            return Pay::alipay($config);
        });

        $this->app->singleton('wechat_pay', function () {
            $config = config('pay.wechat');
            if (app()->environment() !== 'production') {
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            // 调用 Yansongda\Pay 来创建一个微信支付对象
            return Pay::wechat($config);
        });
    }
}
