<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Database\Factories\CourseUnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseUnit extends Model
{
    use CrudTrait;

    /** @use HasFactory<CourseUnitFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'programme_id',
        'code',
        'title',
        'credit_units',
        'year',
        'semester',
    ];

    /**
     * @return BelongsTo<Programme, $this>
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    /**
     * @return HasMany<Score, $this>
     */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }
}
