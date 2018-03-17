<?php

namespace JRC9DS\Analytics;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JRC9DS\Analytics\Analytics
 */
class AnalyticsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-analytics';
    }
}
