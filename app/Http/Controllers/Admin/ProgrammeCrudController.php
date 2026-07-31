<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProgrammeLevel;
use App\Http\Requests\ProgrammeRequest;
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
 * Class ProgrammeCrudController
 *
 * @property-read CrudPanel $crud
 */
class ProgrammeCrudController extends CrudController
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
        CRUD::setModel(Programme::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/programme');
        CRUD::setEntityNameStrings('programme', 'programmes');
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
        CRUD::column('level')->type('closure')->function(fn ($entry) => $entry->level->label());
        CRUD::column('code');
        CRUD::column('min_credit_units')->label('Min CU');
        CRUD::column('max_credit_units')->label('Max CU');
    }

    /**
     * Define what happens when the Show operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-show
     *
     * @return void
     */
    protected function setupShowOperation()
    {
        $this->setupListOperation();
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
        CRUD::setValidation(ProgrammeRequest::class);

        CRUD::field('name')->attributes(['placeholder' => 'e.g. Computer Science']);
        CRUD::field('level')
            ->type('select_from_array')
            ->options($this->levelOptions())
            ->allows_null(false);
        CRUD::field('code');
        CRUD::field('min_credit_units')->type('number')->label('Min credit units');
        CRUD::field('max_credit_units')->type('number')->label('Max credit units');
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
     * @return array<string, string>
     */
    private function levelOptions(): array
    {
        return collect(ProgrammeLevel::cases())
            ->mapWithKeys(fn (ProgrammeLevel $level) => [$level->value => $level->label()])
            ->all();
    }
}
