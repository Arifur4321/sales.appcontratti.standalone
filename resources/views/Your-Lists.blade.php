@extends('layouts.master')
@section('title')
    @lang('translation.Variable-List')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Projects
        @endslot
        @slot('title')
        @lang('translation.Sales List')
        @endslot
    @endcomponent

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> 
    <link rel="stylesheet" href="//cdn.datatables.net/2.0.2/css/dataTables.dataTables.min.css">
    <script src="//cdn.datatables.net/2.0.2/js/dataTables.min.js"></script>

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    
    <div class="row">
        <div class="col-sm">
            <div class="search-box me-2 d-inline-block" style="margin-left:8px;">
                <div class="position-relative">
                    <input type="text" class="form-control" autocomplete="off" id="searchInput" placeholder="Search...">
                    <i class="bx bx-search-alt search-icon"></i>
                </div>
            </div>
        </div>

        <div class="col-sm-auto" style="margin-right:8px;">
            <div class="text-sm-end">
                <button type="button" class="btn btn-primary" onclick="openModalNew()">  @lang('translation.Add New Sales')</button>
            </div>
        </div>
    </div>
 
    
    <div class="table-responsive" style="margin-top:10px;">
    <table id="ContractList" class="table">
        <thead>
            <tr>
                    <th style="text-align: left;">ID</th>
                    <th style="text-align: left;" class="sales-column">@lang('translation.Sales')</th>
                    <th style="text-align: left;">@lang('translation.PDF Name')</th>
                    <th style="text-align: left;">@lang('translation.Contract Name')</th>
                    <th style="text-align: left;">@lang('translation.Recipient Email')</th>
                    <th style="text-align: left;">@lang('translation.Status')</th>
                    <th style="text-align: left; width: 18%;">@lang('translation.Action')</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesListDraft as $item)
                <tr>
                    <td style="text-align: left;">{{ $item->id }}</td>
                    <td style="text-align: left;">{{ Auth::user()->name }}</td>
                    <td style="text-align: left;">{{ $item->selected_pdf_name }}</td>
                    <td style="text-align: left;">{{ $item->contract_name }}</td>
                
                    <td style="text-align: left;">{{ $item->recipient_email}}</td>

                    <td style="text-align: left;" class="
                        @if($item->status == 'pending') 
                            text-danger
                        @elseif($item->status == 'viewed') 
                            text-warning
                        @elseif($item->status == 'signed') 
                            text-success
                        @else 
                            text-secondary
                        @endif">
                        {{ $item->status }}
                    </td>
                                        
                    <td style="text-align: left;">
                        <div class="btn-toolbar">
                            @if($item->status == 'signed')
                                <button onclick="openSignedPDF('{{ $item->id }}')" class="btn btn-success">PDF</button>
                            @elseif($item->status == 'pending')
                                  <button class="btn btn-primary" onclick="EditSalesContract('{{ $item->id }}')">@lang('translation.Edit')</button>
                            @else
                                <button class="btn btn-primary" onclick="EditSalesContract('{{ $item->id }}')">@lang('translation.Edit')</button>
                                <button type="button" style="margin-left:2px;" onclick="DeleteSalesContract('{{ $item->id }}')" class="btn btn-danger waves-effect waves-light">
                                    <i class="bx bx-block font-size-16 align-middle me-2"></i> @lang('translation.Delete')
                                </button>
                            @endif
                        </div>
                    </td>
                    
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    // function openSignedPDF(id) {
    //     $.ajax({
    //         url: '/contract/get-signed-pdf-url/' + id,
    //         method: 'GET',
    //         success: function(response) {
    //             if (response.success) {
    //                 window.open(response.file_url, '_blank');
    //             } else {
    //                 alert(response.message);
    //             }
    //         },
    //         error: function() {
    //             alert('Error occurred while trying to retrieve the signed PDF URL.');
    //         }
    //     });
    // }

    function openSignedPDF(id) {
        window.location.href = '/contract/download-signed-pdf/' + id;
    }


</script>


    <style>
        
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 10px;
    }

    .dataTables_wrapper .dataTables_length {
        margin: 8px;
        margin-left: 8px;
    }

    .float-start {
        float: left !important;
    }

    .float-end {
        float: right !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        display: inline-block;
        padding: 6px 12px;
        margin-left: 2px;
        margin-right: 2px;
        border: 1px solid #ddd;
        border-radius: 4px;
        color: #333;
        background-color: #fff;
        text-decoration: none;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background-color: #eee;
        border-color: #ddd;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        color: #ddd;
    }
    </style>

    <script>
    $(document).ready(function() {


/*
        window.onload = function() {
            checkSignatureStatus();
        };

        function checkSignatureStatus() {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: '/check-signature-status',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(response) {
                    console.log('Signature status check completed');
                    // Since we're not receiving specific statuses, we don't need to process the response further
                },
                error: function(xhr, status, error) {
                    console.error('Error checking signature status:', error);
                }
            });
        }

       
        setInterval(function() {
            checkSignatureStatus();
        }, 300000); 
*/

        
        let table = new DataTable('#ContractList', {
            pagingType: 'full_numbers',
            dom: '<"top"f>rt<"bottom"<"float-start"l><"float-end"p>><"clear">',
            language: {
                paginate: {
                    first: '<<',
                    last: '>>',
                    next: '@lang('translation.NEXT')',
                    previous: '@lang('translation.PREVIOUS')'
                },
                lengthMenu: "@lang('translation.SHOW_ENTRIES', ['entries' => '_MENU_'])"
            }
        });

        $('.dt-search').hide();
        $('.dataTables_info').addClass('right-info');

        $('#searchInput').on('keyup', function() {
            table.search($(this).val()).draw();
        });
    });

    function DeleteSalesContract(id) {

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to delete this record?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/sales-list-draft/' + id,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            'Record deleted successfully.',
                            'success'
                        ).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire(
                            'Error!',
                            'There was an error deleting the record.',
                            'error'
                        );
                        console.error('Error deleting record:', error);
                    }
                });
            }
        });
    }

    function EditSalesContract(id) {
        window.location.href = "/Edit-New-Contracts/" + id;
    }

    function openModalNew() {
        $.ajax({
            url: '/create-new-entry',
            method: 'POST',
            data: {_token: '{{ csrf_token() }}'},
            success: function(response) {
              //  window.location.href = "/Send-New-Contracts";
              var newEntryId = response.entry.id;

            // Redirect to the new URL with the id
            window.location.href = "/Send-New-Contracts/" + newEntryId;
            
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert('An error occurred while creating a new entry.');
            }
        });
    }




    </script>

 
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
    #spinner-overlay {
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
    }

    #spinner {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        display: flex;
        align-items: center;
        justify-content: center;
        width: 120px;
        height: 120px;
    }

    .ring {
        border: 8px solid transparent;
        border-radius: 50%;
        position: absolute;
        animation: spin 1.5s linear infinite;
    }

    .ring:nth-child(1) {
        width: 120px;
        height: 120px;
        border-top: 8px solid #3498db;
        animation-delay: -0.45s;
    }

    .ring:nth-child(2) {
        width: 100px;
        height: 100px;
        border-right: 8px solid #f39c12;
        animation-delay: -0.3s;
    }

    .ring:nth-child(3) {
        width: 80px;
        height: 80px;
        border-bottom: 8px solid #e74c3c;
        animation-delay: -0.15s;
    }

    .ring:nth-child(4) {
        width: 60px;
        height: 60px;
        border-left: 8px solid #9b59b6;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<!-- Spinner Overlay -->
<div id="spinner-overlay">
    <div id="spinner">
        <div class="ring"></div>
        <div class="ring"></div>
        <div class="ring"></div>
        <div class="ring"></div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const spinnerOverlay = document.getElementById("spinner-overlay");

        // Show the spinner when the page is loading
        spinnerOverlay.style.display = "block";

        window.addEventListener("load", function() {
            // Hide the spinner when the page has fully loaded
            spinnerOverlay.style.display = "none";
        });

        document.addEventListener("ajaxStart", function() {
            // Show the spinner when an AJAX request starts
            spinnerOverlay.style.display = "block";
        });

        document.addEventListener("ajaxStop", function() {
            // Hide the spinner when the AJAX request completes
            spinnerOverlay.style.display = "none";
        });
    });
</script>



@endsection



