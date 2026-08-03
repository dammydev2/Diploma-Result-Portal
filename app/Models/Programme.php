<?php

namespace App\Models;

use App\Enums\ProgrammeLevel;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Database\Factories\ProgrammeFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /**
     * @return HasMany<Student, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * The programme's name with its level, e.g. "Computer Science (ND)" —
     * used anywhere a programme is picked from a list, since the name alone
     * is ambiguous once the same programme exists at both ND and HND level.
     */
    protected function nameWithLevel(): Attribute
    {
        return Attribute::get(fn (): string => "{$this->name} ({$this->level->value})");
    }
}
