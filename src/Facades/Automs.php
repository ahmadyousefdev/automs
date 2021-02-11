<?php

namespace Ahmadyousefdev\Automs\Facades;

use Illuminate\Support\Facades\Facade;

class Automs extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'automs';
    }
}
