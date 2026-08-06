<?php

namespace App\Enums;

enum Permission: string
{
    case ManageProgrammes = 'manage_programmes';
    case ManageStudents = 'manage_students';
    case SuspendReactivateStudents = 'suspend_reactivate_students';
    case AddCourseUnits = 'add_course_units';
    case EditCourseUnits = 'edit_course_units';
    case EditGradingScale = 'edit_grading_scale';
    case EditDiplomaClassification = 'edit_diploma_classification';
    case ScoreEntry = 'score_entry';
    case EditScore = 'edit_score';
    case SemesterResult = 'semester_result';

    public function label(): string
    {
        return match ($this) {
            self::ManageProgrammes => 'Programmes',
            self::ManageStudents => 'Students',
            self::SuspendReactivateStudents => 'Suspend and reactivate a student',
            self::AddCourseUnits => 'Add course unit',
            self::EditCourseUnits => 'Edit course unit',
            self::EditGradingScale => 'Edit grading scale',
            self::EditDiplomaClassification => 'Edit diploma classification',
            self::ScoreEntry => 'Score entry',
            self::EditScore => 'Edit score',
            self::SemesterResult => 'Semester result',
        };
    }
}
