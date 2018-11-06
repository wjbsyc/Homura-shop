@extends('layouts.app')
@section('title', 'Products List')

@section('content')
<div class="row">
<div class="col-lg-10 col-lg-offset-1">
<div class="panel panel-default">
  <div class="panel-body">
    <div class="row">
      <form action="{{ route('products.index') }}" class="form-inline search-form">
        <input type="text" class="form-control input-sm" name="search" placeholder="搜索" value ="{{$filters['search'] ?? ''}}">
        <button class="btn btn-primary btn-sm">搜索</button>
        <select name="order" class="form-control input-sm pull-right">
          <option value="">排序方式</option>
          <option value="price_asc" {{ $filters['order'] =='price_asc'?"selected='1'":''}}>价格从低到高</option>
          <option value="price_desc" {{ $filters['order'] =='price_desc'?"selected='1'":''}}>价格从高到低</option>
          <option value="sold_count_desc" {{ $filters['order'] =='sold_count_desc'?"selected='1'":''}}>销量从高到低</option>
          <option value="sold_count_asc" {{ $filters['order'] =='sold_count_asc'?"selected='1'":''}}>销量从低到高</option>
          <option value="rating_desc" {{ $filters['order'] =='rating_desc'?"selected='1'":''}}>评价从高到低</option>
          <option value="rating_asc" {{ $filters['order'] =='rating_asc'?"selected='1'":''}}>评价从低到高</option>
        </select>
      </form>
    </div>
    <div class="row products-list">

      @foreach($products as $product)
      <div class="col-xs-3 product-item">
        <div class="product-content">
          <div class="top">
            <div class='imgbox'>            
            <div class="img">
               <a href="{{ route('products.show', ['product' => $product->id]) }}">
              <img src="{{ $product->image_url }}" alt="">
              </a>
            </div>
          </div>
            <div class="price"><b>￥</b>{{ $product->price }}</div>
            <div class="title">
              <a href="{{ route('products.show', ['product' => $product->id]) }}">{{ $product->title }}</a>
            </div>
          </div>
          <div class="bottom">
            <div class="sold_count">Sales <span>{{ $product->sold_count }}</span></div>
            <div class="review_count">Rate <span>{{ $product->rating }}</span></div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    <div class="pull-right">{{ $products->render() }}</div>
  </div>
</div>
</div>
</div>
@endsection


@section('script')
<script type="text/javascript">
  $(document).ready(function () {
      $('.search-form select[name=order]').on('change', function() {
        $('.search-form').submit();
      });
    })
</script>

@endsection