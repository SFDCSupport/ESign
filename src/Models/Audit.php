<?php

namespace NIIT\ESign\Models;

class Audit extends Model
{
    protected $table = 'e_audits';

    /**
     * @var array<int,string>
     */
    protected $fillable = [];

    /**
     * @var array<string,string>
     */
    protected $casts = [];
}
