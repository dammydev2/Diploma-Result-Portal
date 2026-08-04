<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Closure;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use CrudTrait;

    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'programme_id',
        'name',
        'matric_no',
        'year_of_admission',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'suspended_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Programme, $this>
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function isSuspended(): Attribute
    {
        return Attribute::get(fn (): bool => ! is_null($this->suspended_at));
    }

    public function suspend(): void
    {
        $this->forceFill(['suspended_at' => now()])->save();
    }

    public function reactivate(): void
    {
        $this->forceFill(['suspended_at' => null])->save();
    }

    /**
     * @return HasMany<Score, $this>
     */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    /**
     * Grade point average for a specific year and semester of study.
     */
    public function gpaFor(int $year, int $semester): ?float
    {
        return $this->calculateGpa(
            fn ($query) => $query->where('course_units.year', $year)->where('course_units.semester', $semester)
        );
    }

    /**
     * Cumulative grade point average across every recorded score.
     */
    public function cgpa(): ?float
    {
        return $this->calculateGpa(fn ($query) => $query);
    }

    private function calculateGpa(Closure $constraints): ?float
    {
        $query = $this->scores()->join('course_units', 'course_units.id', '=', 'scores.course_unit_id');

        $totals = $constraints($query)
            ->selectRaw('SUM(scores.weighting * course_units.credit_units) as weighted_points, SUM(course_units.credit_units) as total_credit_units')
            ->first();

        $totalCreditUnits = (int) ($totals->total_credit_units ?? 0);

        if ($totalCreditUnits === 0) {
            return null;
        }

        return round((float) $totals->weighted_points / $totalCreditUnits, 2);
    }
}
