<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CourseUnitRequest;
use App\Models\CourseUnit;
use App\Models\Programme;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CourseUnitCrudController
 *
 * @property-read CrudPanel $crud
 */
class CourseUnitCrudController extends CrudController
{
    use CreateOperation;
    use DeleteOperation;
    use ListOperation;
    use ShowOperation;
    use UpdateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(CourseUnit::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/course-unit');
        CRUD::setEntityNameStrings('course unit', 'course units');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     *
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('code');
        CRUD::column('title');
        CRUD::column('programme')->type('select')->attribute('name');
        CRUD::column('year');
        CRUD::column('semester');
        CRUD::column('credit_units')->label('CU');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     *
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(CourseUnitRequest::class);

        CRUD::field('programme_id')
            ->type('select')
            ->entity('programme')
            ->attribute('name')
            ->model(Programme::class)
            ->label('Programme');

        CRUD::field('code');
        CRUD::field('title');

        CRUD::field('year')->type('select_from_array')->options([1 => 'Year 1', 2 => 'Year 2']);
        CRUD::field('semester')->type('select_from_array')->options([1 => 'Semester 1', 2 => 'Semester 2']);

        CRUD::field('lecture_hours')->type('number')->label('Lecture hours (L)');
        CRUD::field('practical_hours')->type('number')->label('Practical hours (P)');
        CRUD::field('credit_units')->type('number')->label('Credit units (CU)');
        CRUD::field('contact_hours')->type('number')->label('Contact hours (CH)');

        CRUD::field('prerequisite_course_unit_id')
            ->type('select')
            ->entity('prerequisite')
            ->attribute('code')
            ->model(CourseUnit::class)
            ->label('Prerequisite')
            ->allows_null(true);
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     *
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
