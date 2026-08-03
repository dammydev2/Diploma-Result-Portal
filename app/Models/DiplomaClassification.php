<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Database\Factories\DiplomaClassificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiplomaClassification extends Model
{
    use CrudTrait;

    /** @use HasFactory<DiplomaClassificationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'min_cgpa',
        'max_cgpa',
        'classification',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_cgpa' => 'decimal:2',
            'max_cgpa' => 'decimal:2',
        ];
    }

    /**
     * Find the diploma classification band a given CGPA falls into.
     */
    public static function forCgpa(float $cgpa): ?self
    {
        return static::query()
            ->where('min_cgpa', '<=', $cgpa)
            ->where('max_cgpa', '>=', $cgpa)
            ->first();
    }
}
