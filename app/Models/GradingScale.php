<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Database\Factories\GradingScaleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingScale extends Model
{
    use CrudTrait;

    /** @use HasFactory<GradingScaleFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'min_score',
        'max_score',
        'letter_grade',
        'weighting',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weighting' => 'decimal:2',
        ];
    }

    /**
     * Find the grading scale band a given score falls into.
     */
    public static function forScore(int $score): ?self
    {
        return static::query()
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first();
    }
}
