<?php

namespace NIIT\ESign\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    /** @var string */
    protected $table = 'e_templates';

    /**
     * @var array<int,string>
     */
    protected $fillable = [
        'id',
        'label',
        'body',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [];

    /**
     * @var array<int, string>
     */
    protected $appends = [
        'documents',
    ];

    /**
     * @return HasMany<Document>
     */
    public function documents()
    {
        return $this->hasMany(
            related: Document::class,
            foreignKey: 'template_id',
        );
    }
}
