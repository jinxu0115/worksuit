<?php

namespace App\Http\Controllers;

use App\DataTables\LeaveQuotaReportDataTable;
use App\DataTables\LeaveReportDataTable;
use App\Models\LeaveType;
use App\Models\User;
use App\Scopes\ActiveScope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveReportController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.leaveReport';
    }

    public function index(LeaveReportDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_leave_report');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {
            $this->employees = User::allLeaveReportEmployees(null, true);
            $this->fromDate = now($this->company->timezone)->startOfMonth();
            $this->toDate = now($this->company->timezone)->endOfMonth();
        }

        return $dataTable->render('reports.leave.index', $this->data);
    }

    public function show(Request $request, $id)
    {
        $this->userId = $id;
        $view = $request->view;

        $this->leave_types = LeaveType::with(['leaves' => function ($query) use ($request, $id, $view) {
            if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
                $this->startDate = $request->startDate;
                $startDate = companyToDateString($request->startDate);
                $query->where(DB::raw('DATE(leaves.`leave_date`)'), '>=', $startDate);
            }

            if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
                $this->endDate = $request->endDate;
                $endDate = companyToDateString($request->endDate);
                $query->where(DB::raw('DATE(leaves.`leave_date`)'), '<=', $endDate);
            }

            switch ($view) {
            case 'pending':
                $query->where('status', 'pending')->where('user_id', $id);
                break;
            default:
                $query->where('status', 'approved')->where('user_id', $id);
                break;
            }
        }, 'leaves.type'])->get();

        if (request()->ajax() && $view != '') {
            $this->view = 'reports.leave.ajax.show';

            return $this->returnAjax($this->view);
        }

        return view('reports.leave.show', $this->data);
    }

    public function leaveQuota(LeaveQuotaReportDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_leave_report');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        $this->pageTitle = 'app.leaveQuotaReport';

        if (!request()->ajax()) {
            $this->year = now()->format('Y');
            $this->month = now()->format('m');
            $this->employees = User::allLeaveReportEmployees(null, true);
        }

        return $dataTable->render('reports.leave-quota.index', $this->data);
    }

    public function employeeLeaveQuota($id, $year, $month)
    {
        $forMontDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $thisMonthStartDate = now()->startOfMonth();

        $this->employee = User::with([
            'employeeDetail',
             'employeeDetail.designation',
             'employeeDetail.department',
             'country',
             'employee',
             'roles'
             ])
            ->onlyEmployee()
            ->when(!$thisMonthStartDate->eq($forMontDate), function($query) use($forMontDate) {
                $query->with([
                    'leaveQuotaHistory' => function($query) use($forMontDate) {
                        $query->where('for_month', $forMontDate);
                    },
                    'leaveQuotaHistory.leaveType',
                ])->whereHas('leaveQuotaHistory', function($query) use($forMontDate) {
                    $query->where('for_month', $forMontDate);
                });
            })
            ->when($thisMonthStartDate->eq($forMontDate), function($query) {
                $query->with([
                    'leaveTypes',
                    'leaveTypes.leaveType',
                ]);
            })
            ->withoutGlobalScope(ActiveScope::class)
            ->findOrFail($id);


        $hasLeaveQuotas = false;
        $totalLeaves = 0;
        $overUtilizedLeaves = 0;
        $leaveCounts = [];

        $settings = company();
        $now = Carbon::now();
        $yearStartMonth = $settings->year_starts_from;
        $leaveStartDate = null;
        $leaveEndDate = null;
        

        if($settings && $settings->leaves_start_from == 'year_start'){

            if ($yearStartMonth > $now->month) {
                // Not completed a year yet
                $leaveStartDate = Carbon::create($now->year, $yearStartMonth, 1)->subYear();
                $leaveEndDate = $leaveStartDate->copy()->addYear()->subDay();

            } else {
                $leaveStartDate = Carbon::create($now->year, $yearStartMonth, 1);
                $leaveEndDate = $leaveStartDate->copy()->addYear()->subDay();
                
            }

        } elseif ($settings && $settings->leaves_start_from == 'joining_date'){

            $joiningDate = Carbon::parse($this->employee->employeedetails->joining_date->format((now(company()->timezone)->year) . '-m-d'));
            $joinMonth = $joiningDate->month;
            $joinDay = $joiningDate->day;
            
            if ($joinMonth > $now->month || ($joinMonth == $now->month && $now->day < $joinDay)) {
                // Not completed a year yet
                $leaveStartDate = $joiningDate->copy()->subYear();
                $leaveEndDate = $joiningDate->copy()->subDay();
                
            } else {
                // Completed a year
                $leaveStartDate = $joiningDate;
                $leaveEndDate = $joiningDate->copy()->addYear()->subDay();
            }

        }
            

            $processedLeaveTypes = []; // Array to store processed leave_type_ids
                        
        if ($thisMonthStartDate->eq($forMontDate)) {
            $this->employeeLeavesQuotas = $this->employee->leaveTypes;

            foreach($this->employeeLeavesQuotas->unique('leave_type_id') as $leavesQuota)
            {
                if (in_array($leavesQuota->leave_type_id, $processedLeaveTypes)) {
                    continue; // Skip this iteration if already processed
                }
            
                // Add the current leave_type_id to the array
                $processedLeaveTypes[] = $leavesQuota->leave_type_id;

                $count = $this->employee->leaves()
                    ->where('leave_type_id', $leavesQuota->leave_type_id)->where('status', 'approved')
                    ->where(function ($query) use ($leaveStartDate, $leaveEndDate) {
                        $query->where('leave_date', '<=', $leaveEndDate)
                            ->where('leave_date', '>=', $leaveStartDate);
                    })->get()
                    ->sum(function($leave) {
                        return $leave->half_day_type ? 0.5 : 1;
                    });

                $overUtilizedLeaves += $this->employee->leaves()
                    ->where('leave_type_id', $leavesQuota->leave_type_id)->where('status', 'approved')
                    ->where(function ($query) use ($leaveStartDate, $leaveEndDate) {
                        $query->where('over_utilized', 1)
                            ->where('leave_date', '<=', $leaveEndDate)
                            ->where('leave_date', '>=', $leaveStartDate);
                    })->get()
                    ->sum(function($leave) {
                        return $leave->half_day_type ? 0.5 : 1;
                    });
                
                
                // Store the count in the array with leave_type_id as the key
                $leaveCounts[$leavesQuota->leave_type_id] = $count;

                if ($leavesQuota->leaveType && ($leavesQuota->leaveType->leaveTypeCondition($leavesQuota->leaveType, $this->employee)))
                {
                    $hasLeaveQuotas = true;
                    $totalLeaves = $totalLeaves + ($leavesQuota?->no_of_leaves ?: 0) - ($leaveCounts[$leavesQuota->leave_type_id] ?: 0);
                    // $totalLeaves += $leavesQuota->leaves_remaining;
                }
            }
        }
        else {

            $this->employeeLeavesQuotas = $this->employee->leaveQuotaHistory;
            
            foreach($this->employeeLeavesQuotas as $leavesQuota)
            {
                if (in_array($leavesQuota->leave_type_id, $processedLeaveTypes)) {
                    continue; // Skip this iteration if already processed
                }
            
                // Add the current leave_type_id to the array
                $processedLeaveTypes[] = $leavesQuota->leave_type_id;

                $count = $this->employee->leaves()
                    ->where('leave_type_id', $leavesQuota->leave_type_id)->where('status', 'approved')
                    ->where(function ($query) use ($leaveStartDate, $leaveEndDate) {
                        $query->where('leave_date', '<=', $leaveEndDate)
                            ->where('leave_date', '>=', $leaveStartDate);
                    })->get()
                    ->sum(function($leave) {
                        return $leave->half_day_type ? 0.5 : 1;
                    });

                $overUtilizedLeaves += $this->employee->leaves()
                    ->where('leave_type_id', $leavesQuota->leave_type_id)->where('status', 'approved')
                    ->where(function ($query) use ($leaveStartDate, $leaveEndDate) {
                        $query->where('over_utilized', 1)
                            ->where('leave_date', '<=', $leaveEndDate)
                            ->where('leave_date', '>=', $leaveStartDate);
                    })->get()
                    ->sum(function($leave) {
                        return $leave->half_day_type ? 0.5 : 1;
                    });
                
                
                // Store the count in the array with leave_type_id as the key
                $leaveCounts[$leavesQuota->leave_type_id] = $count;

                $hasLeaveQuotas = true;
                $sumrem = (($leavesQuota?->no_of_leaves ?: 0) > ($leaveCounts[$leavesQuota->leave_type_id] ?: 0) )
                    ? ($leavesQuota?->no_of_leaves ?: 0) - ($leaveCounts[$leavesQuota->leave_type_id] ?: 0) : 0;

                $totalLeaves += $sumrem;
            }
        }

        $this->leaveCounts = $leaveCounts;
        $this->hasLeaveQuotas = $hasLeaveQuotas;
        $this->allowedLeaves = $totalLeaves + $overUtilizedLeaves; // remining leaves
        
        return view('reports.leave-quota.show', $this->data);
    }

}
