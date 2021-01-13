<?php
/**
 * Created by PhpStorm.
 * User: LINH
 * Date: 9/24/2020
 * Time: 1:43 PM
 */

namespace Linhnh95\LaravelQuerySupport;

use Linhnh95\LaravelQuerySupport\Helper\QueryHelpers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Query\Builder;

class SupportProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Builder::macro('filterQuery', function ($params = [], $conditions = []) {
            $query = new QueryHelpers();
            $query->setQuery($this);
            $query->createWhere($params, $conditions);
            return $query->getQuery();
        });
    }
}
