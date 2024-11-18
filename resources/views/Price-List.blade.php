
@extends('layouts.master')
@section('title')
    @lang('translation.arifurtable')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Projects
        @endslot
        @slot('title')
            Price List 
        @endslot
    @endcomponent
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> 
    <link ref="stylesheet" href="//cdn.datatables.net/2.0.2/css/dataTables.dataTables.min.css">   </link>
    <script src="//cdn.datatables.net/2.0.2/js/dataTables.min.js"  ></script>

     <!--  Arifur change  -->
     <div class="row">
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
                    <button type="button" class="btn btn-primary" onclick="redirectToEditPrice()">Add New Price</button>
                </div>
            </div>
    </div>


<script>

    function redirectToEditPrice() {
        // Redirect to the Add-New-Price page
        window.location.href = 'createpricewithupdate';
    }

    function redirectToNewPricePage() {
        // Redirect to the Add-New-Price page
        window.location.href = 'Add-New-Price';
    }
</script>

<!-- Table content -->
<div class="table-responsive">
<table id="PriceList" class="table">
    <!-- Table header -->
    <thead>
        <tr>
            <th>Price ID</th>
            <th>Price Name</th>
            <th>User Name</th>
            <th>Created Date</th>
            <th>Updated Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <!-- Table body  -->
    <tbody>
        @foreach ($priceLists as $price)
        <tr>
            <td>{{ $price->id }}</td>
            <td>{{ $price->pricename }}</td>
            <td>{{ Auth::user()->name }}</td>
            <td>{{ $price->created_at }}</td>
            <td>{{ $price->updated_at }}</td>
            <td>
                <!-- Dropdown menu for actions -->
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="mdi mdi-dots-horizontal font-size-18"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- Edit action -->
                        <a href="#" class="dropdown-item edit-list" onclick="editPrice({{ $price->id }})">
                            <i class="mdi mdi-pencil font-size-16 text-success me-1"></i> Edit
                        </a>
                        <!-- Delete action -->
                        <a href="#" class="dropdown-item delete-list" onclick="deletePrice({{ $price->id }})">
                            <i class="mdi mdi-delete font-size-16 text-danger me-1"></i> Delete
                        </a>
                    </div>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>
<script>
//    let table = new DataTable('#ContractList');
$(document).ready(function() {
        let table = new DataTable('#PriceList');

        let lengthMenu = $('.dt-length');
        lengthMenu.appendTo($('#PriceList').parent().parent().parent().parent().parent().find('tfoot'));

        // Hide additional search box
        $('.dt-search').hide();
        $('.dt-info').addClass('right-info');
        // Bind DataTable search to custom search box
        $('#searchInput').on('keyup', function() {
            table.search($(this).val()).draw();
        });
    });
 </script>


<script>
    function editPrice(id) {
        window.location.href = "/edit-price/" + id;
    }

function deletePrice(id) {
    if (confirm('Are you sure you want to delete this price list?')) {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: '/price-lists/' + id,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                // Remove the row from the table upon successful deletion
                // $('#PriceList tr[data-id="' + id + '"]').remove();
                alert('Data deleted successfully from the price List!');
                window.location.href = "Price-List";
            },
            error: function(xhr, status, error) {
                console.error(error);
                alert('Error occurred while deleting the price list.');
            }
        });
    }
}
</script>

@endsection
