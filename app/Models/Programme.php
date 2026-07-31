<?php

namespace App\Models;

use App\Enums\ProgrammeLevel;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Database\Factories\ProgrammeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Programme extends Model
{
    use CrudTrait;

    /** @use HasFactory<ProgrammeFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'level',
        'min_credit_units',
        'max_credit_units',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => ProgrammeLevel::class,
        ];
    }

    /**
     * @return HasMany<CourseUnit, $this>
     */
    public function courseUnits(): HasMany
    {
        return $this->hasMany(CourseUnit::class);
    }
}
