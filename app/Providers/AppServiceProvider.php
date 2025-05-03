<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection; //追記
use Illuminate\Pagination\LengthAwarePaginator; //追記
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        View::composer('*', function ($view) {
            // カテゴリデータの生成
            $categories = DB::table('categories')->get();
            $physical_categories = DB::table('physical_categories')->get();

            // カテゴリデータのHTMLオプション生成
            $cate_data = "";
            foreach($categories as $val){
                $cate_data .= "<option value='". $val->cateid;
                $cate_data .= "'>". $val->catename. "</option>";
            }

            // 運動量カテゴリデータのHTMLオプション生成
            $physical_cate_data = "";
            foreach($physical_categories as $val){
                $physical_cate_data .= "<option value='". $val->physical_cateid;
                $physical_cate_data .= "'>". $val->physical_catename. "</option>";
            }

            $view->with([
                'categories' => $categories,
                'physical_categories' => $physical_categories,
                'cate_data' => $cate_data,
                'physical_cate_data' => $physical_cate_data,
            ]);
        });
        // /**
        //  * Paginate a standard Laravel Collection.
        //  *
        //  * @param int $perPage
        //  * @param int $total
        //  * @param int $page
        //  * @param string $pageName
        //  * @return array
        //  */
        // Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
        //     $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

        //     return new LengthAwarePaginator(
        //         $this->forPage($page, $perPage)->values(),
        //         $total ?: $this->count(),
        //         $perPage,
        //         $page,
        //         [
        //             'path' => LengthAwarePaginator::resolveCurrentPath(),
        //             'pageName' => $pageName,
        //         ]
        //     );
        // });
    }
}
