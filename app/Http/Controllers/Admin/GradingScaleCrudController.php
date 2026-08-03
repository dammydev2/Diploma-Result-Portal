<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\GradingScaleRequest;
use App\Models\GradingScale;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class GradingScaleCrudController
 *
 * The grading scale is a fixed set of grade bands, so this controller
 * deliberately only supports viewing and editing — no create or delete.
 *
 * @property-read CrudPanel $crud
 */
class GradingScaleCrudController extends CrudController
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
        CRUD::setModel(GradingScale::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/grading-scale');
        CRUD::setEntityNameStrings('grading scale', 'grading scale');
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
        CRUD::orderBy('min_score', 'desc');

        CRUD::column('letter_grade')->label('Letter grade');
        CRUD::column('min_score')->type('number')->label('Min score');
        CRUD::column('max_score')->type('number')->label('Max score');
        CRUD::addColumn([
            'name' => 'weighting',
            'type' => 'number',
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
        CRUD::setValidation(GradingScaleRequest::class);

        CRUD::field('letter_grade')->label('Letter grade');
        CRUD::field('min_score')->type('number')->label('Min score');
        CRUD::field('max_score')->type('number')->label('Max score');
        CRUD::field('weighting')->type('number')->attributes(['step' => '0.01']);
    }
}
