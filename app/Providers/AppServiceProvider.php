<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection; //追記
use Illuminate\Pagination\LengthAwarePaginator; //追記
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
