<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Database\Factories\ScoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class Score extends Model
{
    use CrudTrait;

    /** @use HasFactory<ScoreFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'course_unit_id',
        'score',
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

    protected static function booted(): void
    {
        // A suspended student cannot have new scores recorded against them.
        static::creating(function (Score $score): void {
            if ($score->student->isSuspended) {
                throw new RuntimeException('Cannot record a score for a suspended student.');
            }
        });

        // The letter grade and grade point are derived from the grading
        // scale at the point a score is entered/changed, not recalculated
        // later, so a result stays accurate even if the scale is edited.
        static::saving(function (Score $score): void {
            if ($score->isDirty('score')) {
                $band = GradingScale::forScore($score->score);

                if (! $band) {
                    throw new RuntimeException("No grading scale band covers a score of {$score->score}.");
                }

                $score->letter_grade = $band->letter_grade;
                $score->weighting = $band->weighting;
            }
        });
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<CourseUnit, $this>
     */
    public function courseUnit(): BelongsTo
    {
        return $this->belongsTo(CourseUnit::class);
    }
}
