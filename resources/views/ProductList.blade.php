@extends('layouts.master')
@section('title')
    @lang('translation.Product-List')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Projects
        @endslot
        @slot('title')
        Product List 
        @endslot
    @endcomponent
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> 
    <link ref="stylesheet" href="//cdn.datatables.net/2.0.2/css/dataTables.dataTables.min.css">   </link>
    <script src="//cdn.datatables.net/2.0.2/js/dataTables.min.js"  ></script>


     <!--  Arifur change  -->
     <div class="row"  id="firstid">
    <div class="col-sm">
        <div class="search-box me-2 d-inline-block">
            <div class="position-relative">
                <input type="text" class="form-control" autocomplete="off" id="searchInput" placeholder="Search...">
                <i class="bx bx-search-alt search-icon"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-auto">
        <div class="text-sm-end">
            <button type="button" class="btn btn-primary" onclick="openModalNew()">Add New Product</button>
        </div>
    </div>
</div>


<!-- product list Modal -->
<div class="modal" id="exampleModalNew" tabindex="-1" aria-labelledby="exampleModalLabelNew" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabelNew">New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="productFormNew">
              
                    <div class="mb-3">
                        <label for="product-name-new" class="col-form-label">Product Name:</label>
                        <input type="text" class="form-control" id="product-name-new">
                    </div>
                    <div class="mb-3">
                        <label for="description-new" class="col-form-label">Description:</label>
                        <textarea class="form-control" id="description-new"></textarea>
                    </div>
               
                     
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveProduct()">Save Product</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Edit Variable</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editVariableForm">
          <input type="hidden" id="variable-id">
          <div class="mb-3">
            <label for="variable-name" class="form-label">Name</label>
            <input type="text" class="form-control" id="variable-name" required>
          </div>
     
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="editVariable()">Save changes</button>
      </div>
    </div>
  </div>
</div>


<script>
function saveProduct() {
    // Get form data
    var productName = $('#product-name-new').val();
    var description = $('#description-new').val();

    // Basic validation
    if (!productName || !description   ) {
        console.error('All fields must be filled out.');
        return;
    }

    // Get the CSRF token from the meta tag
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Send data to the server using AJAX
    $.ajax({
        url: '/save-product',
        type: 'POST',
        data: {
            productName: productName,
            description: description,
        },
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        success: function (response) {
            // Handle success
            console.log('Data saved successfully.');
            // Optionally, close the modal or perform other actions.
            location.reload();
        },
        error: function (error) {
            // Handle error
            console.error('Error saving data:', error);
        }
    });

    $('#exampleModalNew').modal('hide');
}


</script>

<!-- arifur for search -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function () {
        // Reference to the input field and the table
        var $searchInput = $('#searchInput');
        var $table = $('#ContractList');

        // Event listener for keyup on the search input
        $searchInput.on('keyup', function () {
            var searchText = $(this).val().toLowerCase();

            // Filter the table rows based on the search text
            $table.find('tbody tr').filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(searchText) > -1);
            });
        });
    });


    function openModalNew() {
        // Using Bootstrap's JavaScript to open the product list modal
        var myModal = new bootstrap.Modal(document.getElementById('exampleModalNew'));
        myModal.show();
    }

    $('#exampleModalNew').modal('hide');


    function openModal(variableID, variableName,description) {
    // Using Bootstrap's JavaScript to open the modal
            var myModal = new bootstrap.Modal(document.getElementById('exampleModal'));
            myModal.show();

            // Set values in the modal form
            document.getElementById('variable-id').value = variableID;
            document.getElementById('variable-name').value = variableName;
            
            document.getElementById('description').value = description;
        }

        // AJAX method to handle form submission for editing
        function editVariable() {
            var variableID = document.getElementById('variable-id').value;
            var variableName = document.getElementById('variable-name').value;
           
            var description = document.getElementById('description').value;

            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            $.ajax({
                url: '/edit-variable/' + variableID,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: {
                    product_name: variableName,
               
                    description: description
                },
                success: function(response) {
                    // Handle success response
                    console.log(response);
                    location.reload();
                },
                error: function(error) {
                    // Handle error response
                    console.error('Error editing variable:', error.responseText);
                }
            });
        }


</script>

<script>
    function redirectTocreatecontract() {
        // Redirect to the route associated with createcontract.blade.php
        window.location.href = "/createcontract"; // Replace with your actual route path
    }
</script>

<!-- Modal -->
<div class="modal" id="exampleModalNew" tabindex="-1" aria-labelledby="exampleModalLabelNew" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
             
        <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">CKEditor</h4>
                    <p class="card-title-desc">CKEditor is a powerful WYSIWYG text editor.</p>

                    <form id="contractFormmodal" method="post">
                        <div class="mb-3">
                            <label for="title" class="form-label">Contract Name</label>
                            <input type="text" class="form-control" id="titlemodal" name="titlemodal">
                        </div>
                        <textarea id="editormodal" name="editormodal"></textarea>
                    </form>
                </div>
            </div>
        </div> <!-- end col -->
    </div> <!-- end row -->

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveDatamodal()">Save Project</button>
            </div>
        </div>
    </div>
</div>

    <!-- For Main table -->
    <div class="table-responsive">
    <table id="ContractList" class="table">
    <thead>
        <tr>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Description</th>
            <th>Created Date</th>
            <th>updated Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $contract)
            <tr>
                <td>{{ $contract->id }}</td>
                <td>{{ $contract->product_name }}</td>
                <td>{{ $contract->description }}</td>
                <td>{{ $contract->created_at }}</td>
                <td>{{ $contract->updated_at }}</td>
                <td>
    <div class="dropdown">
        <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="mdi mdi-dots-horizontal font-size-18"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <ul class="dropdown-menu dropdown-menu-end show" style="position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate3d(-31px, 27px, 0px);" data-popper-placement="bottom-end">
                <!-- Edit Button -->
                <li>
                    <a href="#" class="dropdown-item edit-list"  
                    onclick="openModal('{{  $contract->id}}', '{{ $contract->product_name }}',
                     '{{ $contract->description }}')">
                        <i class="mdi mdi-pencil font-size-16 text-success me-1"></i> Edit
                    </a>
                </li>
                <!-- History Button 
                <li>
                    <a href="" class="dropdown-item">
                        <i class="mdi mdi-history font-size-16 text-info me-1"></i> History
                    </a>
                </li>-->
                <!-- Delete Button -->
                <li>
                <form id="deleteForm-{{  $contract->id}}" action="{{ route('product.delete',  $contract->id) }}" method="POST">
                    @csrf
                    @method('POST')
                    <a href="#" class="dropdown-item edit-list" onclick="confirmDelete('{{  $contract->id}}');">
                        <i class="mdi mdi-trash-can font-size-16 text-danger me-1"></i> Delete
                    </a>
                    <!-- JavaScript for confirmation popup -->
                    <script>
                        function confirmDelete(contractId) {
                            // Display confirmation popup
                            if (confirm('Are you sure you want to delete this contract?')) {
                                // If user clicks 'Yes', submit the form
                                document.getElementById('deleteForm-' + contractId).submit();
                            } else {
                                // If user clicks 'No', do nothing
                                return false;
                            }
                        }
                    </script>
                </form>
                </li>
            </ul>
        </div>
    </div>
</td>
</tr>

<!-- Modal for Editing Contract -->
        <div class="modal fade" id="editContractModal{{ $contract->id }}" tabindex="-1" role="dialog" aria-labelledby="editContractModalLabel{{ $contract->id }}" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editContractModalLabel{{ $contract->id }}">Edit Contract</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- CKEditor for editing contract content -->
                        <textarea id="contractContent{{ $contract->id }}" name="contractContent">{{ $contract->editor_content }}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="saveEditedContent('{{ $contract->id }}')">Save</button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </tbody>
</table>
</div>

<span>
 
</span>

<style>
    .w-5{
        display:none;
    }
</style>

<!-- For pagination  -->
<script>
//    let table = new DataTable('#ContractList');
$(document).ready(function() {
        let table = new DataTable('#ContractList');

        let lengthMenu = $('.dt-length');
        lengthMenu.appendTo($('#ContractList').parent().parent().parent().parent().parent().find('tfoot'));

       // let lengthMenu = $('.dt-length');
       // lengthMenu.addClass('smaller-length-menu').appendTo($('#ContractList').parent().parent().parent().parent().parent().find('tfoot'));

        // Hide additional search box
        $('.dt-search').hide();
        $('.dt-info').addClass('right-info');


        // Bind DataTable search to custom search box
        $('#searchInput').on('keyup', function() {
            table.search($(this).val()).draw();
        });
    });
 </script>

@section('script')
    <!-- CKEditor script -->
    <script src="https://cdn.ckeditor.com/ckeditor5/35.0.0/classic/ckeditor.js"></script>

    <script>

        function openModalNew() {
                // Using Bootstrap's JavaScript to open the modal
                var myModal = new bootstrap.Modal(document.getElementById('exampleModalNew'));
                myModal.show();
            }
        
        function saveContent(title, content) {
            // Send title and content to Laravel backend using AJAX
            $.ajax({
                url: '/save',
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    title: title,
                    content: content
                },
                success: function(response) {
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }


        function saveEditedContent() {
            var editedContent = CKEDITOR.instances.contractContent.getData();

            // Send the edited content to the server using AJAX
            // You can use the saveContent function you provided earlier
            saveContent(editedContent);

            // Close the modal
            $('#editContractModal').modal('hide');
        }


        // Function to save data when Save button is clicked
        function saveData() {
            const title = document.querySelector('#title').value; // Get title
            const content = editor.getData(); // Get content from CKEditor
            saveContent(title, content); // Call saveContent function to save data
        }

        function saveDatamodal() {
            const title = document.querySelector('#titlemodal').value; // Get title
            const content = editormodal.getData(); // Get content from CKEditor
           // saveContent(title, content); // Call saveContent function to save data
        }

    </script>
@endsection
@endsection
