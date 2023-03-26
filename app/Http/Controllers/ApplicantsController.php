<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Redirect;
use App\Models\applicants;
use App\Models\operations;
use App\Models\applied;
use App\Models\declined;
use App\Models\backout;
use App\Models\completedOperation;
use App\Models\blockedApplicants;
use Auth;    
use Session;
use Hash;

class ApplicantsController extends Controller
{
    // APPLICANT AUTHENTICATION
        // ROUTES
            public function applicantsAuthentication(){
                return view('applicantAuth');
            }    
            public function applicantSignUp(){
                return view('applicantSignUp');
            }    
            public function forgotPasswordRoutes(){
                return view('forgotPassword');
            }
        // ROUTES
    
        // FUNCTION
            protected function applicantCredentials(Request $request){
                return [
                    'emailAddress' => request()->{$this->applicantEmail()},
                    'password' => request()->applicantPassword,
                ];
            }
            protected function applicantEmail(){
                return 'applicantEmail';
            }

            // APPLICANTS LOGIN
                public function applicantLoginFunction(Request $request){
                    if(auth()->guard('applicantsModel')->attempt($this->applicantCredentials($request))){
                        if(auth()->guard('applicantsModel')->user()->is_blocked != 1){
                            if(auth()->guard('applicantsModel')->user()->is_active != 0 ){
                                if(auth()->guard('applicantsModel')->user()->is_utilized == 0){
                                    $updateUtilized = applicants::where('applicant_id', '=', auth()->guard('applicantsModel')->user()->applicant_id)
                                    ->update(['is_utilized' => '1']);
                                    if($updateUtilized){ 
                                        // SUCCESSFULLY LOGIN
                                        $request->session()->regenerate();
                                        return response()->json(1);
                                    }
                                }else{
                                    // ALREADY LOGGED IN
                                    return response()->json(3);
                                }
                            }else{
                                // INACTIVE ACCOUNT
                                return response()->json(2);
                            }
                        }else{
                            $data = blockedApplicants::where('applicantId', '=', auth()->guard('applicantsModel')->user()->applicant_id)->first('reason');
                            return response()->json($data);
                        }
                    }else{
                        // WRONG CREDENTIALS
                        return response()->json(0);
                    }
                }
            // APPLICANTS LOGIN

            // APPLICANTS SIGNUP
                public function applicantSignUpFunction(Request $request){
                    $existingEmail = applicants::select('emailAddress')->where('emailAddress','=',$request->applicantSignUpEmail)->get();
                    if($existingEmail->isNotEmpty()){
                        return response()->json(3); // CHOOSE ANOTHER EMAIL
                    }else{
                        if($request->applicantSignUpPassword != $request->applicantSignUpConfirmPassword){
                            return response()->json(2); // PASSWORD MISMATCH
                        }else{
                            $hashed = Hash::make($request->applicantSignUpPassword);
                            $applicantSignUp = applicants::create([
                                'emailAddress' => $request->applicantSignUpEmail,
                                'password' => $hashed,
                                'is_active' => 1,
                            ]);
                            if($applicantSignUp){
                                echo 1; // SUCCESSFULLY CREATE
                                exit();
                            }else{
                                echo 0; // ERROR ON BACKEND
                                exit();
                            }
                        }
                    }
                }
            // APPLICANTS SIGNUP

        // FUNCTION

        // LOGOUT FUNCTION
            public function applicantLogout(){
                $update = applicants::where('applicant_id', auth()->guard('applicantsModel')->user()->applicant_id)
                ->update(array('is_utilized' => 0));
                if($update){
                    Session::flush();
                    Auth::logout();
                    return response()->json(1);
                }
            }
        // LOGOUT FUNCTION

        // RESET PASSWORD
            public function forgotPassword(Request $request){
                $data = applicants::where('emailAddress', '=', $request->applicantEmail)->first();
                if($data != '' ){
                    return response()->json(1);
                }else{
                    return response()->json(0);
                }
            }
        // RESET PASSWORD
    // APPLICANT AUTHENTICATION

    // APPLICANT DASHBOARD
        // ROUTES
            public function applicantDashboardRoutes(){
                return view('applicants/dashboard');
            }   
        // ROUTES

        // FETCH
            // UPCOMING OPERATION
                public function totalUpcomingOperation(Request $request){
                    $data = operations::where('is_completed', '=', 0)->get();
                    $countData = $data->count();
                    return response()->json($countData != '' ? $countData : '0');
                }
            // UPCOMING OPERATION

            // TOTAL INVITATION
                public function totalInvitationOperation(Request $request){
                    $data = applied::where([
                    ['applicants_id', '=', auth()->guard('applicantsModel')->user()->applicant_id],
                    ['is_recommend', '=' ,1],['is_recruited', '!=', 1]])->get();
                    $countData = $data->count();
                    return response()->json($countData != '' ? $countData : '0');
                }
            // TOTAL INVITATION

            // TOTAL SCHEDULED
               public function totalScheduledOperation(Request $request){
                    $data = applied::where([
                    ['applicants_id', '=', auth()->guard('applicantsModel')->user()->applicant_id],
                    ['is_recruited', '=', 1]])->get();
                    $countData = $data->count();
                    return response()->json($countData != '' ? $countData : '0');
                }
            // TOTAL SCHEDULED

            // APPLICANT INVITATION
                public function applicantInvitation(Request $request){
                    $data = applied::join('operations', 'applied.operation_id', '=', 'operations.certainOperation_id')
                    ->join('employees', 'operations.foreman', '=', 'employees.employee_id')
                    ->where([['applied.applicants_id', '=', auth()->guard('applicantsModel')->user()->applicant_id],
                    ['applied.is_recommend', '=', 1],['applied.is_recruited', '!=' ,1],['applied.is_recommend', '=', 1],
                    ['applied.is_recruited', '!=', 1]])->get();
                    if($data->isNotEmpty()){
                        foreach($data as $certainData){
                            $applicant = auth()->guard('applicantsModel')->user()->firstname.' '.
                            auth()->guard('applicantsModel')->user()->lastname.' '.auth()->guard('applicantsModel')->user()->extention;
                            $position = auth()->guard('applicantsModel')->user()->position;
                            $newOperationStartDate = date('F d, Y | h:i: A',strtotime($certainData->operationStart));
                            $newOperationEndDate = date('F d, Y | h:i: A',strtotime($certainData->operationEnd));
                            echo "
                            <div class='col-12'>
                                <div class='card mb-2 shadow'>
                                    <div class='card-body'>
                                        <h5 class='card-title'>Dear $applicant,</h5>
                                        <p class='card-text mb-3'>$certainData->firstname $certainData->lastname (recruiter) are invites you to join in the operation as $position from
                                        <span class='fw-bold'>$newOperationStartDate until $newOperationEndDate</span> to manage the $certainData->shipCarry of the $certainData->shipName Cargo Ship. If you are available to work at Subic Consolidated Project Inc., please respond to our invitation to notify the recruiter. Thank you, and may God bless the workers.</p>
                                            <button onclick=acceptInvitation('$certainData->operation_id') class='btn btn-success btn-sm'>Accept</button>
                                            <button onclick=coWorkersDetails('$certainData->certainOperation_id') class='btn btn-secondary btn-sm'>Co-Workers</button>
                                            <button onclick=declineInvitation('$certainData->certainOperation_id') class='btn btn-danger btn-sm'>Decline</button>
                                    </div>
                                </div>
                            </div>
                            ";
                        }
                    }else{
                        echo "
                            <H5 class='mx-auto text-danger my-5 py-5'>NO INVITATION FOUND</H5>
                        ";
                    }
                }
            // APPLICANT INVITATION
        // FETCH
    // APPLICANT DASHBOARD

    // OPERATION DASHBOARD 
        // ROUTES
            public function upcomingOperationRoutes(){
                return view('applicants/operations');
            }    
        // ROUTES

        // FETCH
            // FETCH APPLICANT OPERATION
                public function applicantOperation(Request $request){
                    $data = operations::where([['is_completed', '=', 0],['foreman' , '!=', 0]])->with('employees', 'applicants')->get();
                    if($data->isNotEmpty()){
                        foreach($data as $item){
                            $operationStartDate = date('F d, Y',strtotime($item->operationStart));
                            $operationStartTime = date('D | h:i: A ',strtotime($item->operationStart)); 
                            $operationEndDate = date('F d, Y',strtotime($item->operationEnd));
                            $operationEndTime = date('D | h:i: A ',strtotime($item->operationEnd)); 
                            $recruiter = $item->employees->firstname.' '.$item->employees->lastname.' '.$item->employees->extention;
                            echo"
                            <div class='col-lg-6 col-sm-12 g-0 gx-lg-5 text-center text-lg-start'>
                            <div class='card mb-3 shadow border-2 border rounded' style='width:100%'>
                                <div class='row g-0'>
                                        <img loading='lazy' src='$item->photos' class='card-img-top img-thumdnail' style='height:230px; width:100%;'>
                                    <div class='col-md-12'>
                                        <ul class='list-group list-group-flush fw-bold'>      
                                            <li class='list-group-item'>
                                                <div class='row'>
                                                    <div class='col-12 col-lg-6 ps-0 ps-lg-4'>
                                                        Ship's Name: <span class='fw-normal'> $item->shipName</span>
                                                    </div>
                                                    <div class='col-12 col-lg-6 pt-2 pt-lg-0 ps-0 ps-lg-4'>
                                                        Ship's Carry:<span class='fw-normal'> $item->shipCarry</span>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class='list-group-item'>
                                                <div class='row'>
                                                    <div class='col-12 col-lg-6 ps-0 ps-lg-4'>
                                                        Foreman: <span class='fw-normal'>$recruiter</span>                                                    
                                                    </div>
                                                    <div class='col-12 col-lg-6 pt-2 pt-lg-0 ps-0 ps-lg-4'>
                                                        Available Slot:<span class='fw-normal'> $item->slot Applicants</span>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class='list-group-item fw-bold' style='color:#'>    
                                                <div class='row'>
                                                    <div class='col-12 col-lg-6 pt-2 pt-lg-0 ps-0 ps-lg-4'>
                                                        <p class='fw-bold text-success'>Operation Start:</p>
                                                        <a class='fw-bold text-dark nav-link' style='margin-top:-13px;'>Date: <span class='fw-normal'>$operationStartDate</span></a>
                                                        <a class='fw-bold text-dark nav-link'>Time: <span class='fw-normal'>$operationStartTime</span></a>
                                                    </div>
                                                    <div class='col-12 col-lg-6 pt-2 pt-lg-0 ps-0 ps-lg-4'>
                                                        <p class='fw-bold text-danger'>Operation End:</p>
                                                        <a class='fw-bold text-dark nav-link' style='margin-top:-13px;'>Date: <span class='fw-normal'>$operationEndDate</span></a>
                                                        <a class='fw-bold text-dark nav-link'>Time: <span class='fw-normal'>$operationEndTime</span></a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class='list-group-item text-center text-lg-end'>";
                                        $checkStatus = applied::where([['applicants_id', '=', auth()->guard('applicantsModel')->user()->applicant_id],
                                        ['operation_id' ,'=', $item->certainOperation_id]])->get();
                                        if(!$checkStatus->isEmpty()){
                                            foreach($checkStatus as $appliedData){
                                                if($appliedData != ''){
                                                    // CONDITIONS FOR BUTTONS
                                                        if($appliedData->is_recommend == 1 && $appliedData->is_recruited == 0){
                                                            echo"
                                                                <button onclick=acceptInvitation('$appliedData->operation_id') class='btn btn-sm btn-success px-4 py-2'>Accept</button>
                                                                <button onclick=coWorkersDetails('$item->certainOperation_id') class='btn btn-sm btn-secondary px-3 py-2'>Co-Workers</button>
                                                                <button onclick=declineInvitation('$item->certainOperation_id') class='btn btn-sm btn-danger px-4 py-2'>Decline</button>
                                                            ";
                                                        }else if($appliedData->is_recommend == 1 && $appliedData->is_recruited == 1){
                                                            echo"
                                                                <button onclick=coWorkersDetails('$item->certainOperation_id') class='btn btn-sm btn-secondary py-2'>Co-Workers</button>
                                                                <button onclick=backOutOperation('$item->certainOperation_id') class='btn btn-sm btn-danger px-3 py-2'>Back Out</button>
                                                            ";
                                                        }else if($appliedData->is_recommend == 0 && $appliedData->is_recruited == 1){
                                                            echo"
                                                                <button onclick=coWorkersDetails('$item->certainOperation_id') class='btn btn-sm btn-secondary px-3 py-2'>Co-Workers</button>
                                                                <button onclick=backOutOperation('$item->certainOperation_id') class='btn btn-sm btn-danger px-4 py-2'>Back Out</button>
                                                            ";
                                                        }else{
                                                            echo"
                                                                <button type='button' onclick=cancelApplied('$appliedData->applied_id') class='btn btn-sm btn-danger px-4 py-2'>Cancel Apply</button>
                                                            ";
                                                        }
                                                    // CONDITIONS FOR BUTTONS
                                                }else{
                                                    echo"
                                                        <button type='button' id='taraNaBtn' onclick=taraNaBtn('$item->certainOperation_id') class='btn btn-sm btn-primary px-4 py-2'>APPLY</button>
                                                    ";
                                                }                         
                                            }
                                        }else{
                                            echo"
                                                <button type='button' id='taraNaBtn' onclick=taraNaBtn('$item->certainOperation_id') class='btn btn-sm btn-primary px-4 py-2'>APPLY</button>
                                            ";
                                        }
                                        echo"</li></ul>
                                    </div>
                                </div>
                            </div>
                            </div>";
                        }
                    }else{
                        echo "
                        <div class='row applicantNoSched' style='margin-top:15rem; color: #800000;'>
                            <div class='alert alert-light text-center fs-4' role='alert' style='color: #800000;'>
                                NO OPERATION YET
                            </div>
                        </div>
                        ";
                    }
                } 
            // FETCH APPLICANT OPERATION

            // APPLY ON SPECIFIC OPERATION
                public function applicantApply(Request $request){
                    $applicantId =  auth()->guard('applicantsModel')->user()->applicant_id;
                    $operationId = $request->operationId;
                    $applicantsData = applicants::where([['applicant_id', '=', $applicantId]])->get();
                        foreach($applicantsData as $certainData){
                            if($certainData->lastname == "" && $certainData->personal_id == ""){
                                echo 2; // Please complete all of your information.
                                exit();
                            }else{
                                $operationsData = operations::where([['certainOperation_id', '=', $operationId]])->get();
                                if($operationsData->isNotEmpty()){
                                    foreach($operationsData as $certainOperationsData){
                                        // APPLYING OPERATION
                                        $applyingOperationStart = date('F d, Y | h:i:a',strtotime($certainOperationsData->operationStart));
                                        $applyingOperationEnd  = date('F d, Y | h:i:a',strtotime($certainOperationsData->operationEnd));
                                    }
                                    $applyingData = applied::join('operations', 'applied.operation_id', '=', 'operations.certainOperation_id')
                                    ->where([['applied.applicants_id', '=' ,$applicantId],['applied.is_recruited','=',1]])->get();
                                    if($applyingData->isNotEmpty()){
                                        foreach($applyingData as $certainApplyingData){
                                            // CHECK IF THEY ARE ALREADY SCHED ON SAME DATE/TIME
                                            $scheduledOperationStart = date('F d, Y | h:i:a',strtotime($certainApplyingData->operationStart));
                                            $scheduledOperationEnd = date('F d, Y | h:i:a',strtotime($certainApplyingData->operationEnd));
                                        }   
                                        if($applyingOperationStart == $scheduledOperationStart){
                                            echo 3; // NOT AVAILABLE ON THAT DAY
                                            exit();
                                        }else{
                                            $applicationApply = applied::create([
                                                'operation_id' => $operationId,
                                                'applicants_id' => $applicantId,
                                                'date_time_applied' => now(),
                                                'is_recruited' => 0,
                                                'is_recommend' => 0,
                                            ]);
                                            if($applicationApply){
                                                echo 1; // SUCCESSFULLY APPLY
                                                exit();
                                            }else{
                                                echo 0; // ERROR ON BACKEND
                                                exit();
                                            }
                                        }
                                    }else{
                                        $applicationApply = applied::create([
                                            'operation_id' => $operationId,
                                            'applicants_id' => $applicantId,
                                            'date_time_applied' => now(),
                                            'is_recruited' => 0,
                                            'is_recommend' => 0,
                                        ]);
                                        if($applicationApply){
                                            echo 1; // SUCCESSFULLY APPLY
                                            exit();
                                        }else{
                                            echo 0; // ERROR ON BACKEND
                                            exit();
                                        }
                                    }
                                }
                            }
                        }
                }
            // APPLY ON SPECIFIC OPERATION

            // CANCEL APPLIED 
                public function cancelApply(Request $request){
                    $cancelApplied = applied::where([['applied_id', '=', $request->appliedId]])->delete();
                    return response()->json($cancelApplied  ? 1 : 0);
                }
            // CANCEL APPLIED 

            // DECLINED INVITATION
                public function declinedInvitation(Request $request){
                    $reason = $request->reason;
                    $operationId = $request->operationId;
                    $applicantId = auth()->guard('applicantsModel')->user()->applicant_id;
                    $addDeclined = declined::create([
                        'operation_id' => $operationId,
                        'applicant_id' => $applicantId,
                        'reason' => $reason,
                        'date_time_declined' => now(),
                    ]);
                    if($addDeclined){
                        $cancelApplied = applied::where([['operation_id', '=', $operationId],['applicants_id', '=', $applicantId], 
                        ['is_recommend', '=', 1]])->delete();
                        if($cancelApplied){
                            $updateSlot = operations::find($operationId)->increment('slot');
                            return response()->json($updateSlot ? 1 : 0);
                        }
                    }
                }
            // DECLINED INVITATION

            // BACK OUT SCHEDULED OPERATION
                public function backOutOperation(Request $request){
                    $reason = $request->reason;
                    $operationId = $request->operationId;
                    $applicantId = auth()->guard('applicantsModel')->user()->applicant_id;
                    $addBackout = backout::create([
                        'operation_id' => $operationId,
                        'applicant_id' => $applicantId,
                        'reason' => $reason,
                        'date_time_backOut' => now(),
                    ]);
                    if($addBackout){
                        $cancelApplied = applied::where([['operation_id', '=', $operationId],['applicants_id', '=', $applicantId]])->delete();
                        if($cancelApplied){
                            $updateSlot = operations::find($operationId)->increment('slot');
                            return response()->json($updateSlot ? 1 : 0);
                        }
                    }
                }
            // BACK OUT SCHEDULED OPERATION

            // ACCEPT INVITATION
                public function acceptInvitation(Request $request){
                    $operationId = $request->operationId;
                    $applicantId = auth()->guard('applicantsModel')->user()->applicant_id;
                    $acceptInvitation = applied::where([['operation_id','=',$operationId], 
                    ['applicants_id','=',$applicantId]])->update(['is_recruited'=> 1]);
                    if($acceptInvitation){
                        $updateSlot = operations::find($operationId)->decrement('slot');
                        return response()->json($updateSlot ? 1 : 0);
                    }
                }
            // ACCEPT INVITATION

            // COWORKERS DETAILS
                public function coWorkers(Request $request){
                    $applicantId = auth()->guard('applicantsModel')->user()->applicant_id;
                    $coWorkersDetails = applied::
                    join('operations', 'applied.operation_id', '=', 'operations.certainOperation_id')
                   ->join('applicants', 'applied.applicants_id', '=', 'applicants.applicant_id')
                   ->where([['applied.operation_id', '=' ,$request->operationId],['operations.certainOperation_id','=',
                   $request->operationId],['applied.applicants_id', '!=' ,$applicantId], 
                   ['applicants.applicant_id', '!=', $applicantId],['applied.is_recruited', '=', 1]])
                   ->get(['applicants.lastname','applicants.firstname','applicants.extention','applicants.position',
                   'applicants.age']);
                   if($coWorkersDetails->isNotEmpty()){
                            echo"<table class='table text-center align-middle table-bordered'>
                            <thead>
                              <tr>
                                <th class='col-1'>#</th>
                                <th class='col-5'>Applicant</th>
                                <th class='col-2'>Age</th>
                                <th class='col-4'>Position</th>
                              </tr>
                            </thead><tbody>";
                       foreach($coWorkersDetails as $count => $applicantInfo){
                        $perWorkers = $count + 1;
                        echo "
                        <tr>
                        <td>$perWorkers</td>
                            <td>$applicantInfo->firstname $applicantInfo->lastname $applicantInfo->extention</td>
                            <td>$applicantInfo->age</td>
                            <td>$applicantInfo->position</td>
                        </tr>
                        ";}
                            echo"
                            </tbody>
                          </table>
                            ";
                   }else{
                        echo"
                            <p class='text-uppercase text-center' style='color: #800008;'>No Co-Workers Found</p>
                        ";
                   }
                }
            // COWORKERS DETAILS
        // FETCH
    // OPERATION DASHBOARD 

    // SCHEDULED DASHBOARD
        // ROUTES
            public function applicationScheduleRoutes(){
                return view('applicants/scheduled');
            }
        // ROUTES

        // FETCH    
            public function applicantScheduled(Request $request){
                $applicantScheduled = applied::
                 join('operations', 'applied.operation_id', '=', 'operations.certainOperation_id')
                ->join('employees', 'operations.foreman', '=', 'employees.employee_id')
                ->where([['applied.applicants_id', '=', auth()->guard('applicantsModel')->user()->applicant_id],
                ['is_recruited', '=', 1]])->get(['applied.*','employees.lastname','employees.firstname','employees.extention',
                'operations.*']);
                if($applicantScheduled->isNotEmpty()){
                    foreach($applicantScheduled as $certainData){
                        $operationStartDate = date('F d, Y',strtotime($certainData->operationStart));
                        $operationStartTime = date('D | h:i: A ',strtotime($certainData->operationStart)); 
                        $operationEndDate = date('F d, Y',strtotime($certainData->operationEnd));
                        $operationEndTime = date('D | h:i: A ',strtotime($certainData->operationEnd)); 
                        $recruiter = $certainData->firstname.' '.$certainData->lastname.' '.$certainData->extention;
                        echo"
                        <div class='row g-0'>
                            <div class='card col-lg-6 col-sm-12 text-center text-lg-start'>
                                <img loading='lazy' src='$certainData->photos' class='card-img-top img-thumdnail' style='height:230px; width:100%;'>
                                <ul class='list-group list-group-flush fw-bold'>      
                                    <li class='list-group-item'>
                                        <div class='row'>
                                            <div class='col-12 col-lg-6 ps-0 ps-lg-4'>
                                                Ship's Name: <span class='fw-normal'> $certainData->shipName</span>
                                            </div>
                                            <div class='col-12 col-lg-6 pt-2 pt-lg-0 ps-0 ps-lg-4'>
                                                Ship's Carry:<span class='fw-normal'> $certainData->shipCarry</span>
                                            </div>
                                        </div>
                                    </li>
                                    <li class='list-group-item'>
                                        <div class='row'>
                                            <div class='col-12 col-lg-6 ps-0 ps-lg-4'>
                                                Foreman: <span class='fw-normal'>$recruiter</span>                                                    
                                            </div>
                                            <div class='col-12 col-lg-6 pt-2 pt-lg-0 ps-0 ps-lg-4'>
                                                Total Applicants:<span class='fw-normal'> $certainData->slot</span>
                                            </div>
                                        </div>
                                    </li>
                                    <li class='list-group-item fw-bold' style='color:#'>    
                                        <div class='row'>
                                            <div class='col-12 col-lg-6 pt-2 pt-lg-0 ps-0 ps-lg-4'>
                                                <p class='fw-bold text-success'>Operation Start:</p>
                                                <a class='fw-bold text-dark nav-link' style='margin-top:-13px;'>Date: <span class='fw-normal'>$operationStartDate</span></a>
                                                <a class='fw-bold text-dark nav-link'>Time: <span class='fw-normal'>$operationStartTime</span></a>
                                            </div>
                                            <div class='col-12 col-lg-6 pt-2 pt-lg-0 ps-0 ps-lg-4'>
                                                <p class='fw-bold text-danger'>Operation End:</p>
                                                <a class='fw-bold text-dark nav-link' style='margin-top:-13px;'>Date: <span class='fw-normal'>$operationEndDate</span></a>
                                                <a class='fw-bold text-dark nav-link'>Time: <span class='fw-normal'>$operationEndTime</span></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class='list-group-item text-center text-lg-end'>
                                        <button onclick=backOutOperation('$certainData->certainOperation_id') class='btn btn-sm btn-danger px-4 py-2'>Back Out</button>
                                    </li>
                                </ul>
                            </div>
                            <div class='card col-lg-6 col-sm-12 text-center text-lg-start rounded-0'>
                            <div class='card-body' style='height:280px; overflow-y:auto;'>
                                <h5 class='card-title'>CO-WORKERS</h5>
                                <table class='table table-bordered  text-center align-middle'>
                                    <thead>
                                        <tr>
                                            <th scope='col'>#</th>
                                            <th scope='col'>Full Name</th>
                                            <th scope='col'>Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            ";
                            $coWorkers = applied::join('applicants', 'applied.applicants_id', '=', 'applicants.applicant_id')
                            ->where([['applied.operation_id', '=', $certainData->operation_id],['is_recruited', '=', 1],
                            ['applicants.applicant_id', '!=',  auth()->guard('applicantsModel')->user()->applicant_id]])
                            ->orderBy('applicants.position' )
                            ->get(['applicants.lastname','applicants.firstname','applicants.extention', 'applicants.position']);
                                foreach($coWorkers as  $count => $applicantData){
                                    $coWorker = $applicantData->firstname.' '.$applicantData->lastname.' '.$applicantData->extention;
                                    $count = $count +1;
                                    echo"
                                        <tr>
                                            <td class='fw-bold'>$count</td>
                                            <td>$coWorker</td>
                                            <td>$applicantData->position</td>
                                        </tr>
                                    ";
                                }
                            echo"
                            </tbody>              
                            </table>
                            </div>   
                            </div>   
                            </div>";
                    }
                }else{
                    echo "
                    <div class='row applicantNoSched' style='padding-top:15rem; color: #800000;'>
                        <div class='alert alert-light text-center fs-4' role='alert' style='color: #800000;'>
                            NO SCHEDULED YET
                        </div>
                    </div>
                    ";
                }
            }
        // FETCH
    // SCHEDULED DASHBOARD

    // COMPLETED DASHBOARD
        // ROUTES
            public function applicantCompletedRoutes(){
                return view('applicants/completed');
            } 
        // ROUTES

        // FETCH
            public function applicantCompletedOperation(Request $request){
                $data = completedOperation::join('operations', 'completedOperation.operation_id', '=', 'operations.certainOperation_id')
                ->join('employees', 'employees.employee_id', '=', 'completedOperation.recruiter_id')
                ->where([['completedOperation.applicant_id', '=', auth()->guard('applicantsModel')->user()->applicant_id]])
                ->orderBy('completedOperation.completed_id')->get(['operations.*', 'employees.lastname', 'employees.firstname', 'employees.extention']);
                return response()->json($data);
            }
        // FETCH
    // COMPLETED DASHBOARD

    // MANAGE ACCOUNT
        // ROUTES
            public function applicantAccountRoutes(){
                return view('applicants/account');
            }   

            public function applicantCredentialsRoutes(){
                return view('applicants/applicantCredentials');
            }   
        // ROUTES 

        // FETCH DATA
            // APPLICANT PERSONAL INFO
                public function getApplicantData(Request $request){  
                    $data = applicants::where([['applicant_id', '=', auth()->guard('applicantsModel')->user()->applicant_id]])->get();
                    return response()->json($data);
                }  
            // APPLICANT PERSONAL INFO

            // EDIT ACCOUNT
                public function editApplicantInfo(Request $request){ 
                    $data = applicants::select('emailAddress')->where('applicant_id', $request->appId)->get();
                    foreach($data as $certainData){
                        $certainData->emailAddress;
                    }
                    if($certainData->emailAddress = $request->emailAddress){
                        return response()->json(2); // EMAIL ADDRESS ALREADY EXIST
                    }else{
                        if ($request->hasFile('appPhotos')) {
                                $filename = $request->file('appPhotos');
                                $imageName =   time().rand() . '.' .  $filename->getClientOriginalExtension();
                                $path = $request->file('appPhotos')->storeAs('applicants', $imageName, 'public');
                                $imageData['appPhotos'] = '/storage/'.$path;
                                // EDIT ONE
                                    $update = applicants::find($request->appId);
                                    $update->photos=$imageData['appPhotos'];
                                    $update->lastname=$request->input('appLastName');
                                    $update->firstname=$request->input('appFirstName');
                                    $update->middlename=$request->input('appMiddleName');
                                    $update->extention = $request->input('appExtention');
                                    $update->Gender=$request->input('appGender');
                                    $update->status=$request->input('appStatus');
                                    $update->position=$request->input('appPosition');
                                    $update->age=$request->input('appAge');
                                    $update->birthday=$request->input('appBirthday');
                                    $update->nationality=$request->input('appNationality');
                                    $update->religion=$request->input('appReligion');
                                    $update->address=$request->input('appAddress');
                                    $update->phoneNumber=$request->input('appPhoneNumber');
                                    $update->emailAddress=$request->input('appEmail');
                                    $update->phoneNumber=$request->input('appPhoneNumber');
                                    $update->save();
                                    if($update){
                                        return response()->json(1);
                                    }
                                // EDIT ONE
                        }else{
                                // EDIT TWO
                                    $update = applicants::find($request->appId);
                                    $update->lastname=$request->input('appLastName');
                                    $update->firstname=$request->input('appFirstName');
                                    $update->middlename=$request->input('appMiddleName');
                                    $update->extention = $request->input('appExtention');
                                    $update->Gender=$request->input('appGender');
                                    $update->status=$request->input('appStatus');
                                    $update->position=$request->input('appPosition');
                                    $update->age=$request->input('appAge');
                                    $update->birthday=$request->input('appBirthday');
                                    $update->nationality=$request->input('appNationality');
                                    $update->religion=$request->input('appReligion');
                                    $update->address=$request->input('appAddress');
                                    $update->phoneNumber=$request->input('appPhoneNumber');
                                    $update->emailAddress=$request->input('appEmail');
                                    $update->phoneNumber=$request->input('appPhoneNumber');
                                    $update->save();
                                    if($update){
                                        return response()->json(1);
                                    }                                
                                // EDIT TWO
                        }
                    }                    
                }
            // EDIT ACCOUNT

            // SUBMIT APPLICANT ID
                public function submitApplicantId(Request $request){ 
                    $applicantId = auth()->guard('applicantsModel')->user()->applicant_id;
                    if(!$request->hasFile('updatePersonalId2') && $request->hasFile('updatePersonalId')){   
                        // CODE FOR PERSONAL ID 1 
                                $filename = $request->file('updatePersonalId'); 
                                $imageName =   time().rand() . '.' .  $filename->getClientOriginalExtension();
                                $path = $request->file('updatePersonalId')->storeAs('applicant_id', $imageName, 'public');
                                $imageData1['updatePersonalId'] = '/storage/'.$path;
        
                                $update = applicants::find($applicantId);
                                $update->personal_id=$imageData1['updatePersonalId'];
                                $update->save();
                                return response()->json(1);
                        // CODE FOR PERSONAL ID 1 
                    }elseif(!$request->hasFile('updatePersonalId') && $request->hasFile('updatePersonalId2')){
                        // CODE FOR PERSONAL ID 2
                                $filename2 = $request->file('updatePersonalId2'); 
                                $imageName2 =   time().rand() . '.' .  $filename2->getClientOriginalExtension();
                                $path2 = $request->file('updatePersonalId2')->storeAs('applicant_id', $imageName2, 'public');
                                $imageData2['updatePersonalId2'] = '/storage/'.$path2;
                                $update = applicants::find($applicantId);
                                $update->personal_id2=$imageData2['updatePersonalId2'];
                                $update->save();
                                    return response()->json(1);
                        // CODE FOR PERSONAL ID 2
                    }else{
                        // CODE FOR BOTH ID
                            // APPLICANT ID
                                $filename = $request->file('updatePersonalId'); 
                                $imageName =   time().rand() . '.' .  $filename->getClientOriginalExtension();
                                $path = $request->file('updatePersonalId')->storeAs('applicant_id', $imageName, 'public');
                                $imageData1['updatePersonalId'] = '/storage/'.$path;
                            // APPLICANT ID
                                
                            // APPLICANT ID 2
                                $filename2 = $request->file('updatePersonalId2'); 
                                $imageName2 =   time().rand() . '.' .  $filename2->getClientOriginalExtension();
                                $path2 = $request->file('updatePersonalId2')->storeAs('applicant_id', $imageName2, 'public');
                                $imageData2['updatePersonalId2'] = '/storage/'.$path2;
                            // APPLICANT ID 2
                            $update = applicants::find($applicantId);
                            $update->personal_id=$imageData1['updatePersonalId'];
                            $update->personal_id2=$imageData2['updatePersonalId2'];
                            $update->save();
                            if($update){
                                return response()->json(1);
                            }
                        // CODE FOR BOTH ID
                    }
                }
            // SUBMIT APPLICANT ID

                    // UPDATE PASSWORD
                        public function updateUsersPassword(Request $request){
                            $passwordVerify = applicants::select('password')->where('applicant_id', '=',  auth()->guard('applicantsModel')->user()->applicant_id)->first();
                            if(!Hash::check($request->currentPassword, $passwordVerify->password)){
                                return response()->json(0);
                            }else{
                                $update = applicants::find(auth()->guard('applicantsModel')->user()->applicant_id);
                                $update->password = Hash::make($request->input('confirmPassword'));
                                $update->save();
                                return response()->json(1);
                            }         
                        }
                    // UPDATE PASSWORD
        // FETCH DATA
    // MANAGE ACCOUNT
}