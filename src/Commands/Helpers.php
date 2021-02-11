<?php

namespace Ahmadyousefdev\Automs\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class Helpers extends Command
{
    /**
     * insert text after given string
     * gotten from https://stackoverflow.com/a/25372554/5342709
     * @param  mixed $string
     * @param  mixed $keyword
     * @param  mixed $body
     * @return void
     */
    public static function insertTextAfter ($string, $keyword, $body) {
        return substr_replace($string, PHP_EOL . $body, strpos($string, $keyword) + strlen($keyword), 0);
    }
}