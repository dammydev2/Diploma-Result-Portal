<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StudentRequest;
use App\Models\Programme;
use App\Models\Student;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Class StudentCrudController
 *
 * Students are never deleted, only suspended/reactivated, so this
 * controller deliberately does not use Backpack's DeleteOperation.
 *
 * @property-read CrudPanel $crud
 */
class StudentCrudController extends CrudController
{
    use CreateOperation {
        create as protected traitCreate;
        store as protected traitStore;
    }
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
        CRUD::setModel(Student::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/student');
        CRUD::setEntityNameStrings('student', 'students');
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
        CRUD::column('name');
        CRUD::column('matric_no')->label('Matric no');
        CRUD::addColumn([
            'name' => 'programme',
            'type' => 'select',
            'attribute' => 'name_with_level',
        ]);
        CRUD::column('year_of_admission')->label('Year of admission');
        CRUD::addColumn([
            'name' => 'status',
            'type' => 'closure',
            'function' => fn (Student $entry) => $entry->isSuspended ? 'Suspended' : 'Active',
        ]);

        CRUD::addButton('line', 'suspend_toggle', 'view', 'admin.student.suspend_button');
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
        CRUD::setValidation(StudentRequest::class);

        CRUD::addField([
            'name' => 'programme_id',
            'type' => 'select',
            'entity' => 'programme',
            'attribute' => 'name_with_level',
            'model' => Programme::class,
            'label' => 'Programme',
        ]);

        CRUD::field('name');
        CRUD::field('matric_no')->label('Matric no');
        CRUD::field('year_of_admission')->type('number')->label('Year of admission');
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
     * Show the form for creating a new student, unless no programme exists yet.
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
     * Store a newly created student, unless no programme exists yet.
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

    public function suspend(int $id): RedirectResponse
    {
        $this->crud->hasAccessOrFail('update');

        Student::findOrFail($id)->suspend();

        \Alert::success('Student suspended.')->flash();

        return redirect()->back();
    }

    public function reactivate(int $id): RedirectResponse
    {
        $this->crud->hasAccessOrFail('update');

        Student::findOrFail($id)->reactivate();

        \Alert::success('Student reactivated.')->flash();

        return redirect()->back();
    }

    private function noProgrammesExist(): bool
    {
        return ! Programme::query()->exists();
    }

    private function redirectForMissingProgramme(): RedirectResponse
    {
        \Alert::error('You need to add a programme before adding a student.')->flash();

        return redirect()->to($this->crud->route);
    }
}
