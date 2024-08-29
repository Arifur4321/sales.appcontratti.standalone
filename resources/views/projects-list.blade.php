@extends('layouts.master')

@section('title')
    @lang('translation.Projects_List')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Projects
        @endslot
        @slot('title')
            Projects List
        @endslot
    @endcomponent
    <meta name="csrf-token" content="{{ csrf_token() }}">

      <!--  Arifur change  -->
      <div class="col-sm">
    <div class="search-box me-2 d-inline-block">
        <div class="position-relative">
            <input type="text" class="form-control" autocomplete="off" id="searchTableList" placeholder="Search...">
            <i class="bx bx-search-alt search-icon"></i>
        </div>
    </div>
</div>


<!-- Your existing button -->
<div class="col-sm-auto">
    <div class="text-sm-end">
        <button type="button" class="btn btn-primary" onclick="openModal()">Add New Project</button>
    </div>
</div>




<!-- Modal -->
<div class="modal" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">New Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="projectForm">
                    <div class="mb-3">
                        <label for="project-name" class="col-form-label">Project:</label>
                        <input type="text" class="form-control" id="project-name">
                    </div>
                    <div class="mb-3">
                        <label for="due-date" class="col-form-label">Due Date:</label>
                        <input type="date" class="form-control" id="due-date">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="col-form-label">Status:</label>
                        <select class="form-select" id="status">
                            <option value="in-progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="team" class="col-form-label">Team:</label>
                        <input type="text" class="form-control" id="team">
                    </div>
                    <div class="mb-3">
                        <label for="action" class="col-form-label">Action:</label>
                        <input type="text" class="form-control" id="action">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveProject()">Save Project</button>
            </div>
        </div>
    </div>
</div>
<!-- for close arifur -->
<script>
    function openModal() {
        // Using Bootstrap's JavaScript to open the modal
        var myModal = new bootstrap.Modal(document.getElementById('exampleModal'));
        myModal.show();
    }
</script>

<!-- arifur for search -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function () {
        // Reference to the input field and the table
        var $searchInput = $('#searchTableList');
        var $table = $('#projectTable');

        // Event listener for keyup on the search input
        $searchInput.on('keyup', function () {
            var searchText = $(this).val().toLowerCase();

            // Filter the table rows based on the search text
            $table.find('tbody tr').filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(searchText) > -1);
            });
        });
    });
</script>

<!-- arifur for adding row in table-->

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    function openModal() {
        // Using Bootstrap's JavaScript to open the modal
        var myModal = new bootstrap.Modal(document.getElementById('exampleModal'));
        myModal.show();
    }

    function saveProject() {
    // Get form data
    var projectName = $('#project-name').val();
    var dueDate = $('#due-date').val();
    var status = $('#status').val();
    var team = $('#team').val();
    var action = $('#action').val();

    // Get the CSRF token from the meta tag
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Send data to the server using AJAX
    $.ajax({
        url: '/save-project',
        type: 'POST',
        data: {
            projectName: projectName,
            dueDate: dueDate,
            status: status,
            team: team,
            action: action
        },
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        success: function (response) {
            // Handle success
            console.log('Data saved successfully.');
            // Optionally, close the modal or perform other actions.
        },
        error: function (error) {
            // Handle error
            console.error('Error saving data:', error);
        }
    });



        // Create a new row for the table
        var newRow = '<tr>' +
            '<td><img src="{{ URL::asset('build/images/companies/img-5.png') }}" alt="" class="avatar-sm"></td>' +
            '<td>' + projectName + '</td>' +
            '<td>' + dueDate + '</td>' +
            '<td>' + status + '</td>' +
            '<td>' + team + '</td>' +
            '<td>' +
                '<div class="dropdown">' +
                    '<a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">' +
                        '<i class="mdi mdi-dots-horizontal font-size-18"></i>' +
                    '</a>' +
                    '<div class="dropdown-menu dropdown-menu-end">' +
                        '<ul class="dropdown-menu dropdown-menu-end show" style="position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate3d(-31px, 27px, 0px);" data-popper-placement="bottom-end">' +
                            '<li><a href="#" class="dropdown-item edit-list">' +
                                '<i class="mdi mdi-pencil font-size-16 text-success me-1"></i> Edit</a></li>' +
                            '<li><a href="#" class="dropdown-item remove-list">' +
                                '<i class="mdi mdi-trash-can font-size-16 text-danger me-1"></i> Remove</a></li>' +
                        '</ul>' +
                    '</div>' +
                '</div>' +
            '</td>' +
            '</tr>';

        // Append the new row to the table
        $('#projectTable tbody').append(newRow);

        // Close the modal
        $('#exampleModal').modal('hide');
    }


    // for edit
    
    // Inside the edit-list event handler
$('.edit-list').on('click', function () {
    // Get the row data
    var row = $(this).closest('tr');
    var projectId = row.data('project-id');

    // Assuming you have a route named 'get-project' to fetch project data by ID
    var getProjectUrl = '/get-project/' + projectId;

    // Open a modal for editing (you can use the same modal as the 'saveProject' function)
    openEditModal(getProjectUrl, row);
});

function openEditModal(getProjectUrl, row) {
    // Use AJAX to get the current project data
    $.ajax({
        url: getProjectUrl,
        type: 'GET',
        success: function (response) {
            // Populate the modal form fields with the retrieved data
            $('#project-name').val(response.projectName);
            $('#due-date').val(response.dueDate);
            $('#status').val(response.status);
            $('#team').val(response.team);
            $('#action').val('edit');

            // Show the modal for editing
            openModal();

            // Update the modal save button click event for editing
            $('#save-edit-btn').off('click').on('click', function () {
                // Extract edited data from modal fields
                var editedData = {
                    projectName: $('#project-name').val(),
                    dueDate: $('#due-date').val(),
                    status: $('#status').val(),
                    team: $('#team').val(),
                    action: 'edit'
                };

                // Assuming you have a route named 'edit-project' with a parameter {id}
                var editUrl = '/edit-project/' + response.id;

                // Send an AJAX request to update the project data
                $.ajax({
                    url: editUrl,
                    type: 'POST',
                    data: editedData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function (updateResponse) {
                        // Handle success, e.g., update the table row with the edited data
                        row.find('td:eq(1)').text(editedData.projectName);
                        row.find('td:eq(2)').text(editedData.dueDate);
                        row.find('td:eq(3)').text(editedData.status);
                        row.find('td:eq(4)').text(editedData.team);

                        // Optionally, close the modal or perform other actions.
                        $('#exampleModal').modal('hide');
                    },
                    error: function (error) {
                        // Handle error
                        console.error('Error updating data:', error);
                    }
                });
            });
        },
        error: function (error) {
            console.error('Error loading project data:', error);
        }
    });
}

</script>






    <div class="row">
        <div class="col-lg-12">
            <div class="">
                <div class="table-responsive">

                    <table id="projectTable" class="table project-list-table table-nowrap align-middle table-borderless">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 100px">#</th>
                                <th scope="col">Projects</th>
                                <th scope="col">Due Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Team</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><img src="{{ URL::asset('build/images/companies/img-1.png') }}" alt="" class="avatar-sm"></td>
                                <td>
                                    <h5 class="text-truncate font-size-14"><a href="javascript: void(0);"
                                            class="text-dark">New admin Design</a></h5>
                                    <p class="text-muted mb-0">It will be as simple as Occidental</p>
                                </td>
                                <td>15 Oct, 19</td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td>
                                    <div class="avatar-group">
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-4.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-5.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <div class="avatar-xs">
                                                    <span
                                                        class="avatar-title rounded-circle bg-success text-white font-size-16">
                                                        A
                                                    </span>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-2.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <!--  Arifur change  -->
                                        <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="mdi mdi-dots-horizontal font-size-18"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                        <ul class="dropdown-menu dropdown-menu-end show" style="position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate3d(-31px, 27px, 0px);" data-popper-placement="bottom-end"> 
                                        <li><a href="projects-create.html" class="dropdown-item edit-list" data-edit-id="7">
                                            <i class="mdi mdi-pencil font-size-16 text-success me-1"></i> Edit</a></li> 
                                            
                                            <li>
                                <a href="#" class="dropdown-item edit-list" data-bs-toggle="modal" data-bs-target="#editProjectModal" data-edit-id="7">
                                    <i class="mdi mdi-pencil font-size-16 text-success me-1"></i> Edit
                                </a>
                            </li>

                                                    <li><a href="#removeItemModal" data-bs-toggle="modal" class="dropdown-item remove-list" data-remove-id="7">
                                                        <i class="mdi mdi-trash-can font-size-16 text-danger me-1"></i> Remove</a></li>             
                                                       </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><img src="{{ URL::asset('build/images/companies/img-2.png') }}" alt="" class="avatar-sm"></td>
                                <td>
                                    <h5 class="text-truncate font-size-14"><a href="javascript: void(0);"
                                            class="text-dark">Brand logo design</a></h5>
                                    <p class="text-muted mb-0">To achieve it would be necessary</p>
                                </td>
                                <td>22 Oct, 19</td>
                                <td><span class="badge bg-warning">Pending</span></td>
                                <td>
                                    <div class="avatar-group">
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-1.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-3.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="mdi mdi-dots-horizontal font-size-18"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                        <ul class="dropdown-menu dropdown-menu-end show" style="position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate3d(-31px, 27px, 0px);" data-popper-placement="bottom-end">                    <li><a href="projects-create.html" class="dropdown-item edit-list" data-edit-id="7"><i class="mdi mdi-pencil font-size-16 text-success me-1"></i> Edit</a></li>                    <li><a href="#removeItemModal" data-bs-toggle="modal" class="dropdown-item remove-list" data-remove-id="7"><i class="mdi mdi-trash-can font-size-16 text-danger me-1"></i> Remove</a></li>                </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><img src="{{ URL::asset('build/images/companies/img-3.png') }}" alt="" class="avatar-sm"></td>
                                <td>
                                    <h5 class="text-truncate font-size-14"><a href="javascript: void(0);"
                                            class="text-dark">New Landing Design</a></h5>
                                    <p class="text-muted mb-0">For science, music, sport, etc</p>
                                </td>
                                <td>13 Oct, 19</td>
                                <td><span class="badge bg-danger">Delay</span></td>
                                <td>
                                    <div class="avatar-group">
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-3.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-8.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-6.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="mdi mdi-dots-horizontal font-size-18"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                        <ul class="dropdown-menu dropdown-menu-end show" style="position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate3d(-31px, 27px, 0px);" data-popper-placement="bottom-end">                    <li><a href="projects-create.html" class="dropdown-item edit-list" data-edit-id="7"><i class="mdi mdi-pencil font-size-16 text-success me-1"></i> Edit</a></li>                    <li><a href="#removeItemModal" data-bs-toggle="modal" class="dropdown-item remove-list" data-remove-id="7"><i class="mdi mdi-trash-can font-size-16 text-danger me-1"></i> Remove</a></li>                </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><img src="{{ URL::asset('build/images/companies/img-4.png') }}" alt="" class="avatar-sm"></td>
                                <td>
                                    <h5 class="text-truncate font-size-14"><a href="javascript: void(0);"
                                            class="text-dark">Redesign - Landing page</a></h5>
                                    <p class="text-muted mb-0">If several languages coalesce</p>
                                </td>
                                <td>14 Oct, 19</td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td>
                                    <div class="avatar-group">
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-5.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <div class="avatar-xs">
                                                    <span
                                                        class="avatar-title rounded-circle bg-warning text-white font-size-16">
                                                        R
                                                    </span>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-2.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="mdi mdi-dots-horizontal font-size-18"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                        <ul class="dropdown-menu dropdown-menu-end show" style="position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate3d(-31px, 27px, 0px);" data-popper-placement="bottom-end">                    <li><a href="projects-create.html" class="dropdown-item edit-list" data-edit-id="7"><i class="mdi mdi-pencil font-size-16 text-success me-1"></i> Edit</a></li>                    <li><a href="#removeItemModal" data-bs-toggle="modal" class="dropdown-item remove-list" data-remove-id="7"><i class="mdi mdi-trash-can font-size-16 text-danger me-1"></i> Remove</a></li>                </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><img src="{{ URL::asset('build/images/companies/img-5.png') }}" alt="" class="avatar-sm"></td>
                                <td>
                                    <h5 class="text-truncate font-size-14"><a href="javascript: void(0);"
                                            class="text-dark">Skote Dashboard UI</a></h5>
                                    <p class="text-muted mb-0">Separate existence is a myth</p>
                                </td>
                                <td>22 Oct, 19</td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td>
                                    <div class="avatar-group">
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-4.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-1.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="mdi mdi-dots-horizontal font-size-18"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                        <ul class="dropdown-menu dropdown-menu-end show" style="position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate3d(-31px, 27px, 0px);" data-popper-placement="bottom-end">                    <li><a href="projects-create.html" class="dropdown-item edit-list" data-edit-id="7"><i class="mdi mdi-pencil font-size-16 text-success me-1"></i> Edit</a></li>                    <li><a href="#removeItemModal" data-bs-toggle="modal" class="dropdown-item remove-list" data-remove-id="7"><i class="mdi mdi-trash-can font-size-16 text-danger me-1"></i> Remove</a></li>                </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><img src="{{ URL::asset('build/images/companies/img-6.png') }}" alt="" class="avatar-sm"></td>
                                <td>
                                    <h5 class="text-truncate font-size-14"><a href="javascript: void(0);"
                                            class="text-dark">Blog Template UI</a></h5>
                                    <p class="text-muted mb-0">For science, music, sport, etc</p>
                                </td>
                                <td>24 Oct, 19</td>
                                <td><span class="badge bg-warning">Pending</span></td>
                                <td>
                                    <div class="avatar-group">
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <div class="avatar-xs">
                                                    <span
                                                        class="avatar-title rounded-circle bg-danger text-white font-size-16">
                                                        A
                                                    </span>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-2.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="mdi mdi-dots-horizontal font-size-18"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                        <ul class="dropdown-menu dropdown-menu-end show" style="position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate3d(-31px, 27px, 0px);" data-popper-placement="bottom-end">                    <li><a href="projects-create.html" class="dropdown-item edit-list" data-edit-id="7"><i class="mdi mdi-pencil font-size-16 text-success me-1"></i> Edit</a></li>                    <li><a href="#removeItemModal" data-bs-toggle="modal" class="dropdown-item remove-list" data-remove-id="7"><i class="mdi mdi-trash-can font-size-16 text-danger me-1"></i> Remove</a></li>                </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><img src="{{ URL::asset('build/images/companies/img-3.png') }}" alt="" class="avatar-sm"></td>
                                <td>
                                    <h5 class="text-truncate font-size-14"><a href="javascript: void(0);"
                                            class="text-dark">Multipurpose Landing</a></h5>
                                    <p class="text-muted mb-0">It will be as simple as Occidental</p>
                                </td>
                                <td>15 Oct, 19</td>
                                <td><span class="badge bg-danger">Delay</span></td>
                                <td>
                                    <div class="avatar-group">
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-4.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-5.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('build/images/users/avatar-2.jpg') }}" alt=""
                                                    class="rounded-circle avatar-xs">
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="mdi mdi-dots-horizontal font-size-18"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                        <ul class="dropdown-menu dropdown-menu-end show" style="position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate3d(-31px, 27px, 0px);" data-popper-placement="bottom-end">                    <li><a href="projects-create.html" class="dropdown-item edit-list" data-edit-id="7"><i class="mdi mdi-pencil font-size-16 text-success me-1"></i> Edit</a></li>                    <li><a href="#removeItemModal" data-bs-toggle="modal" class="dropdown-item remove-list" data-remove-id="7"><i class="mdi mdi-trash-can font-size-16 text-danger me-1"></i> Remove</a></li>                </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-12">
            <div class="text-center my-3">
                <a href="javascript:void(0);" class="text-success"><i
                        class="bx bx-loader bx-spin font-size-18 align-middle me-2"></i> Load more </a>
            </div>
        </div> <!-- end col-->
    </div>
    <!-- end row -->
@endsection
