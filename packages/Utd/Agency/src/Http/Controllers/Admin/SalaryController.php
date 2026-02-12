<?php

namespace Utd\Agency\Http\Controllers\Admin;

use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Utd\Agency\Entities\AgencySalary;
use Utd\Agency\Repositories\AgencySalaryRepository;
use Utd\Agency\Services\TargetService;

class SalaryController extends Controller
{
    protected $title = 'Agency Salaries';

    public function __construct(
        protected AgencySalaryRepository $salaryRepository,
        protected TargetService $targetService
    ) {}

    /**
     * Index interface
     */
    public function index(Content $content)
    {
        return $content
            ->title(__('Agency Salaries'))
            ->description(__('Salary records'))
            ->body($this->grid());
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(Request $request, $id)
    {
        try {
            $this->targetService->markSalaryAsPaid($id);

            return response()->json([
                'status' => true,
                'message' => 'Salary marked as paid',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get report
     */
    public function report(Request $request, Content $content)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $data = $this->salaryRepository->getSalaryReport($month, $year);

        return $content
            ->title(__('Salary Report'))
            ->body(view('agency::admin.salary-report', compact('data', 'month', 'year')));
    }

    /**
     * Make a grid builder
     */
    protected function grid()
    {
        $grid = new Grid(new AgencySalary());

        $grid->model()->orderByDesc('id');

        $grid->column('id', __('ID'))->sortable();
        $grid->column('agency.name', __('Agency'));
        $grid->column('month', __('Month'))->sortable();
        $grid->column('year', __('Year'))->sortable();
        $grid->column('sallary', __('Salary'))->display(function ($salary) {
            return number_format($salary, 2);
        });
        $grid->column('cut_amount', __('Cut Amount'))->display(function ($amount) {
            return number_format($amount, 2);
        });
        $grid->column('net_salary', __('Net Salary'))->display(function () {
            return number_format($this->sallary - $this->cut_amount, 2);
        });
        $grid->column('is_paid', __('Paid'))->display(function ($isPaid) {
            return $isPaid ? '<span class="label label-success">Paid</span>' : '<span class="label label-warning">Unpaid</span>';
        });
        $grid->column('created_at', __('Created At'))->sortable();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('agency_id', __('Agency ID'));
            $filter->equal('month', __('Month'));
            $filter->equal('year', __('Year'));
            $filter->equal('is_paid', __('Paid'))->select([
                0 => 'Unpaid',
                1 => 'Paid',
            ]);
        });

        $grid->disableCreateButton();

        return $grid;
    }
}
