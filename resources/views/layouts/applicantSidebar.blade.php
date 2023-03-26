<div class="border-end bg-white" id="sidebar-wrapper">
    <div class="sidebar-heading border-bottom pt-5 text-center">
        <img class="scpiLogo" src="/assets/frontend/scpi.webp">
        <p class="portal pt-4">APPLICANT PORTAL</p>
    </div>
    <div class="list-group list-group-flush recruiterLink">
        <a class="list-group-item recruiterA list-group-item-action list-group-item-light p-3" href="/applicantDashboardRoutes"><i class="fa-solid fa-chart-line pe-3"></i> Details</a>
        <a class="list-group-item recruiterA list-group-item-action list-group-item-light p-3" href="/upcomingOperationRoutes"><i class="fa-solid fa-ship pe-3"></i> Upcoming Operation</a>
        <a class="list-group-item recruiterA list-group-item-action list-group-item-light p-3" href="/applicationScheduleRoutes"><i class="fa-regular fa-calendar pe-3"></i> Scheduled Operation</a>
        <a class="list-group-item recruiterA list-group-item-action list-group-item-light p-3" href="/applicantCompletedRoutes"><i class="fa-solid fa-circle-check pe-3"></i> Completed Operation</a>
        <a class="list-group-item recruiterA list-group-item-action list-group-item-light p-3" href="/applicantAccountRoutes"><i class="bi bi-pencil-square pe-3"></i> Manage Account</a>
    </div>
    <div class="sidebar-footing border-top pt-3 text-center">
        <p class="text-center" id="dateDisplay"></p>
        <p class="text-center" id="clockDisplay"></p>

        <button type="button" id="applicantslogout" class="btn btn-sm py-2" data-title="Logout?">
            <i class="bi bi-box-arrow-left fs-4"></i>
        </button>
    </div>
</div>