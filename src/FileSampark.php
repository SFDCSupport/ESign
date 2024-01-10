<?php

namespace NIIT\ESign;

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
