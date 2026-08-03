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
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Class CourseUnitCrudController
 *
 * @property-read CrudPanel $crud
 */
class CourseUnitCrudController extends CrudController
{
    use CreateOperation {
        create as protected traitCreate;
        store as protected traitStore;
    }
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
        CRUD::addColumn([
            'name' => 'programme',
            'type' => 'select',
            'attribute' => 'name_with_level',
        ]);
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

        CRUD::addField([
            'name' => 'programme_id',
            'type' => 'select',
            'entity' => 'programme',
            'attribute' => 'name_with_level',
            'model' => Programme::class,
            'label' => 'Programme',
        ]);

        CRUD::field('code');
        CRUD::field('title');

        CRUD::addField(['name' => 'year', 'type' => 'select_from_array', 'options' => [1 => 'Year 1', 2 => 'Year 2']]);
        CRUD::addField(['name' => 'semester', 'type' => 'select_from_array', 'options' => [1 => 'Semester 1', 2 => 'Semester 2']]);

        CRUD::field('credit_units')->type('number')->label('Credit units (CU)');
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

    /**
     * Show the form for creating a new course unit, unless no programme exists yet.
     *
     * @return View|RedirectResponse
     */
    public function create()
    {
        if ($this->noProgrammesExist()) {
            return $this->redirectForMissingProgramme();
        }

        return $this->traitCreate();
    }

    /**
     * Store a newly created course unit, unless no programme exists yet.
     *
     * @return RedirectResponse
     */
    public function store()
    {
        if ($this->noProgrammesExist()) {
            return $this->redirectForMissingProgramme();
        }

        return $this->traitStore();
    }

    private function noProgrammesExist(): bool
    {
        return ! Programme::query()->exists();
    }

    private function redirectForMissingProgramme(): RedirectResponse
    {
        \Alert::error('You need to add a programme before adding a course unit.')->flash();

        return redirect()->to($this->crud->route);
    }
}
