<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use App\Models\CouponCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
class ProductsController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return Admin::content(function(Content $content)
                {
                    $content->header('Product List');
                    $content->body($this->grid());
                }
        ) ;
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('Edit Product');
            $content->body($this->form()->edit($id));
        });
    }
    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('New Product');
            $content->body($this->form());
        });
    }
    // public function edit($id)
    // {
    //     return Admin::content(function (Content $content) use ($id) {
    //         $content->header('Edit Product');
    //         $content->body($this->form()->edit($id));
    //     });
    // }
     /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Product::class,function(Grid $grid)
                {
                    $grid->id('ID')->sortable();
                    $grid->title('Product Name');
                    $grid->on_sale('On Sale')->display(function($value)
                    { return $value ?'Y':'N';}
                    );
                    $grid->price('Price');
                    $grid->rating('Rate');
                    $grid->sold_count('Sales');
                    $grid->review_count('Comments');
                    $grid->actions(function($actions)
                        {
                            $actions->disableView();
                            //$actions->disableDelete();
                        }
                    );
                    $grid->tools(function($tools)
                        {
                            $tools->batch(function($batch)
                                {
                                    //$batch->disableDelete();
                                }
                            );

                        }
                    );                    
                }
            );
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Product::findOrFail($id));

        $show->id('Id');
        $show->title('Title');
        $show->description('Description');
        $show->image('Image');
        $show->on_sale('On sale');
        $show->rating('Rating');
        $show->sold_count('Sold count');
        $show->review_count('Review count');
        $show->price('Price');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
   protected function form()
    {
        // 创建一个表单
        return Admin::form(Product::class, function (Form $form) {

            // 创建一个输入框，第一个参数 title 是模型的字段名，第二个参数是该字段描述
            $form->text('title', 'Title')->rules('required');

            // 创建一个选择图片的框
            $form->image('image', 'image')->rules('required|image');

            // 创建一个富文本编辑器
            $form->editor('description', 'description');

            // 创建一组单选框
            $form->radio('on_sale', 'On Sale')->options(['1' => 'Y', '0'=> 'N'])->default('0');

            // 直接添加一对多的关联模型
            $form->hasMany('skus', 'SKU LIST', function (Form\NestedForm $form) {
                $form->text('title', 'SKU Name')->rules('required');
                $form->text('description', 'SKU desc')->rules('required');
                $form->text('price', 'price')->rules('required|numeric|min:0.01');
                $form->text('stock', 'remain stock')->rules('required|integer|min:0');
            });

             $form->hasMany('coupons','Coupons',function(Form\NestedForm $form) 
             {          

                        $form->text('name', 'Name')->rules('required');
                        $form->radio('type', 'Type')->options(CouponCode::$typeMap)->rules('required');
                        $form->text('value','Value')->rules('range|numeric|min:0');
                        $form->text('min_amount','Min')->rules('required|numeric|min:0');
                        $form->radio('enabled', 'Enabled')->options(['1' => 'Y', '0'=> 'N'])->default('0');
             });
            
            $form->saving(function (Form $form) {
                
                $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price') ?: 0;
            });
        });
    }


}
