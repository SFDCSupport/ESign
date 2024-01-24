<?php

namespace NIIT\ESign\Models;

use App\Traits\Userstamps;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    use Userstamps;

    /** @var string */
    protected $table = 'e_templates';

    /**
     * @var array<int,string>
     */
    protected $fillable = [
        'id',
        'label',
        'body',
        'created_at', 'updated_at', 'deleted_at',
        'created_by', 'updated_by', 'deleted_by',
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
