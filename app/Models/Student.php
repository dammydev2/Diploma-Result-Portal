<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
