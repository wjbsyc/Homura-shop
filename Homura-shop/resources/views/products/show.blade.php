@extends('layouts.app')
@section('title', 'Product')

@section('content')
<div class="row">
<div class="col-lg-10 col-lg-offset-1">
<div class="panel panel-default">
  <div class="panel-body product-info">
    <div class="row">
      <div class="col-sm-4">
        <img class="cover" src="{{ $product->image_url }}" alt="">
      </div>
      <div class="col-sm-7">
        <div class="title">{{ $product->title }}</div>
        <div class="price"><label>价格</label><em>￥</em><span>{{ $product->price }}</span></div>
        <div class="sales_and_reviews">
          <div class="sold_count">累计销量 <span class="count">{{ $product->sold_count }}</span></div>
          <div class="review_count">累计评价 <span class="count">{{ $product->review_count }}</span></div>
          <div class="rating" title="评分 {{ $product->rating }}">评分 <span class="count">{{$product->rating}}</span></div>
        </div>
        <div class="skus">
          <label>选择</label>
          <div class="btn-group" data-toggle="buttons">
 
            @foreach($product->skus as $sku)
              <label
                  class="btn btn-default sku-btn"
                  data-price="{{ $sku->price }}"
                  data-stock="{{ $sku->stock }}"
                  data-toggle="tooltip"
                  title="{{ $sku->description }}"
                  data-placement="bottom">
                <input type="radio" name="skus" autocomplete="off" value="{{ $sku->id }}"> {{ $sku->title }}
              </label>
            @endforeach
     
          </div>
        </div>
        <div class="cart_amount"><label>数量</label><input type="text" class="form-control input-sm" value="1" ><span>件</span><span class="stock"></span></div>
        <div class="buttons">
          @if(isset($favorite) && $favorite)
          <form action="{{ url('products/'.($product->id).'/favorite') }}" method="post" style="display: inline-block">
          {{ csrf_field() }}
          {{ method_field('DELETE') }}
         <button class="btn btn-danger btn-disfavor" type="submit">取消收藏</button>
       </form>
            <!-- <a href="{{ url('products/'.($product->id).'/favorite') }}" class="btn btn-danger btn-disfavor">取消收藏</a> -->
          <!--  <button class="btn btn-danger btn-disfavor">取消收藏</button> -->
          @else
            <a href="{{ url('products/'.($product->id).'/favorite') }}" class="btn btn-success btn-favor">收藏</a>
        <!--    <button class="btn btn-success btn-favor">收藏</button> -->
          @endif
          <button class="btn btn-primary btn-add-to-cart">加入购物车</button>
        </div>
      </div>
    </div>
    <div class="product-detail">
      <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#product-detail-tab" aria-controls="product-detail-tab" role="tab" data-toggle="tab">商品详情</a></li>
        <li role="presentation"><a href="#product-reviews-tab" aria-controls="product-reviews-tab" role="tab" data-toggle="tab">用户评价</a></li>
        <li role="presentation"><a href="#product-coupons-tab" aria-controls="product-coupons-tab" role="tab" data-toggle="tab">优惠信息</a></li>
      </ul>
      <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade in active" id="product-detail-tab">
         
          {!! $product->description !!}

        </div>
        <div role="tabpanel" class="tab-pane fade" id="product-reviews-tab">

        </div>
        <div role="tabpanel" class="tab-pane fade" id="product-coupons-tab">
           @foreach($coupons as $coupon)
          <p>{{$coupon->getDescriptionAttribute()}}</p>
          @endforeach
          @if($coupons->count())
          <p>以上优惠均不叠加</p>
          @endif
        </div>
      </div>
  </div>
  </div>
</div>
</div>
</div>
@endsection

@section('script')

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script>
  $(function(){
  $('a[data-toggle="tab"]').on('shown.bs.tab');
});
  $(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip({trigger: 'hover'});
    $('.sku-btn').click(function () {
      $('.product-info .price span').text($(this).data('price'));
      $('.product-info .stock').text('库存：' + $(this).data('stock') + '件');
    });

    // 加入购物车按钮点击事件
    $('.btn-add-to-cart').click(function () {

      // 请求加入购物车接口
      axios.post('{{ route('cart.add') }}', {
        sku_id: $('label.active input[name=skus]').val(),
        amount: $('.cart_amount input').val(),
      })
        .then(function () { 
        // 请求成功执行此回调
          swal('加入购物车成功', '', 'success');
        }, function (error) { // 请求失败执行此回调
          if (error.response.status === 401) {
console.log(error.response.data);
            // http 状态码为 401 代表用户未登陆
            swal('请先登录', '', 'error');

          } else if (error.response.status === 422) {
            console.log(error.response.data);
            // http 状态码为 422 代表用户输入校验失败
            var html = '<div>';
            _.each(error.response.data, function (errors) {          
                html += errors+'<br>';  
            });
            html += '</div>';
            console.log(html);
            swal({content: $(html)[0], icon: 'error'})
          } else {
            swal('系统错误', '', 'error');
          }
        })
    });

  });
</script>


@endsection