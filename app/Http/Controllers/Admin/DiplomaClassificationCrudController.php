<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DiplomaClassificationRequest;
use App\Models\DiplomaClassification;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class DiplomaClassificationCrudController
 *
 * The diploma classification table is a fixed set of CGPA bands, so this
 * controller deliberately only supports viewing and editing — no create
 * or delete.
 *
 * @property-read CrudPanel $crud
 */
class DiplomaClassificationCrudController extends CrudController
{
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
        CRUD::setModel(DiplomaClassification::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/diploma-classification');
        CRUD::setEntityNameStrings('diploma classification', 'diploma classification');
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
        CRUD::orderBy('min_cgpa', 'desc');

        CRUD::column('classification');
        CRUD::addColumn([
            'name' => 'min_cgpa',
            'type' => 'number',
            'label' => 'Min CGPA',
            'decimals' => 2,
        ]);
        CRUD::addColumn([
            'name' => 'max_cgpa',
            'type' => 'number',
            'label' => 'Max CGPA',
            'decimals' => 2,
        ]);
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
        CRUD::setValidation(DiplomaClassificationRequest::class);

        CRUD::field('classification');
        CRUD::field('min_cgpa')->type('number')->label('Min CGPA')->attributes(['step' => '0.01']);
        CRUD::field('max_cgpa')->type('number')->label('Max CGPA')->attributes(['step' => '0.01']);
    }
}
