$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    recruiterFormedGroup();
});

// SHOW TOTAL OPERATIONS
    function recruiterFormedGroup(){
        $.ajax({
            url: "/recruiterFormedGroup",
            method: 'GET',
            success : function(data) {
                $("#showFormedGroup").html(data);
            }
        })
    }
// SHOW TOTAL OPERATIONS

// SHOW CERTAIN APPLICANTS DETAILS
    function viewApplicants(id){
    $('#viewApplicantsDetails').modal('show')
    $.ajax({
        url: '/getCertainApplicants',
        type: 'GET',
        dataType: 'json',
        data: {applicantId: id},
    })
    .done(function(response) {
        function applicantExperienceSoya(){
            $.ajax({
                url: "/applicantExperienceSoya",
                method: 'GET',
                data: {applicantId:response.applicant_id},
                success : function(data) {
                    if(data != ''){
                        $("#soyaExp").html("<span class='text-success'>"+data+" Total</span>");
                    }else{
                        $("#soyaExp").html("<span class='text-danger'>No Experience</span>");
                    }
                }
            })
        }
        function applicantExperienceCable(){
            $.ajax({
                url: "/applicantExperienceCable",
                method: 'GET',
                data: {applicantId:response.applicant_id},
                success : function(data) {
                    if(data != ''){
                        $("#cableExp").html("<span class='text-success'>"+data+" Total</span>");
                    }else{
                        $("#cableExp").html("<span class='text-danger'>No Experience</span>");
                    }
                }
            })
        }
        function applicantExperienceRice(){
            $.ajax({
                url: "/applicantExperienceRice",
                method: 'GET',
                data: {applicantId:response.applicant_id},
                success : function(data) {
                    if(data != ''){
                        $("#riceExp").html("<span class='text-success'>"+data+" Total</span>");
                    }else{
                        $("#riceExp").html("<span class='text-danger'>No Experience</span>");
                    }
                }
            })
        }
        function applicantExperienceWood(){
            $.ajax({
                url: "/applicantExperienceWood",
                method: 'GET',
                data: {applicantId:response.applicant_id},
                success : function(data) {
                    if(data != ''){
                        $("#woodExp").html("<span class='text-success'>"+data+" Total</span>");
                    }else{
                        $("#woodExp").html("<span class='text-danger'>No Experience</span>");
                    }
                }
            })
        }
        function applicantExperiencePlyWood(){
            $.ajax({
                url: "/applicantExperiencePlyWood",
                method: 'GET',
                data: {applicantId:response.applicant_id},
                success : function(data) {
                    if(data != ''){
                        $("#plyWoodExp").html("<span class='text-success'>"+data+" Total</span>");
                    }else{
                        $("#plyWoodExp").html("<span class='text-danger'>No Experience</span>");
                    }
                }
            })
        }
        applicantExperiencePlyWood();
        applicantExperienceWood();
        applicantExperienceRice();
        applicantExperienceSoya();
        applicantExperienceCable();
        $('#applicantsPhoto').attr("src", response.photos)
        $('#applicantsLastname').html(response.lastname)           
        $('#applicantsFirstname').html(response.firstname)           
        $('#applicantsMiddlename').html(response.middlename)           
        $('#applicantsExt').html(response.extention)           
        $('#applicantsStatus').html(response.status)           
        $('#applicantsPosition').html(response.position)
        $('#applicantsGender').html(response.Gender)
        $('#applicantsAge').html(response.age)           
        $('#applicantsAddress').html(response.address)           
        $('#applicantsPnumber').html(response.phoneNumber)           
        $('#applicantsEmail').html(response.emailAddress)   
        if(response.personal_id != '' && response.personal_id2 != ''){
            $('#personalId').attr("src", response.personal_id)
            $('#personalId2').attr("src", response.personal_id2)
        }else{
            $('#personalId').attr("src","/storage/applicant_id/noId.jpg")
            $('#personalId2').attr("src","/storage/applicant_id/noId.jpg")
        }        
        let dtFormat = new Intl.DateTimeFormat('en-Us',{
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
        var newDate = new Date(response[0].birthday);
        $('#applicantsBirthday').html(dtFormat.format(newDate));    
    })    
    }
// SHOW CERTAIN APPLICANTS DETAILS

// FUNCTION
    // SUBMIT ATTENDANCE
        $("body").delegate("#operationCompleteBtn","click",function(e){
            applicantId = [];
            $(':checkbox:checked').each(function(applicant){
                applicantId[applicant] = $(this).val();
            });
            if(applicantId.length === 0){
                Swal.fire(
                'CANNOT SUBMIT',
                'Check the checkbox of an applicant who attend',
                'error'
                )
            }else{
                var operationId = $('#operationId').val().trim();
                Swal.fire({
                    icon: 'question',
                    title: 'Are you sure?',
                    text: "Do you want to COMPLETE this operation?",
                    input: 'text',
                    inputPlaceholder: 'Enter your password to confirm',
                    inputAttributes: {autocapitalize: 'off'},
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    }).then((response) => {
                        if(response.value === ""){
                            Swal.fire(
                                'Cancel Failed',
                                'Please Enter Your Password',
                                'error'
                            )
                        }else{
                            $.ajax({
                                url: '/confirmationPassword',
                                type: 'GET',
                                dataType: 'text',
                                data: {employeePassword: response.value},
                                success:function(response2){
                                    if(response2 == 1){
                                        $.ajax({
                                            url: '/submitApplicantAttendance',
                                            method: 'POST',
                                            dataType: 'text',
                                            data:{operationId:operationId,applicantId:applicantId},
                                            success: function(response3) {
                                                if(response3 == 1){
                                                    Swal.fire({
                                                        title: 'OPERATION WAS COMPLETE SUCCESSFULLY',
                                                        icon: 'success',
                                                        showConfirmButton: false,
                                                        timer: 1500,
                                                    }).then((result) => {
                                                    if (result) {
                                                        recruiterFormedGroup();
                                                    }
                                                    });
                                                }else if(response3 == 2){
                                                    Swal.fire(
                                                        'SUBMIT FAILED',
                                                        'The operation was not already done',
                                                        'error'
                                                    )
                                                }else if(response3 == 3){
                                                    Swal.fire(
                                                        'SUBMIT FAILED',
                                                        'Operation still operate, please wait until done',
                                                        'error'
                                                    )
                                                }
                                            }
                                        });                                
                                    }else if(response2 == 0){
                                            Swal.fire(
                                            'Wrong Password',
                                            'Please re-type your password',
                                            'error'
                                            )
                                    }
                                }
                            });
                        }
                });
            }
        });
    // SUBMIT ATTENDANCE
// FUNCTION