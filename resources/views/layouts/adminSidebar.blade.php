<div class="border-end bg-white" id="sidebar-wrapper">
    <div class="sidebar-heading border-bottom pt-5 text-center">
        <img class="scpiLogo" src="/assets/frontend/scpi.webp">
        <p class="portal pt-4">ADMINISTRATOR PORTAL</p>
    </div>
    <div class="list-group list-group-flush recruiterLink">
        <a class="list-group-item list-group-item-action list-group-item-light" href="/adminDashboardRoutes"><i class="bi bi-bar-chart-line-fill pe-3"></i> Dashboard</a>
        <a class="list-group-item list-group-item-action list-group-item-light" href="/adminOperationRoutes"><i class="bi bi-box pe-3"></i> Operation</a>
        <a class="list-group-item list-group-item-action list-group-item-light" href="/adminScheduleRoutes"><i class="bi bi-calendar-check pe-3"></i> Group</a>
        <a class="list-group-item list-group-item-action list-group-item-light" href="/adminEmployeesRoutes"><i class="bi bi-person-workspace pe-3"></i> Manpower Pooling</a>
        <a class="list-group-item list-group-item-action list-group-item-light" href="/adminOldApplicantsRoutes"><i class="bi bi-people-fill pe-3"></i> Project Workers</a>
        <a class="list-group-item list-group-item-action list-group-item-light" href="/adminApplicantsRoutes"><i class="bi bi-people-fill pe-3"></i> Applicants</a>
        <a class="list-group-item list-group-item-action list-group-item-light" href="/adminBackOutArchiveRoutes"><i class="bi bi-archive pe-3"></i> Archive</a>
        <a class="list-group-item list-group-item-action list-group-item-light" href="/adminManageAccount"><i class="bi bi-pencil-square pe-3"></i> Manage Account</a>
    </div>
    <div class="sidebar-footing border-top pt-4 text-center">
        <p class="text-center" id="dateDisplay"></p>
        <p class="text-center" id="clockDisplay"></p>

        <button type="button" id="logout" class="btn btn-sm" data-title="Logout?">
            <i class="bi bi-box-arrow-left fs-4"></i>
        </button>
    </div>
</div>
