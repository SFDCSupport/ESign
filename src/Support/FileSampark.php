<?php
/*
 * @author : Anand Pilania
 * @mailto : Anand.Pilania@niit.com
 * @updated : 1/10/24, 4:52 PM
 */

namespace NIIT\ESign\Support;

use Illuminate\Support\Collection;

class FileSampark
{
    protected $attachemtableModel;

    public function setup(array|Collection $config)
    {
        if (gettype($config) !== Collection::class) {
            $config = collect($config);
        }
    }

    public function upload()
    {
    }

    public function load()
    {
    }

    public function delete()
    {
    }

    public function routes(?string $prefix)
    {
    }
}
