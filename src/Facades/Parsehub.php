<?php

namespace Shahrukh\Parsehub\Facades;

use Illuminate\Support\Facades\Facade;

class Parsehub extends Facade
{
    /**
     * Get the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Shahrukh\Parsehub\Parsehub';
    }
}
