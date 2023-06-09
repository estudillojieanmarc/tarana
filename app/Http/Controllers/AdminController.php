<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\employees;
use App\Models\applicants;
use App\Models\operations;
use App\Models\backout;
use App\Models\applied;
use App\Models\declined;
use App\Models\blockedApplicants;
use App\Models\completed;
use App\Models\cancelOperation;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\employeesImport;
use App\Imports\operationImport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use PDF;
use ZipArchive;


class AdminController extends Controller
{
    // ADMIN OPERATION FUNCTION
        // ADMIN DASHBOARD
            // ROUTES
                // ADMIN VISUALIZATION BLADE
                    public function adminDashboardRoutes(){
                        return view('administrator/dashboard');
                    }
                // ADMIN VISUALIZATION BLADE
            // ROUTES

            // FETCH
                // TOTAL OPERATION
                    public function totalUpcomingOperation(Request $request){
                        $date = date('Y-m-d H:i:s', strtotime("+1 hours", strtotime(now())));
                        $data = operations::where([['is_completed', '=', 0],[ 'is_archived' , '=' ,0],['operationEnd','>',$date]])->get();
                        $countData = $data->count();
                        return response()->json($countData != '' ? $countData : '0');
                    }
                // TOTAL OPERATION

                // TOTAL COMPLETED OPERATION
                    public function totalCompletedOperation(Request $request){
                        $data = operations::where('is_completed', '=', 1)->get();
                        $countData = $data->count();
                        return response()->json($countData != '' ? $countData : '0');
                    }
                // TOTAL COMPLETED OPERATION

                // TOTAL FOREMAN
                    public function totalForeman(Request $request){
                        $data = employees::where('position', '=', 'Recruiter')->where('is_active', '=', 1)->get();
                        $countData = $data->count();
                        return response()->json($countData != '' ? $countData : '0');
                    }
                // TOTAL FOREMAN

                // TOTAL APPLICANTS
                    public function totalApplicants(Request $request){
                        $data = applicants::where('is_active', '=', 1)->get();
                        $countData = $data->count();
                        return response()->json($countData != '' ? $countData : '0');
                    }
                // TOTAL APPLICANTS

                // VISUALIZATION
                    public function visualization(Request $request)
                    {
                        $distinctMonths = operations::selectRaw('DATE_FORMAT(operationStart, "%M") as monthName')
                        ->whereRaw('MONTH(operationStart) BETWEEN 1 AND 12')
                        ->groupBy('monthName')
                        ->orderByRaw('MONTH(operationStart)')
                        ->get();

                    $months = $distinctMonths->pluck('monthName')->toArray();

                    // Ensure January is the first month
                    $januaryIndex = array_search('January', $months);
                    if ($januaryIndex !== false) {
                        $months = array_merge(array_slice($months, $januaryIndex), array_slice($months, 0, $januaryIndex));
                    }

                    $data = operations::selectRaw('DATE_FORMAT(operationStart, "%M") as monthName, COUNT(*) as totalOperation')
                        ->groupBy('monthName')
                        ->orderByRaw('MONTH(operationStart)')
                        ->get();

                    $operations = [];
                    foreach ($data as $monthData) {
                        $operations[$monthData->monthName] = $monthData->totalOperation;
                    }

                    foreach ($months as $month) {
                        if (!isset($operations[$month])) {
                            $operations[$month] = 0;
                        }
                    }

                    $formattedOperations = [];
                    foreach ($months as $month) {
                        $formattedOperations[] = $operations[$month];
                    }

                    return response()->json([
                        'months' => $months,
                        'operations' => $formattedOperations
                    ]);
                    }
                // VISUALIZATION

                // GET HIGHEST RATING
                    public function getHighestRating(Request $request){
                        $data = applicants::join('completed', 'applicants.applicant_id', '=', 'completed.applicant_id')
                        ->select(
                            'applicants.applicant_id',
                            'applicants.lastname',
                            'applicants.firstname',
                            'applicants.extention',
                            'applicants.age',
                            \DB::raw('AVG(completed.performanceRating) as averageRating')
                        )
                        ->orderBy('averageRating', 'desc')
                        ->groupBy('applicants.applicant_id', 'applicants.lastname', 'applicants.firstname', 'applicants.extention', 'applicants.age')
                        ->limit(10)
                        ->get();
                        return response()->json($data);
                    }
                // GET HIGHEST RATING

                // MOST COMMON CARGO
                    public function mostCommonCargo(Request $request){
                        $data = operations::where([
                            ['is_completed', '=', 1]
                        ])
                        ->orderBy('operationStart')
                        ->select('shipCarry', \DB::raw('COUNT(*) as count'))
                        ->groupBy('shipCarry')
                        ->get();

                    $shipCarry = $data->pluck('shipCarry');
                    $count = $data->pluck('count');

                    $response = [
                        'shipCarry' => $shipCarry,
                        'count' => $count
                    ];

                    return response()->json($response);
                    }
                // MOST COMMON CARGO
            // FETCH
        // ADMIN DASHBOARD

        // ADMIN OPERATION DASHBOARD
            // ROUTES
                // NOT COMPLETED ROUTES
                    public function adminOperationRoutes(){
                        return view('administrator/operation');
                    }
                // NOT COMPLETED ROUTES

                // COMPLETED ROUTES
                    public function adminCompletedOperation(){
                        return view('administrator/completedOperation');
                    }
                // COMPLETED ROUTES

                // CANCELLED OPERATION ROUTES
                    public function adminCancelledOperation(){
                        return view('administrator/cancelledOperation');
                    }
                // CANCELLED OPERATION ROUTES
            // ROUTES

            // FETCH
                // UPCOMING OPERATION
                    public function getOperationData(Request $request){

                        $data = operations::where([['is_completed','=',0],['is_archived','=',0]])->orderBy('operationStart')->get();
                        return response()->json($data);
                    }
                // UPCOMING OPERATION

                // COMPLETED OPERATION
                    public function getCompletedOperationData(Request $request){
                        $data = operations::where('is_completed' ,'=', 1)->get();
                        return response()->json($data);
                    }
                // COMPLETED OPERATION

                // CERTAIN OPERATION
                    public function showCertainOperation(Request $request){
                        $data = operations::where('certainOperation_id', '=', $request->operationId)->first();
                        return response()->json($data);
                    }
                // CERTAIN OPERATION

                // GENERATE ID
                    public function generateOperationId(Request $request){
                        $randomNumber = rand(00001,99999);
                        $year = date("Y");
                        echo $year.''.$randomNumber;
                    }
                // GENERATE ID

            // FETCH

            // ADD
                public function addOperation(Request $request){
                    date_default_timezone_set('Asia/Manila');
                    $currentDateTime = date('m-d-Y h:i A');
                    $newOperationStartDate = date('m-d-Y h:i A',strtotime($request->addOperationStart));
                    $newOperationEndDate = date('m-d-Y h:i A',strtotime($request->addOperationEnd));
                    if($currentDateTime > $newOperationStartDate){
                        // INVALID OPERATION START | DATE AND TIME
                        return response()->json(4);
                        exit();
                    }else if($newOperationStartDate == $newOperationEndDate){
                        // the date of both operation date and time must not the same';
                        return response()->json(2);
                        exit();
                    }else if($newOperationEndDate < $newOperationStartDate ){
                        // invalid operation end date/time';
                        return response()->json(3);
                        exit();
                    }else{
                        $filename = $request->file('addOperationPhoto');
                        $imageName =   time().rand() . '.' .  $filename->getClientOriginalExtension();
                        $path = $request->file('addOperationPhoto')->storeAs('ship', $imageName, 'public');
                        $imageData['addOperationPhoto'] = '/storage/'.$path;
                        $addOperation = operations::create([
                        'operationId' => $request->addOperationId,
                        'photos' => $imageData['addOperationPhoto'],
                        'shipName' => $request->addShipsName,
                        'shipCarry' => $request->addShipsCarry,
                        'operationStart' => $request->addOperationStart,
                        'operationEnd' => $request->addOperationEnd,
                        'totalWorkers' => $request->addApplicantsSlot,
                        'slot' => $request->addApplicantsSlot,
                        'is_completed' => 0,
                        'is_archived' => 0
                        ]);
                        return response()->json($addOperation ? 1 : 0);
                        exit();
                    }
                }
            // ADD

            // UPDATE
                public function updateOperation(Request $request){
                    // dd($request);
                    $currentDateTime = date('y-m-d h:i:a');
                    $newOperationStart = date('y-m-d h:i:a', strtotime($request->operationStart));
                    $newOperationEnd = date('y-m-d h:i:a', strtotime($request->operationEnd));
                    if($newOperationStart == $newOperationEnd){
                        // INVALID OPERATION START | DATE AND TIME
                        return response()->json(2);
                        exit();
                    }elseif($currentDateTime > $newOperationStart){
                        // INVALID OPERATION START | DATE AND TIME
                        return response()->json(4);
                        exit();
                    }elseif($currentDateTime > $newOperationEnd || $newOperationEnd < $newOperationStart){
                        // INVALID OPERATION START | DATE AND TIME
                        return response()->json(3);
                        exit();
                    }else{
                        if ($request->hasFile('operationPhoto')) {
                            $filename = $request->file('operationPhoto');
                            $imageName =   time().rand() . '.' .  $filename->getClientOriginalExtension();
                            $path = $request->file('operationPhoto')->storeAs('ship', $imageName, 'public');
                            $imageData['operationPhoto'] = '/storage/'.$path;
                            $updateOperation = operations::where('certainOperation_id', $request->certainOperation_id)->update([
                                'photos' => $imageData['operationPhoto'],
                                'shipName' => $request->shipName,
                                'shipCarry' => $request->shipCarry,
                                'operationStart' => $request->operationStart,
                                'operationEnd' => $request->operationEnd,
                                'slot' => $request->slot,
                            ]);
                            return response()->json($updateOperation ? 1 : 0);
                        }else{
                            $updateOperation = operations::where('certainOperation_id', $request->certainOperation_id)->update([
                                'shipName' => $request->shipName,
                                'shipCarry' => $request->shipCarry,
                                'operationStart' => $request->operationStart,
                                'operationEnd' => $request->operationEnd,
                                'slot' => $request->slot,
                            ]);
                            return response()->json($updateOperation ? 1 : 0);
                        }
                    }
                }
            // UPDATE

            // CANCEL OPERATION
                public function cancelOperation(Request $request){
                    $cancelOperation = operations::where([['certainOperation_id', '=', $request->operationId]])->update(['is_archived' => 1]);
                    if($cancelOperation){
                        $reasonOfCancel = canceloperation::create([
                            'operation_id' => $request->operationId,
                            'reason' => $request->reason
                        ]);
                        if($reasonOfCancel){
                            $applied = applied::where([['operation_id', '=', $request->operationId]])->delete();
                            $backout = backout::where([['operation_id', '=', $request->operationId]])->update(['is_archived' => 1]);
                            $declined = declined::where([['operation_id', '=', $request->operationId]])->update(['is_archived' => 1]);
                            return response()->json($applied && $backout && $declined ? 1 : 0);
                        }else{
                            dd(0);
                        }
                    }
                }
            // CANCEL OPERATION
        // ADMIN OPERATION DASHBOARD

        // ADMIN EMPLOYEES DASHBOARD
            // ROUTES
                // ACTIVE EMPLOYEES ROUTES
                    public function adminEmployeesRoutes(){
                        return view('administrator/employees');
                    }
                // ACTIVE EMPLOYEES ROUTES

                // INACTIVE EMPLOYEES ROUTES
                    public function inactiveEmployees(){
                        return view('administrator/inactiveEmployees');
                    }
                // INACTIVE EMPLOYEES ROUTES

                // CURRENTLY UTILIZING
                    public function utilizedAppRecruiter(){
                        return view('administrator/utilizedAppRecruiter');
                    }
                // CURRENTLY UTILIZING
            // ROUTES

            // FETCH
                // ACTIVE EMPLOYEES DATA FOR TABLE
                    public function getAllEmployeesData(Request $request){
                        $data = employees::where([['position', '=', 'Recruiter'],['is_active', '=', 1]])->get();
                        return response()->json($data);
                    }
                // ACTIVE EMPLOYEES DATA FOR TABLE

                // INACTIVE EMPLOYEES DATA FOR TABLE
                    public function getInactiveEmployees(Request $request){
                        $data = employees::where([['position', '=', 'Recruiter'],['is_active', '=', 0]])->get();
                        return response()->json($data);
                    }
                // INACTIVE EMPLOYEES DATA FOR TABLE

                // FETCH SPECIFIC EMPLOYEES
                    public function getCertainEmployee(Request $request){
                        $data = employees::where('employee_id', '=', $request->employeesId)->first();
                        return response()->json($data);
                    }
                // FETCH SPECIFIC EMPLOYEES

                // ALL APPLICANTS CURRENTLY USED APPLICATION
                    public function getEmpCurrentlyUtilizing(Request $request){
                        $data = employees::where([['is_active', '=', 1],['is_utilized', '=', 1],
                        ['employee_id', '!=', auth()->guard('employeesModel')->user()->employee_id],])->get();
                        return response()->json($data);
                    }
                // ALL APPLICANTS CURRENTLY USED APPLICATION

                // DEACTIVATE EMPLOYEES
                    public function deactivateEmployee(Request $request){
                        $employee = employees::find($request->employee_id);
                        $employee->is_active = 0;
                        $employee->save();
                    }
                // DEACTIVATE EMPLOYEES

                // ACTIVATE EMPLOYEES
                    public function activateEmployee(Request $request){
                        $employee = employees::find($request->employee_id);
                        $employee->is_active = 1;
                        $employee->save();
                    }
                // ACTIVATE EMPLOYEES

                // UNUTILIZED APPLICANT
                    public function unutilizedEmployee(Request $request){
                        $employees = employees::find($request->EmployeeId);
                        $employees->is_utilized = 0;
                        $employees->save();
                    }
                // UNUTILIZED APPLICANT

                // UPDATE EMPLOYEES
                    public function updateEmployees(Request $request){
                        if ($request->hasFile('employeePhoto')) {
                            $filename = $request->file('employeePhoto');
                            $imageName =   time().rand() . '.' .  $filename->getClientOriginalExtension();
                            $path = $request->file('employeePhoto')->storeAs('employees', $imageName, 'public');
                            $imageData['employeePhoto'] = '/storage/'.$path;
                            $updateEmployee = employees::where('employee_id', $request->uniqueEmployeeId)->update([
                                'companyId' => $request->employeeId,
                                'photos' =>  $imageData['employeePhoto'],
                                'lastname' => $request->employeeLastname,
                                'firstname' => $request->employeeFirstname,
                                'middlename' => $request->employeeMiddlename,
                                'extention' => $request->employeeExt,
                                'gender' => $request->employeesSex,
                                'position' => $request->employeePosition,
                                'status' => $request->employeeStatus,
                                'age' => $request->employeeAge,
                                'birthday' => $request->employeeBirthday,
                                'address' => $request->employeeAddress,
                                'phoneNumber' => $request->employeePnumber,
                                'emailAddress' => $request->employeeEmail,
                            ]);
                            return response()->json($updateEmployee ? 1 : 0);
                        }else{
                            $updateEmployee = employees::where('employee_id', $request->uniqueEmployeeId)->update([
                                'companyId' => $request->employeeId,
                                'lastname' => $request->employeeLastname,
                                'firstname' => $request->employeeFirstname,
                                'middlename' => $request->employeeMiddlename,
                                'extention' => $request->employeeExt,
                                'gender' => $request->employeesSex,
                                'position' => $request->employeePosition,
                                'status' => $request->employeeStatus,
                                'age' => $request->employeeAge,
                                'birthday' => $request->employeeBirthday,
                                'address' => $request->employeeAddress,
                                'phoneNumber' => $request->employeePnumber,
                                'emailAddress' => $request->employeeEmail,
                            ]);
                            return response()->json($updateEmployee ? 1 : 0);
                        }
                    }
                // UPDATE EMPLOYEES

                // ADD EMPLOYEES
                    public function addEmployee(Request $request){
                        $existingID = employees::where('companyId', '=', $request->addEmployeeId)->get();
                        $existingEmail = employees::where('emailAddress', '=', $request->addEmployeeEmail)->get();
                        if($existingID->isNotEmpty()){
                            return response()->json(2);
                            exit();
                        }else if($existingEmail->isNotEmpty()){
                            return response()->json(3);
                            exit();
                        }else{
                            if ($request->hasFile('addEmployeePhoto')) {
                                $filename = $request->file('addEmployeePhoto');
                                $imageName =   time().rand() . '.' .  $filename->getClientOriginalExtension();
                                $path = $request->file('addEmployeePhoto')->storeAs('ship', $imageName, 'public');
                                $imageData['addEmployeePhoto'] = '/storage/'.$path;
                                $addEmployee = employees::create([
                                    'companyId' => $request->addEmployeeId,
                                    'photos' => $imageData['addEmployeePhoto'],
                                    'lastname' => $request->addEmployeeLastname,
                                    'firstname' => $request->addEmployeeFirstname,
                                    'middlename' => $request->addEmployeeMiddlename,
                                    'extention' => $request->addEmployeeExt,
                                    'gender' => $request->addEmployeeGender,
                                    'position' => $request->addEmployeePosition,
                                    'status' => $request->addEmployeeStatus,
                                    'age' => $request->addEmployeeAge,
                                    'birthday' => $request->addEmployeeBirthday,
                                    'nationality' => $request->addEmployeeNationality,
                                    'religion' => $request->addEmployeeReligion,
                                    'address' => $request->addEmployeeAddress,
                                    'phoneNumber' => $request->addEmployeePnumber,
                                    'emailAddress' => $request->addEmployeeEmail,
                                    'username' => $request->addEmployeeUsername,
                                    'password' => 'default123',
                                    'is_active' => 1,
                                    'is_utilized' => 0,
                                ]);
                                return response()->json($addEmployee ? 1 : 0);
                            }else{
                                $addEmployee = employees::create([
                                    'companyId' => $request->addEmployeeId,
                                    'photos' => '/storage/employees/defaultImage.png',
                                    'lastname' => $request->addEmployeeLastname,
                                    'firstname' => $request->addEmployeeFirstname,
                                    'middlename' => $request->addEmployeeMiddlename,
                                    'extention' => $request->addEmployeeExt,
                                    'gender' => $request->addEmployeeGender,
                                    'position' => $request->addEmployeePosition,
                                    'status' => $request->addEmployeeStatus,
                                    'age' => $request->addEmployeeAge,
                                    'birthday' => $request->addEmployeeBirthday,
                                    'nationality' => $request->addEmployeeNationality,
                                    'religion' => $request->addEmployeeReligion,
                                    'address' => $request->addEmployeeAddress,
                                    'phoneNumber' => $request->addEmployeePnumber,
                                    'emailAddress' => $request->addEmployeeEmail,
                                    'username' => $request->addEmployeeUsername,
                                    'password' => 'default123',
                                    'is_active' => 1,
                                    'is_utilized' => 0,
                                ]);
                                return response()->json($addEmployee ? 1 : 0);
                            }
                        }

                    }
                // ADD EMPLOYEES
            // FETCH

            // IMPORT

                // EMPLOYEES
                    public function employeesImport(Request $request){
                        $importEmployee = Excel::import(new employeesImport, $request->fileImport);
                        return response()->json($importEmployee ? 1 : 0);
                    }
                // EMPLOYEES

                // OPERATION
                    public function operationImport(Request $request){
                        $importOperation = Excel::import(new operationImport, $request->fileImport);
                        return response()->json($importOperation ? 1 : 0);
                    }
                // OPERATION
            // IMPORT
        // ADMIN EMPLOYEES DASHBOARD

        // ADMIN APPLICANTS DASHBOARD
            // ROUTES
                // ACTIVE APPLICANTS ROUTES
                    public function adminApplicantsRoutes(){
                        return view('administrator/applicants');
                    }
                // ACTIVE APPLICANTS ROUTES

                // INACTIVE APPLICANTS ROUTES
                    public function inactiveApplicants(){
                        return view('administrator/inactiveApplicants');
                    }
                // INACTIVE APPLICANTS ROUTES

                // BLOCKED APPLICANTS ROUTES
                    public function blockedApplicants(){
                        return view('administrator/blockedApplicants');
                    }
                // BLOCKED APPLICANTS ROUTES

                // BLOCKED APPLICANTS ROUTES
                    public function utilizedApplication(){
                        return view('administrator/utilized');
                    }
                // BLOCKED APPLICANTS ROUTES

                // OLD APPLICANTS ROUTES
                        public function adminOldApplicantsRoutes(){
                        return view('administrator/oldApplicants');
                    }
                // OLD APPLICANTS ROUTES

                // INACTIVE OLD APPLICANTS ROUTES
                        public function inactiveOldApplicantsRoutes(){
                        return view('administrator/inactiveOldApplicants');
                    }
                // INACTIVE OLD APPLICANTS ROUTES

                // BLOCKED OLD APPLICANTS ROUTES
                        public function blockedOldApplicantsRoutes(){
                        return view('administrator/blockedOldApplicants');
                    }
                // BLOCKED OLD APPLICANTS ROUTES


            // ROUTES

            // FETCH
                // ALL ACTIVE APPLICANTS DATA
                    public function getAdminAllApplicantsData(Request $request){
                            $data = applicants::where([['is_active', '=', 1],['lastname', '!=', ''],['firstname', '!=', ''],['is_pro', '=' , 0]])->get();
                            return response()->json($data);
                    }
                // ALL ACTIVE APPLICANTS DATA

                // ALL ACTIVE OLD APPLICANTS DATA
                    public function getAdminAllOldApplicantsData(Request $request){
                            $data = applicants::where([['is_active', '=', 1],['lastname', '!=', ''],['firstname', '!=', ''],['is_pro', '=' , 1]])->get();
                            return response()->json($data);
                    }
                // ALL ACTIVE OLD APPLICANTS DATA

                // ALL INACTIVE APPLICANTS DATA
                    public function getInactiveApplicantsData(Request $request){
                        $data = applicants::where([['is_active', '=', 0], ['is_blocked', '!=', 1]])->get();
                        return response()->json($data);
                    }
                // ALL INACTIVE APPLICANTS DATA

                // ALL INACTIVE APPLICANTS DATA
                        public function getInactiveOldApplicantsData(Request $request){
                            $data = applicants::where([['is_active', '=', 0], ['is_blocked', '!=', 1],['is_pro', '=' , 1]])->get();
                            return response()->json($data);
                        }
                // ALL INACTIVE APPLICANTS DATA

                // ALL BLOCKED APPLICANTS DATA
                        public function getBlockedOldApplicantsData(Request $request){
                            $data = applicants::join('blockedapplicants', 'applicants.applicant_id', '=', 'blockedapplicants.applicantId')
                            ->where([['applicants.is_pro', '=' , 1],['blockedapplicants.is_archived', '=' , 0]])->get(['applicants.*', 'blockedapplicants.blockId',
                            'blockedapplicants.reason', 'blockedapplicants.date_time_block']);
                            return response()->json($data);
                        }
                // ALL BLOCKED APPLICANTS DATA

                // ALL APPLICANTS CURRENTLY USED APPLICATION
                        public function getCurrentlyUtilizing(Request $request){
                            $data = applicants::where([['is_active', '=', 1],['is_utilized', '=', 1]])->get();
                            return response()->json($data);
                        }
                // ALL APPLICANTS CURRENTLY USED APPLICATION

                // ALL BLOCKED APPLICANTS DATA
                        public function getBlockedApplicants(Request $request){
                            $data = applicants::join('blockedapplicants', 'applicants.applicant_id', '=', 'blockedapplicants.applicantId')
                            ->where([['applicants.is_pro', '=' , 0],['blockedapplicants.is_archived', '=' , 0]])
                            ->get(['applicants.*', 'blockedapplicants.blockId', 'blockedapplicants.reason', 'blockedapplicants.date_time_block']);
                            return response()->json($data);
                        }
                // ALL BLOCKED APPLICANTS DATA

                // FETCH SPECIFIC APPLICANTS
                        public function viewApplicants(Request $request){
                            $data = applicants::where('applicant_id', '=', $request->applicantId)->first();
                            return response()->json($data);
                        }
                // FETCH SPECIFIC APPLICANTS

                // DEACTIVATE APPLICANT
                        public function deactivateApplicants(Request $request){
                            $applicant = applicants::find($request->applicantId);
                            $applicant->is_active = 0;
                            $applicant->save();
                        }
                // DEACTIVATE APPLICANT

                // ACTIVATE APPLICANT
                    public function activateApplicant(Request $request){
                        $applicant = applicants::find($request->applicantId);
                        $applicant->is_active = 1;
                        $applicant->save();
                    }
                // ACTIVATE APPLICANT

                // UNUTILIZED APPLICANT
                    public function unutilizedApplicant(Request $request){
                        $applicant = applicants::find($request->applicantId);
                        $applicant->is_utilized = 0;
                        $applicant->save();
                    }
                // UNUTILIZED APPLICANT

                // BLOCKED APPLICANT
                    public function blockedApplicant(Request $request){
                        // UPDATE DATA
                        $applicantUpdated = applicants::find($request->applicantId)->update(['is_active' => 0 , 'is_blocked' => 1]);
                        // INSERT DATA
                        $blockedApplicants = [
                            'applicantId' => $request->applicantId,
                            'reason' => $request->reason,
                            'date_time_block' => now(),
                            'is_archived' => 0
                        ];
                        return response()->json(blockedApplicants::insert($blockedApplicants) ? 1 : 0);
                    }
                // BLOCKED APPLICANT

                // UNBLOCK APPLICANT
                    public function unblockApplicant(Request $request){
                        $applicant = applicants::find($request->applicantId);
                        $applicant->is_active = 1;
                        $applicant->is_blocked = 0;
                        $applicant->save();
                        blockedapplicants::where([['applicantId', '=', $request->applicantId]])->update(['is_archived' =>  1]);
                    }
                // UNBLOCK APPLICANT

                // FETCH APPLICANTS ON CERTAIN OPERATION
                    public function showApplicantOnCertainOperation(Request $request){
                        $data = completed::join('applicants', 'completed.applicant_id', '=', 'applicants.applicant_id')
                        ->join('employees', 'completed.recruiter_id', '=', 'employees.employee_id')
                        ->where('completed.operation_id', '=', $request->operationId)->orderBy('completed.performanceRating' , 'desc')
                        ->select('completed.performanceRating','applicants.lastname AS applicantLastName', 'applicants.firstname AS applicantFirstname',
                        'applicants.extention AS applicantExtention', 'applicants.age','employees.lastname AS employeeLastName',
                        'employees.firstname AS employeeFirstName','employees.extention AS employeeExtension')
                        ->get();
                        echo "
                        <table class='table table-bordered text-center align-middle'>
                            <thead>
                                <tr>
                                    <th scope='col'>#</th>
                                    <th scope='col'>Applicant</th>
                                    <th scope='col'>Age</th>
                                    <th scope='col'>Performance</th>
                                </tr>
                            </thead>
                            <tbody>";
                            foreach($data as $count => $certainApplicantData){
                                $count = $count +1;
                                echo"
                                    <tr>
                                        <td>$count</td>
                                        <td>$certainApplicantData->applicantFirstname $certainApplicantData->applicantLastName $certainApplicantData->applicantExtention</td>
                                        <td>$certainApplicantData->age years old</td>
                                        <td>Rating: $certainApplicantData->performanceRating%</td>
                                    </tr>
                                    ";
                                }
                                    echo"
                            </tbody>
                        </table>";
                    }
                // FETCH APPLICANTS ON CERTAIN OPERATION
            // FETCH
        // ADMIN APPLICANTS DASHBOARD

        // ADMIN MANAGE ACCOUNT BLADE
            public function adminManageAccount(){
                return view('administrator/manageAccount');
            }
        // ADMIN MANAGE ACCOUNT BLADE

        // FETCH PERSONAL INFO | ACCOUNT
            public function getPersonalInfo(Request $request){
                // FETCH ALL DATA
                    $data = employees::all()->where('employee_id' ,'=', auth()->guard('employeesModel')->user()->employee_id)
                    ->first();
                    return response()->json($data);
                // FETCH ALL DATA
            }
        // FETCH PERSONAL INFO | ACCOUNT

        // EDIT ADMIN ACCOUNT
            public function updateAdminAccount(Request $request){
                if ($request->hasFile('updateEmployeePhoto')) {
                    $filename = $request->file('updateEmployeePhoto');
                    $imageName =   time().rand() . '.' .  $filename->getClientOriginalExtension();
                    $path = $request->file('updateEmployeePhoto')->storeAs('employees', $imageName, 'public');
                    $imageData['updateEmployeePhoto'] = '/storage/'.$path;
                    // EDIT ONE
                        $update = employees::find($request->uniqueEmployeeId);
                        $update->companyId=$request->input('updateCompanyId');
                        $update->photos=$imageData['updateEmployeePhoto'];
                        $update->firstname=$request->input('updateEmployeeFirstname');
                        $update->middlename=$request->input('updateEmployeeMiddlename');
                        $update->lastname=$request->input('updateEmployeeLastname');
                        $update->extention=$request->input('updateEmployeeExt');
                        $update->gender=$request->input('updateEmployeesSex');
                        $update->status=$request->input('updateEmployeeStatus');
                        $update->birthday=$request->input('updateEmployeeBirthday');
                        $update->age=$request->input('updateEmployeeAge');
                        $update->address=$request->input('updateEmployeeAddress');
                        $update->phoneNumber=$request->input('updateEmployeePnumber');
                        $update->emailAddress=$request->input('updateEmployeeEmail');
                        $update->save();
                        return response()->json(1);
                    // EDIT ONE
                }else{
                    // EDIT TWO
                        $update = employees::find($request->uniqueEmployeeId);
                        $update->companyId=$request->input('updateCompanyId');
                        $update->firstname=$request->input('updateEmployeeFirstname');
                        $update->middlename=$request->input('updateEmployeeMiddlename');
                        $update->lastname=$request->input('updateEmployeeLastname');
                        $update->extention=$request->input('updateEmployeeExt');
                        $update->gender=$request->input('updateEmployeesSex');
                        $update->status=$request->input('updateEmployeeStatus');
                        $update->birthday=$request->input('updateEmployeeBirthday');
                        $update->age=$request->input('updateEmployeeAge');
                        $update->address=$request->input('updateEmployeeAddress');
                        $update->phoneNumber=$request->input('updateEmployeePnumber');
                        $update->emailAddress=$request->input('updateEmployeeEmail');
                        $update->save();
                        return response()->json(1);
                    // EDIT TWO
                }
            }
        // EDIT ADMIN ACCOUNT

        // CREDENTIALS ROUTES
            public function adminCredentials(){
                return view('administrator/adminCredentials');
            }
        // CREDENTIALS ROUTES

        // UPDATE PASSWORD
            public function updateUsersPassword(Request $request){
                $passwordVerify = employees::select('password')->where('employee_id', '=',  auth()->guard('employeesModel')->user()->employee_id)->first();
                if(!Hash::check($request->currentPassword, $passwordVerify->password)){
                    return response()->json(0);
                }else{
                    $update = employees::find(auth()->guard('employeesModel')->user()->employee_id);
                    $update->password = Hash::make($request->input('confirmPassword'));
                    $update->save();
                    return response()->json(1);
                }
            }
        // UPDATE PASSWORD

        // PRINT OPERATION
            public function printOperation(Request $request, $id){
                $data = operations::where([['certainOperation_id', '=', $id]])->get();
                foreach($data as $certainData){
                    $operationInfo = [
                        'data' => $data
                    ];
                }
                $pdf = PDF::loadView('fetch.admin.printOperation', $operationInfo)->setPaper('A4', 'portrait');
                return $pdf->stream('operation_'.$certainData->operationId.'.pdf');

            }
        // PRINT OPERATION

        // PRINT COMPLETED OPERATION
            public function printCompletedOperation(Request $request, $id){
                $data = completed::join('operations', 'completed.operation_id', '=', 'operations.certainOperation_id')
                ->join('applicants', 'completed.applicant_id', '=', 'applicants.applicant_id')
                ->join('employees', 'completed.recruiter_id', '=', 'employees.employee_id')
                ->where([['completed.operation_id', '=', $id],['operations.certainOperation_id', '=', $id]])
                ->select('operations.*','employees.firstname as employeeFirstname','employees.lastname as employeeLastname',
                'employees.extention as employeeExtention','applicants.firstname as applicantFirstname','applicants.lastname as applicantLastname',
                'applicants.extention as applicantExtention','applicants.age','completed.performanceRating','completed.created_at')
                ->get();
                foreach($data as $operations){
                    $created_at = date('F d, Y | h:i: a',strtotime($operations->created_at));
                    $operationCompleted = [
                        'operationId' => $operations->operationId,
                        'shipName' => $operations->shipName,
                        'shipCarry' => $operations->shipCarry,
                        'operationStart' => $operations->operationStart,
                        'operationEnd' => $operations->operationEnd,
                        'totalWorkers' => $operations->totalWorkers,
                        'slot' => $operations->slot,
                        'employeeFirstname' => $operations->employeeFirstname,
                        'employeeLastname' => $operations->employeeLastname,
                        'employeeExtention' => $operations->employeeExtention,
                        'created_at' => $created_at,
                        'data' => $data,
                    ];
                }
                $pdf = PDF::loadView('fetch.recruiter.recruiterCompleted', $operationCompleted);
                return $pdf->stream('operation'.$operations->operationId.'.pdf');
            }
        // PRINT COMPLETED OPERATION

        // PRINT APPLICANT
            public function printProjectWorker(Request $request, $id){
                $data = applicants::where([['applicant_id', '=', $id]])->get();
                foreach($data as $count => $certainData){
                    $applicantInfo = [
                        'data' => $data
                    ];
                }
                $pdf = PDF::loadView('fetch.applicants.applicantsInfo', $applicantInfo);
                return $pdf->stream('Project_Worker_'.$certainData->lastname.','.$certainData->firstname.' '.$certainData->extention.'.pdf');
            }
        // PRINT APPLICANT

        // PRINT COMPANY EMPLOYEE
            public function printCompanyEmployee(Request $request, $id){
                $data = employees::where([['employee_id', '=', $id]])->get();
                foreach($data as $count => $certainData){
                    $employeeInfo = [
                        'data' => $data
                    ];
                }
                $pdf = PDF::loadView('fetch.admin.printEmployees', $employeeInfo);
                return $pdf->stream('Manpower_Pooling_'.$certainData->lastname.','.$certainData->firstname.' '.$certainData->extention.'.pdf');
            }
        // PRINT COMPANY EMPLOYEE

        // DOWNLOAD TEMPLATE
            public function downloadExcel(){
                return response()->download('C:/xampp/htdocs/tarana/public/storage/template/employeesImport.xlsx');
            }
        // DOWNLOAD TEMPLATE

        // ARCHIVE
                // BACKOUT ARCHIVED ROUTES
                    public function adminBackOutArchiveRoutes(){
                        return view('administrator/backOutArchive');
                    }
                // BACKOUT ARCHIVED ROUTES

                // DECLINED ARCHIVED ROUTES
                    public function adminDeclinedArchiveRoutes(){
                        return view('administrator/declinedArchive');
                    }
                // DECLINED ARCHIVED ROUTES

                // BLOCKED APPLICANTS ARCHIVED ROUTES
                    public function adminBlockedArchiveRoutes(){
                        return view('administrator/blockApplicantArchive');
                    }
                // BLOCKED APPLICANTS ARCHIVED ROUTES

                // CANCELLED OPERATION ROUTES
                    public function adminCancelOperationArchiveRoutes(){
                        return view('administrator/cancelledOperation');
                    }
                // CANCELLED OPERATION ROUTES

                // CANCELLED OPERATION ROUTES
                    public function adminScheduleRoutes(){
                        return view('administrator/operationSchedule');
                    }
                // CANCELLED OPERATION ROUTES

                // FETCH
                    // BACKOUT ARCHIVED DATA
                        public function getBackOutArchivedForAdmin(Request $request){
                            $data = backout::join('operations', 'backout.operation_id', '=', 'operations.certainOperation_id')
                            ->join('applicants', 'backout.applicant_id', '=', 'applicants.applicant_id')
                            ->join('employees', 'backout.recruiter_id', '=', 'employees.employee_id')
                            ->select('operations.*','backout.backOut_id','backout.reason','applicants.applicant_id', 'applicants.lastname AS applicantLastName', 'applicants.firstname AS applicantFirstname',
                            'applicants.extention AS applicantExtention','applicants.phoneNumber',
                            'employees.lastname AS employeeLastName', 'employees.firstname AS employeeFirstName','employees.extention AS employeeExtension' )
                            ->orderBy('operations.operationStart', 'DESC')
                            ->get();
                            return response()->json($data);
                        }
                    // BACKOUT ARCHIVED DATA

                    // DECLINED ARCHIVED DATA
                        public function getDeclinedArchivedForAdmin(Request $request){
                            $data = declined::join('operations', 'declined.operation_id', '=', 'operations.certainOperation_id')
                            ->join('applicants', 'declined.applicant_id', '=', 'applicants.applicant_id')
                            ->join('employees', 'declined.recruiter_id', '=', 'employees.employee_id')
                            ->select('operations.*','declined.declined_id','declined.reason','applicants.applicant_id', 'applicants.lastname AS applicantLastName', 'applicants.firstname AS applicantFirstname',
                            'applicants.extention AS applicantExtention', 'applicants.phoneNumber',
                            'employees.lastname AS employeeLastName', 'employees.firstname AS employeeFirstName','employees.extention AS employeeExtension' )
                            ->orderBy('operations.operationStart', 'DESC')
                            ->get();
                            return response()->json($data);
                        }
                    // DECLINED ARCHIVED DATA

                    // CANCELLED OPERATION DATA
                        public function getCancelOperationData(Request $request){
                            $data = operations::join('canceloperation', 'canceloperation.operation_id', '=', 'operations.certainOperation_id')
                            ->select('operations.operationId','operations.shipName','operations.shipCarry','operations.operationStart',
                            'operations.operationEnd', 'canceloperation.cancelOperation_id', 'canceloperation.reason')
                            ->where([['is_completed','=',0],['is_archived','=',1]])
                            ->get();
                            return response()->json($data);
                        }
                    // CANCELLED OPERATION DATA

                    // ALL BLOCKED APPLICANTS DATA
                        public function getArchivedBlockedApplicantsForAdmin(Request $request){
                            $data = applicants::join('blockedapplicants', 'applicants.applicant_id', '=', 'blockedapplicants.applicantId')
                            ->orderBy('blockedapplicants.date_time_block', 'DESC')
                            ->get(['applicants.*', 'blockedapplicants.blockId', 'blockedapplicants.reason', 'blockedapplicants.date_time_block']);
                            return response()->json($data);
                        }
                    // ALL BLOCKED APPLICANTS DATA

                    // VIEW REASON CANCELLED OPERATION
                        public function cancelOperationReason(Request $request){
                            $reason = canceloperation::select('reason')->where('cancelOperation_id', '=', $request->cancelOperationId)->first();
                            return response()->json($reason);
                        }
                    // VIEW REASON CANCELLED OPERATION

                    // VIEW REASON BLOCKED WORKERS
                        public function blockedReason(Request $request){
                            $reason = blockedApplicants::select('reason')->where('blockId', '=', $request->blockedReasonId)->first();
                            return response()->json($reason);
                        }
                    // VIEW REASON BLOCKED WORKERS
                // FETCH

        // ARCHIVE

        // SHOW FORMED GROUP
            public function adminFormedGroup(Request $request){
                $data = operations::where([['is_completed', '=', 0],[ 'is_archived' , '=' ,0]])->orderBy('operationStart')->get();
                if($data->isNotEmpty()){
                    foreach($data as $certainData){
                        $startDate = date('F d, Y | D',strtotime($certainData->operationStart));
                        $startTime = date('h:i: A ',strtotime($certainData->operationStart));
                        $endDate = date('F d, Y | D',strtotime($certainData->operationEnd));
                        $endTime = date('h:i: A ',strtotime($certainData->operationEnd));
                        echo "
                        <div class='card mb-3 shadow round'>
                            <div class='row g-0'>
                            <div class='col-md-3'>
                                <img loading='lazy' src='$certainData->photos' class='card-img-top img-fluid img-thumdnail' style='height: 100%; width:100%;'>
                            </div>
                            <div class='col-md-3'>
                                <div class='card-body'>
                                    <ul class='list-group list-group-flush'>
                                        <li class='list-group-item fw-bold'>Ship Name:<a class='fw-normal text-dark' style='text-decoration:none;'> $certainData->shipName</a></li>
                                        <li class='list-group-item fw-bold'>Ship Load:<a class='fw-normal text-dark' style='text-decoration:none;'> $certainData->shipCarry</a></li>
                                        <li class='list-group-item fw-bold'>Slot:<a class='fw-normal text-dark' style='text-decoration:none;'> $certainData->slot out of $certainData->totalWorkers Workers</a></li>
                                        <li class='list-group-item fw-bold text-success'>Operation Start: </br>
                                            <a class='nav-link text-dark'>Date: <span class='fw-normal'> $startDate</br></a>
                                            <a class='nav-link text-dark'>Time: <span class='fw-normal'>$startTime</a>
                                        </li>
                                        <li class='list-group-item fw-bold text-danger'>Operation End: </br>
                                            <a class='nav-link text-dark'>Date: <span class='fw-normal'>$endDate</span></br></a>
                                            <a class='nav-link text-dark'>Time: <span class='fw-normal'>$endTime</span></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class='col-md-6'>
                        ";
                        $applicantData = applied::join('operations', 'applied.operation_id', '=', 'operations.certainOperation_id')
                        ->join('applicants', 'applied.applicants_id', '=', 'applicants.applicant_id')
                        ->join('employees', 'applied.recruiter', '=', 'employees.employee_id')
                        ->where([['applied.operation_id', '=', $certainData->certainOperation_id],['operations.certainOperation_id', '=', $certainData->certainOperation_id],
                        ['applied.is_recruited', '!=' , 0]])
                        ->select('applicants.applicant_id', 'applicants.lastname AS applicantLastName', 'applicants.firstname AS applicantFirstname',
                        'applicants.extention AS applicantExtention', 'applicants.phoneNumber','employees.lastname AS employeeLastName',
                        'employees.firstname AS employeeFirstName','employees.extention AS employeeExtension' )
                        ->get();
                        if($applicantData->isNotEmpty()){
                            echo"
                            <div class='card-body' style='height:300px; overflow-y:auto;'>
                            <div class='row'>
                                <div class='col-6'>
                                    <h5 class='card-title text-start'>Project Workers Joined</h5>
                                </div>
                                <div class='col-6 text-end align-middle'>
                                    <a href='printAttendance/$certainData->certainOperation_id' class='btn rounded-0 btn-outline-secondary btn-sm'>Export to PDF</a>
                                </div>
                            </div>
                                <table class='table table-bordered text-center align-middle'>
                                    <thead>
                                        <tr>
                                            <th scope='col'>No.</th>
                                            <th scope='col'>Project Workers</th>
                                            <th scope='col'>Phone Number</th>
                                            <th scope='col'>Accept By</th>
                                            <th scope='col'>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                            foreach($applicantData as $count => $certainApplicantData){
                                $count = $count +1;
                                echo"
                                    <tr>
                                        <td>$count
                                            <input type='hidden' readonly value='$certainData->certainOperation_id' id='operationId' name='operationId'>
                                        </td>
                                        <td>$certainApplicantData->applicantFirstname $certainApplicantData->applicantLastName $certainApplicantData->applicantExtention</td>
                                        <td>$certainApplicantData->phoneNumber</td>
                                        <td>$certainApplicantData->employeeFirstName $certainApplicantData->employeeLastName $certainApplicantData->employeeExtension</td>
                                        <td><button type='button' onclick='viewApplicants($certainApplicantData->applicant_id)' class='btn rounded-0 btn-outline-secondary btn-sm'>View</button></td>
                                        </tr>
                                        ";
                                    }
                                    echo "
                                    </tbody>
                                    </table>
                                    ";
                        }else{
                            echo "<h5 class='text-center text-dark' style='margin-top:9rem;'>NO PROJECT WORKERS FOUND<br></h5>
                            ";
                        }echo "</div></div></div></div> ";
                    }
                }else{
                    echo "<h5 class='fs-5 text-center' style='color:#800000; margin-top:16.5rem;'>NO SCHEDULED FOUND</h5>";
                }
            }
        // SHOW FORMED GROUP
    }
    // ADMIN OPERATION FUNCTION
