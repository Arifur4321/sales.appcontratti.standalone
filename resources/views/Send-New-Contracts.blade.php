@extends('layouts.master')
@section('title')
@lang('translation.Sales List')
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
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/css/bootstrap3/bootstrap-switch.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.7.0/nouislider.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.7.0/nouislider.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2kEbXSYl43nvUVCRIu-twGWbVCKdl-qo&libraries=places"></script>




<div class="row">
    <div class="col-7">
    @if (Auth::check())
        <h6>@lang('translation.Seller Name') : {{ Auth::user()->name }}</h6> <br>
    @endif
        <div class="mb-3">
            <div class="input-group">
                <label class="input-group-text" for="Product">@lang('translation.Product') :</label>
                <select class="form-select" id="frequency" name="frequency">
                    <option value="" selected>Select Product</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row" id="contractRow" style="display:none;">
    <div class="col-7">
        <div class="mb-3">
            <div class="input-group">
                <label class="input-group-text" for="Contract"> @lang('translation.Contract') :</label>
                <select class="form-select" id="Contract" name="Contract">
                    <option value="" selected>Select Contract</option>
                </select>
            </div>
        </div>
    </div>
</div>
 
<!-- pop up modal for auto fill  -->
 
        <div class="row" style="display: none;">
            <div class="col-7" id="variableContainer" style="overflow-y: scroll;">
                <div class="table-responsive">
                    <table id="sales-variable" class="table">
                        <thead>
                            <tr>
                                
                                <th style="width:50% ; margin-left:-550px"> @lang('translation.Variable Name') </th>
                                <th style="width:60%"> 

                                    @lang('translation.Variable Label Value') 
                                    <button type="button" class="btn btn-info btn-sm ml-2" id="checkButton"> 

                                        @lang('translation.check') </button>
                                
                                    </th>
                            </tr>

                        </thead>
                        
                        <tbody>
                            <tr>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-3">
                    <div class="col-12" id="ImpostaTable1"></div>
                </div>

                <div class="row mt-3">
                    <div class="col-6"></div>
                    <div class="col-6 text-right">
                        <button type="button" class="btn btn-primary" id="updateButton">@lang('translation.Update')</button>
                        <button type="button" class="btn btn-primary ml-2" id="mytestButton">@lang('translation.Preview&Send') </button>
                    </div>
                </div>
            </div>
        </div>

    <!-- Modal for displaying the table and view buttons -->
 
        <div class="modal fade" id="checkModal" tabindex="-1" aria-labelledby="checkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkModalLabel">Check Variables</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-4"> <!-- Adjust this value for the desired width -->
                            <input type="text" id="searchBox" class="form-control" placeholder="Search...">
                        </div>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th> Variable </th>
                                <th>Product Name</th>
                                <th>Recipient Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="modalTableBody">
                            <!-- Rows will be dynamically inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for displaying the variableJson data -->
<div class="modal fade" id="jsonModal" tabindex="-1" aria-labelledby="jsonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jsonModalLabel">Variable Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="variableJsonContent" style="white-space: pre-wrap;"></div>
                <div class="text-right mt-3">
                    <button type="button" class="btn btn-secondary" id="insertDataButton">Insert</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $('#checkButton').on('click', function() {
        $.ajax({
            url: '{{ route("sales.data") }}',
            type: 'GET',
            success: function(data) {
                console.log('The check JSON data:', data);
                populateTable(data);
                $('#checkModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error('Error fetching sales data:', error);
            }
        });
    });

    function populateTable(data) {

        var modalTableBody = $('#modalTableBody');
        modalTableBody.empty();

        data.forEach(function(item) {
            var variableJson = item.variable_json;
            var jsonString = '';
            var variableValues = [];

            var desiredTextValues = [];

            // Check if variableJson is a string and try to parse it
            if (typeof variableJson === 'string') {
                try {
                    variableJson = JSON.parse(variableJson);
                } catch (e) {
                    console.error('Error parsing variable_json string:', e);
                    variableJson = null; // Set to null if parsing fails
                }
            }
 
          if (Array.isArray(variableJson)) {
                    variableJson.forEach(function(variable) {
                        if (variable.value && (variable.type === 'Single Line Text' || variable.type === 'Multiple Line Text')) {
                            desiredTextValues.push(`${variable.name} : ${variable.value}`);
                        }
                    });
                }
        
                // Limit to first two desired text values
                if (desiredTextValues.length > 2) {
                    desiredTextValues = desiredTextValues.slice(0, 2);
                }
        
                var variableDisplay = desiredTextValues.join(', ');


            // Convert the JSON object or array to a string for storage in the data-json attribute
            if (Array.isArray(variableJson) && variableJson.length > 0 && variableJson[0].name && variableJson[0].type) {
                jsonString = JSON.stringify(variableJson, null, 4);
            }

            // Escape any potential HTML or special characters in jsonString
            var sanitizedJsonString = $('<div>').text(jsonString).html();

            var row = `<tr>
                <td>${item.id}</td>
                <td>${variableDisplay}</td>
                <td>${item.product_name}</td>
                <td>${item.recipient_email}</td>
                <td>
                    <button type="button" class="btn btn-primary view-btn" data-json='${sanitizedJsonString}'>View</button>
                </td>
            </tr>`;
            modalTableBody.append(row);
        });

        // Add event listener for search functionality
        $('#searchBox').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $("#modalTableBody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    }

    // Handle "View" button click
    $(document).on('click', '.view-btn', function() {
        var jsonData = $(this).data('json');

        try {
            if (typeof jsonData === 'string' && jsonData !== '') {
                jsonData = JSON.parse(jsonData);
            }
        } catch (e) {
            console.error('Error parsing JSON data:', e);
            jsonData = [];
        }

        // Check if jsonData is an array and has the desired structure
        if (Array.isArray(jsonData) && jsonData.length > 0 && jsonData[0].name && jsonData[0].type) {
            var tableContent = '<table class="table table-bordered"><thead><tr><th>Variable Name</th><th>Variable Type</th><th>Variable Value</th></tr></thead><tbody>';
            jsonData.forEach(function(variable) {
                if (['Single Line Text', 'Dates', 'Multiple Line Text'].includes(variable.type)) {
                    tableContent += `<tr>
                        <td>${variable.name}</td>
                        <td>${variable.type}</td>
                        <td>${variable.value}</td>
                    </tr>`;
                }
            });
            tableContent += '</tbody></table>';
            $('#variableJsonContent').html(tableContent);
        } else {
            $('#variableJsonContent').text('No valid data to display.');
        }

        $('#jsonModal').modal('show');
    });

    // Handle "Insert" button click
    $('#insertDataButton').on('click', function() {
        console.log('Insert button clicked');
        var jsonData = $('#variableJsonContent table tbody tr').map(function() {
            return {
                name: $(this).find('td').eq(0).text().trim(),
                type: $(this).find('td').eq(1).text().trim(),
                value: $(this).find('td').eq(2).text().trim()
            };
        }).get();

        // Insert data into the appropriate fields in the `sales-variable` table
        $('#sales-variable tbody tr').each(function() {
            var $row = $(this);
            var variableName = $row.find('td').eq(0).text().trim();
            var variableType = $row.data('variable-type');

            // Find the matching variable in jsonData
            var matchingVariable = jsonData.find(function(variable) {
                return variable.name === variableName && ['Single Line Text', 'Dates', 'Multiple Line Text'].includes(variable.type);
            });

            if (matchingVariable) {
                // Update the field with the value
                switch (variableType) {
                    case 'Dates':
                        $row.find('input[type="date"]').val(matchingVariable.value);
                        break;
                    case 'Single Line Text':
                    case 'Multiple Line Text':
                        $row.find('input[type="text"]').val(matchingVariable.value);
                        break;
                }
            }
        });

        $('#jsonModal').modal('hide');
    });
});
</script>


    <style>
        #variableJsonContent {
            max-height: 400px;
            overflow-y: auto;
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
    </style>

<div id="liveToast" class="toast fade hide" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 1050;">
    <div class="toast-header">
        <img src="" alt="" class="me-2" height="18">
        <strong class="me-auto">Information</strong>
        <small>Few mins ago</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
        Hello, world! This is a toast message.
    </div>
</div>


<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Preview PDF</h5>
            </div>
            <div class="col-8"><br>
                <h6>Receiver/Customer Info</h6><br>
                <form id="recipientForm" onsubmit="return false;">
                    <div class="col-10">
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="email-addon">Email</span>
                            <input type="email" class="form-control" id="recipientEmail" placeholder="Enter email" aria-label="Email" aria-describedby="email-addon" required>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="mobile-addon">Mobile Number :</span>
                            <input type="tel" class="form-control" id="recipientMobile" placeholder="Enter mobile number" aria-label="Mobile Number">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-body" style="height: 60vh; overflow-y: auto;">
               
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeModalBtn" data-bs-dismiss="modal"> @lang('translation.Close')     </button>
                <button type="button" class="btn btn-primary" id="sendButton" data-bs-dismiss="modal" disabled> @lang('translation.Send')   </button>
            </div>
        </div>
    </div>
</div> 
 



<style>
    .noUi-tooltip {
        display: block !important;
        background: none !important;
        border: none !important;
        color: black !important;
        font-size: 12px !important;
        padding: 0 !important;
        position: absolute;
        top: -25px !important;
        left: 50%;
        transform: translateX(-50%);
    }

    .invalid-field {
        border: 2px solid red;
    }

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

<div id="spinner-overlay">
    <div id="spinner">
        <div class="ring"></div>
        <div class="ring"></div>
        <div class="ring"></div>
        <div class="ring"></div>
    </div>
</div>

<script>
  //-------------
    
    var variableMandatoryStatus = {}; // Initialize as an object

    document.addEventListener("DOMContentLoaded", function() {
        const spinnerOverlay = document.getElementById("spinner-overlay");

        spinnerOverlay.style.display = "block";

        window.addEventListener("load", function() {
            spinnerOverlay.style.display = "none";
        });

        document.addEventListener("ajaxStart", function() {
            spinnerOverlay.style.display = "block";
        });

        document.addEventListener("ajaxStop", function() {
            spinnerOverlay.style.display = "none";
        });
    });

    function showToast(message) {
        var toast = $('#liveToast');
        toast.find('.toast-body').text(message);
        toast.removeClass('hide').addClass('show');
        setTimeout(function() {
            toast.removeClass('show').addClass('hide');
        }, 5000);
    }

    $(document).ready(function() {

        var input = document.querySelector("#recipientMobile");

      
        
        var iti = window.intlTelInput(input, {
            initialCountry: "it",
            preferredCountries: ["us", "gb", "ca", "au"],
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
        });

        var initialCountryData = iti.getSelectedCountryData();
        input.value = "+" + initialCountryData.dialCode;

        input.addEventListener("countrychange", function() {
            var countryCode = iti.getSelectedCountryData().dialCode;
            input.value = "+" + countryCode;
        });

        setInterval(function() {
            checkSignatureStatus();
        }, 600000);

        var id = window.location.pathname.split('/').pop();


        function checkEmailValidity() {
            var emailInput = $('#recipientEmail');
            var sendButton = $('#sendButton');
            if (emailInput[0].checkValidity()) {
                sendButton.prop('disabled', false);
            } else {
                sendButton.prop('disabled', true);
            }
        }

        $('#recipientEmail').on('input', function() {
            checkEmailValidity();
        });

        checkEmailValidity();

        let signatureCheckInterval;

        $('#sendButton').on('click', function() {
            var pdfUrl = $('.modal-body embed').attr('src');
            var recipientEmail = $('#recipientEmail').val();
            var recipientMobile = $('#recipientMobile').val();
            var recipientName = "Recipient";

            sendDocumentForSignature(pdfUrl, recipientEmail, recipientName, recipientMobile);
        });


        function sendDocumentForSignature(pdfUrl, recipientEmail, recipientName, recipientMobile) {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            var id = window.location.pathname.split('/').pop(); // Extract ID from the URL
            var selectedContract = $('#Contract').val();
            var selectedProduct = $('#frequency').val();

            console.log('Sending document for signature...');

            $.ajax({
                url: '/send-document-for-signature',
                type: 'POST',
                data: {
                    pdfUrl: pdfUrl,
                    recipientEmail: recipientEmail,
                    recipientName: recipientName,
                    recipientMobile: recipientMobile,
                    id: id,
                    selectedContractId: selectedContract,
                    selectedProduct: selectedProduct
                },
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(response) {
                    if (response.error) {
                        Swal.fire({
                            title: 'Error!',
                            text: response.error,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        let emailStatus = response.email_status || 'Email sent successfully.';
                        let closeIoStatus = response.close_io_status || 'Note added successfully to Close.io.';

                        Swal.fire({
                            title: 'Success!',
                            html: `<p>${emailStatus}</p><p>${closeIoStatus}</p>`,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Start checking the signature status periodically after the alert is closed
                            startPeriodicStatusCheck(response.envelope_id);
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error sending document for signature:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while sending the document. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }


        function startPeriodicStatusCheck(envelopeId) {
            if (signatureCheckInterval) {
                clearInterval(signatureCheckInterval);
            }
        }

        function checkSignatureStatus() {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: '/check-signature-status',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(response) {
                    if (response.status === 'signed' || response.status === 'declined') {
                        clearInterval(signatureCheckInterval);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error checking signature status:', error);
                }
            });
        }

        $('#mytestButton').on('click', function() {
            if (validateTableFields()) {
                var selectedContract = $('#Contract').val();
                getTheContractmytest(selectedContract);
                update();
            }

            
        });

    function getTheContractmytest(selectedContract) {

    if (selectedContract) {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var variableValues = collectVariableValues();
        var priceValues = collectPriceValues();
        var id = window.location.pathname.split('/').pop();
        console.log('variableValues ****////*----------------------just now>', variableValues);
        console.log('collectPriceValues ****////*----------------------just now>', priceValues);
        $('#spinner-overlay').show();
        $.ajax({
            url: '/get-pdf-sales',
            type: 'POST',
            data: {
                selectedContractId: selectedContract,
                variableValues: variableValues,
                priceValues: priceValues,
                id: id
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
               // var pdfUrl = response.pdf_url;

                var   pdfUrl = '/serve-pdf/' + response.pdf_url.split('/').pop(); // Point to the new route for serving the PDF

                console.log('new my pdfUrl:', pdfUrl);
                $('.modal-body').html('<embed src="' + pdfUrl + '" type="application/pdf" style="width:100%; height:100%;">');
                $('#myModal').modal('show');

                $('#closeModalBtn').on('click', function() {
                    $.ajax({
                        url: '/delete-pdf',
                        type: 'POST',
                        data: {
                            pdfUrl: pdfUrl
                        },
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            console.log('PDF deleted successfully');
                        },
                        error: function(xhr, status, error) {
                            console.error('Error deleting PDF:', error);
                        }
                    });
                });

                $('#spinner-overlay').hide();
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: `
                        <p>${xhr.responseJSON.error}</p>
                        <p><strong>Total Check:</strong> ${xhr.responseJSON.totalCheck}</p>
                        <p><strong>Expected Total:</strong> ${xhr.responseJSON.expectedTotal}</p>
                    `,
                    footer: 'Please try again later or contact support if the issue persists.'
                });

                $('#spinner-overlay').hide();
            }
        });
    }
}




function validateTableFields() {
    let allValid = true;
    let errorMessage = '';

    var id = window.location.pathname.split('/').pop();

    // Step 1: Make an AJAX call to get mandatory status
    $.ajax({
        url: '/get-contract-variable-status/' + id,
        method: 'GET',
        async: false,
        success: function(response) {
            if (response.error) {
                console.error(response.error);
                return;
            }

            variableMandatoryStatus = {};

            variableMandatoryStatus = response.variableStatuses; // Set the mandatory status data globally
            console.log('AJAX Response variableMandatoryStatus:', variableMandatoryStatus); // Log to verify
        },

         

        error: function(xhr, status, error) {
            console.error('Error fetching contract variable statuses:', error);
        }
    });

    // Step 2: Validate each row based on mandatory status
    $('#sales-variable tbody tr').each(function() {
        let row = $(this);
        let variableID = parseInt(row.data('variable-id'), 10); // Convert variableID to integer
        let isMandatory = !!variableMandatoryStatus[variableID]; // Check if mandatory

        row.find('.invalid-field').removeClass('invalid-field');

        row.find('input[type="text"], input[type="date"]').each(function() {
            if (isMandatory && $(this).val().trim() === '') {
                allValid = false;
                $(this).addClass('invalid-field');
                errorMessage = 'Please fill out all mandatory fields.';
            }
        });

        let radioGroups = new Set();
        row.find('input[type="radio"]').each(function() {
            radioGroups.add($(this).attr('name'));
        });

        radioGroups.forEach(function(group) {
            if (isMandatory && $('input[name="' + group + '"]:checked').length === 0) {
                allValid = false;
                $('input[name="' + group + '"]').addClass('invalid-field');
                errorMessage = 'Please select at least one option in each mandatory radio button group.';
            }
        });

        if (isMandatory && row.find('input[type="checkbox"]').length > 0 && row.find('input[type="checkbox"]:checked').length === 0) {
            allValid = false;
            row.find('input[type="checkbox"]').addClass('invalid-field');
            errorMessage = 'Please select at least one checkbox in each mandatory group.';
        }
    });

    if (!allValid) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: errorMessage
        });
    }

    console.log('arifur variableMandatoryStatus[variableID] 2nd time>',   variableMandatoryStatus); // Debugging log
    return allValid;
}

 
        $('#Contract').on('change', function() {  
            var selectedContract = $(this).val();
            if (selectedContract) {
                $.ajax({
                    url: '/get-all-variables',
                    type: 'GET',
                    data: {
                        selectedContractId: selectedContract
                    },
                    success: function(response) {
                        var variableData = response;
                        $.ajax({
                            url: '/save-variable-data',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                variableData: JSON.stringify(variableData)
                            },
                            success: function(result) {
                              
                                console.log('Data saved successfully:', result.message);
                            },
                            error: function(xhr, status, error) {
                                console.error('Error saving data:', error);
                            }
                        });

                        var variableTable = $('#sales-variable');
                        variableTable.find('tbody').empty();

                        variableData.variableData.sort(function(a, b) {
                            if (a.order === null) return 1;
                            if (b.order === null) return -1;
                            return a.order - b.order;
                        });


 

    // Step 1: Make an AJAX call to get mandatory status
    $.ajax({
        url: '/get-contract-variable-status/' + id,
        method: 'GET',
        async: false,
        success: function(response) {
            if (response.error) {
                console.error(response.error);
                return;
            }

            variableMandatoryStatus = {};

            variableMandatoryStatus = response.variableStatuses; // Set the mandatory status data globally
            console.log('AJAX Response variableMandatoryStatus:', variableMandatoryStatus); // Log to verify
        },

         

        error: function(xhr, status, error) {
            console.error('Error fetching contract variable statuses:', error);
        }
    });


// Construct Table Rows Based on variableData and Apply Mandatory Status
$.each(variableData.variableData, function(index, variable) {
    console.log('Processing variable:', variable); // Debugging log for each variable
    var tableRow = $('<tr></tr>').attr('data-variable-type', variable.VariableType).attr('data-variable-id', variable.VariableID);
    var container, icon, inputField;

    function createIcon() {
        return $('<button type="button" class="btn btn-info btn-sm ml-2"><i class="fas fa-info-circle"></i></button>').click(function() {
            showToast(variable.Description);
        });
    }

    function stripHtml(html) {
        var temporaryDiv = document.createElement("div");
        temporaryDiv.innerHTML = html;
        return temporaryDiv.textContent || temporaryDiv.innerText || "";
    }

    tableRow.append('<td>' + variable.VariableName + '</td>');

    var labelValueCell = $('<td></td>');
    var variableID = parseInt(variable.VariableID, 10); // Convert to integer


    console.log('arifur variableMandatoryStatus[variableID] >',   variableMandatoryStatus[variableID] ); // Debugging log

    var isMandatory = !!variableMandatoryStatus[variableID]; // Get mandatory status as a boolean
   
    console.log('VariableID:', variableID, 'isMandatory:', isMandatory); // Debugging log

    var mandatorySign = isMandatory ? $('<span class="text-danger mr-1">*</span>') : null; // Create red asterisk for mandatory fields

    if (mandatorySign) console.log('Mandatory field detected:', variable.VariableName); // Debugging log to confirm

    //-------------------------------------------------------------------------------------
    switch (variable.VariableType) {
        case 'Dates':
            container = $('<div class="d-flex align-items-center w-100 mb-2"></div>');
            var defaultDate = variable.defaultValue ? variable.defaultValue : new Date().toISOString().slice(0, 10);

            inputField = $('<input type="date" class="form-control flex-grow-1 mr-2" id="variableDatpicker" value="' + defaultDate + '">');
            icon = createIcon();
            
            container.append(inputField );
            if (mandatorySign) container.append(mandatorySign); // Add mandatory sign if applicable
            container.append( icon);

            labelValueCell.append(container);

            inputField.on('change', function() {
                updateVariableData(variableID, $(this).val(), 'Dates');
            });
            break;

        case 'Single Line Text':
            container = $('<div class="d-flex align-items-center w-100 mb-2"></div>');
            inputField = $('<input class="form-control flex-grow-1 mr-2 single-line-text" type="text">').val(variable.defaultValue);
            icon = createIcon();
            
            container.append(inputField );
            if (mandatorySign) container.append(mandatorySign); // Add mandatory sign if applicable
            container.append( icon);

            labelValueCell.append(container);

            inputField.on('input', function() {
                updateVariableData(variableID, $(this).val(), 'Single Line Text');
            });
            break;

        case 'Multiple Line Text':
            container = $('<div class="d-flex align-items-center w-100 mb-2"></div>');
            inputField = $('<input class="form-control flex-grow-1 mr-2 multiple-line-text" type="text">').val(variable.defaultValue);
            icon = createIcon();
            
            container.append(inputField );
            if (mandatorySign) container.append(mandatorySign); // Add mandatory sign if applicable
            container.append( icon);

            labelValueCell.append(container);

            inputField.on('input', function() {
                updateVariableData(variableID, $(this).val(), 'Multiple Line Text');
            });
            break;

            case 'Single Box':
                container = $('<div class="d-flex flex-column align-items-start w-100 mb-2"></div>');
                icon = createIcon();
                var iconContainer = $('<div class="d-flex align-items-center ml-auto"></div>');

                var variableLabelValues = JSON.parse(variable.VariableLabelValue);
                if (Array.isArray(variableLabelValues) && variableLabelValues.length > 0) {
                    var radioGroupName = 'radio_group_' + Math.random().toString(36).substring(7);
                    $.each(variableLabelValues, function(index, value) {
                        var inputValue = value.inputValue || '';
                        var ckEditorContent = value.ckEditorContent || '';

                        var radioContainer = $('<div class="form-check mb-2"></div>');
                        var radioBtn = $('<input class="form-check-input" type="radio">').val(JSON.stringify({inputValue: inputValue, ckEditorContent: ckEditorContent})).attr('name', radioGroupName);
                        var radioLabel = $('<label class="form-check-label">' + inputValue + '</label>');

                        if (variable.defaultValue && variable.defaultValue.includes(inputValue)) {
                            radioBtn.prop('checked', true);
                        }
                        radioContainer.append(radioBtn, radioLabel);
                        container.append(radioContainer);

                        radioBtn.on('change', function() {
                            if ($(this).is(':checked')) {
                                updateVariableData(variableID, $(this).val(), 'Single Box');
                            }
                        });
                    });
                }
                // Append mandatory sign and icon to iconContainer
                if (mandatorySign) iconContainer.append(mandatorySign); // Append mandatory sign first
                iconContainer.append(icon); // Then append the icon
                container.append(iconContainer); // Append the iconContainer to the main container
                labelValueCell.append(container);
                break;


                case 'Multiple Box':
                    container = $('<div class="d-flex flex-column align-items-start w-100 mb-2"></div>');
                    icon = createIcon();
                    var iconContainer = $('<div class="d-flex align-items-center ml-auto"></div>');

                    var variableLabelValues = JSON.parse(variable.VariableLabelValue);
                    if (Array.isArray(variableLabelValues) && variableLabelValues.length > 0) {
                        $.each(variableLabelValues, function(index, value) {
                            var inputValue = value.inputValue || '';
                            var ckEditorContent = value.ckEditorContent || '';

                            var checkboxContainer = $('<div class="form-check mb-2"></div>');
                            var checkbox = $('<input class="form-check-input" type="checkbox">').val(JSON.stringify({inputValue: inputValue, ckEditorContent: ckEditorContent}));

                            if (variable.defaultValue && variable.defaultValue.includes(inputValue)) {
                                checkbox.prop('checked', true);
                            }
                            var checkboxLabel = $('<label class="form-check-label">' + inputValue + '</label>');
                            checkboxContainer.append(checkbox, checkboxLabel);
                            container.append(checkboxContainer);

                            checkbox.on('change', function() {
                                var selectedValues = [];
                                container.find('input[type="checkbox"]:checked').each(function() {
                                    if ($(this).val() !== "on") {
                                        selectedValues.push(JSON.parse($(this).val()));
                                    }
                                });
                                updateVariableData(variableID, selectedValues, 'Multiple Box');
                            });
                        });
                    }
                    // Append mandatory sign and icon to iconContainer
                    if (mandatorySign) iconContainer.append(mandatorySign); // Append mandatory sign first
                    iconContainer.append(icon); // Then append the icon
                    container.append(iconContainer); // Append the iconContainer to the main container
                    labelValueCell.append(container);
                    break;

    }

    tableRow.append(labelValueCell);
    $('#sales-variable tbody').append(tableRow);
});

                        getAllPriceLists(selectedContract); // Fetch and display price lists
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }
        });



        $('#frequency').on('change', function() {
            var selectedProduct = $(this).val();
            var id = window.location.pathname.split('/').pop();
            if (selectedProduct) {
                $.ajax({
                    url: '/get-contracts',
                    type: 'GET',
                    data: {
                        product_name: selectedProduct,
                       
                    },
                    success: function(response) {
                        $('#Contract').empty();
                        if (response.status === 'success') {
                            var contracts = response.contracts;
                            $('#Contract').append('<option value="Select Contract">Select Contract</option>');
                            if (contracts.length > 0) {
                                $.each(contracts, function(index, contract) {
                                    $('#Contract').append('<option value="' + contract.id + '">' + contract.contract_name + '</option>');
                                });
                            } else {
                                $('#Contract').append('<option value="" disabled selected>Contracts not found</option>');
                            }
                            $('#contractRow').show();
                            $('#variableContainer').parent().css('display', 'block');
                        } else {
                            console.error(response.message);
                            $('#Contract').append('<option value="" disabled selected>Contracts not found</option>');
                            $('#contractRow').show();
                            $('#variableContainer').parent().hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }
        });

        var id = window.location.pathname.split('/').pop();
        $.ajax({
            url: '/get-products',
            type: 'GET',
            data: {
                seller_name: "{{ Auth::user()->name }}",
                id: id,
            },
            success: function(response) {
                if(response.status === 'success') {
                    var products = response.products;
                    $.each(products, function(index, productName) {
                        $('#frequency').append('<option>' + productName + '</option>');
                    });
                    $('#frequency').on('change', function() {
                        $('#contractRow').show();
                    });
                } else {
                    console.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });

        var updatedVariableData = {};
         function collectVariableValues() {
    var variableValues = {};

    $('#sales-variable tbody tr').each(function() {
        var variableName = $(this).find('td').first().text().trim();
        var variableType = $(this).data('variable-type');
        var variableId = $(this).data('variable-id');
        var value = '';

        switch (variableType) {
            case 'Dates':
                value = $('#variableDatpicker').val();
                break;

            case 'Single Line Text':
                value = $(this).find('.single-line-text').val();
                break;

            case 'Multiple Line Text':
                value = $(this).find('.multiple-line-text').val();
                break;

            case 'Single Box':
                var selectedRadio = $(this).find('input[type="radio"]:checked');
                if (selectedRadio.length > 0) {
                    value = JSON.parse(selectedRadio.val());
                }
                break;

            case 'Multiple Box':
                var multipleBoxValues = [];
                $(this).find('input[type="checkbox"]:checked').each(function() {
                    if ($(this).val() !== "on") { // Filter out default checkbox values
                        multipleBoxValues.push(JSON.parse($(this).val())); // Parse the value as JSON
                    }
                });
                value = multipleBoxValues;
                break;
        }

        variableValues[variableName] = {
            type: variableType,
            value: value
        };
    });
    console.log('variableValues:', variableValues);
    return variableValues;
}



     
        $('#updateButton').on('click', function() {
            if (validateTableFields()) {
                update();
            }
        });

        function update() {
            var updatedVariableData = [];

            $('#sales-variable tbody tr').each(function() {
                var row = $(this);
                var variableName = row.find('td').eq(0).text();
                var variableValue = row.find('td').eq(1).find('input, select').val();

                var variableId = row.data('variable-id');
                var variableType = row.data('variable-type');

                var variable = {
                    id: variableId,
                    name: variableName,
                    value: variableValue,
                    type: variableType
                };

                updatedVariableData.push(variable);
            });

            var id = window.location.pathname.split('/').pop();
            
            // new way using collectVariableValues () ;

            var variableValuesnew = collectVariableValues();

            var updateVariableDataPromise = $.ajax({
                url: '/update-variable-data',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    variableData: JSON.stringify(variableValuesnew),
                    id: id
                }
            });

            var priceValues = collectPriceValues();
            var savePriceJsonDataPromise = $.ajax({
                url: '/save-pricejson-data',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    priceJsonData: JSON.stringify(priceJsonData),
                    id: id,
                    priceValuesforSave: priceValues,
                    priceValues: priceValues
                }
            });

            // for alert 
            $.when(updateVariableDataPromise, savePriceJsonDataPromise).done(function(updateResult, saveResult) {
                Swal.fire({
                    title: 'Success',
                    text: 'Data updated and price JSON data saved successfully',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000, // Auto close after 5 seconds
                    timerProgressBar: true
               
                });
            
            }).fail(function(xhr, status, error) {
                var response = xhr.responseJSON;

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: `
                        <p>${response.error}</p>
                        <p><strong>Total Check:</strong> ${response.totalCheck}</p>
                        <p><strong>Expected Total:</strong> ${response.expectedTotal}</p>
                    `,
                    footer: 'Please try again later or contact support if the issue persists.' ,
                    timer: 2000, // Auto close after 5 seconds
                    timerProgressBar: true
                });
            });


        }



     

        //---------------------------

            function updateVariableData(key, value, type) {
                    if (type === 'Multiple Box') {
                        updatedVariableData[key] = value; // Directly store the array of objects
                    } else {
                        updatedVariableData[key] = value;
                    }
            }



        function getDateByFrequency(frequency, offset) {
            var offset = offset -1;    
            var currentDate = new Date();
            switch (frequency) {
                case 'daily':
                    currentDate.setDate(currentDate.getDate() + offset);
                    break;
                case 'biweekly':
                    currentDate.setDate(currentDate.getDate() + (14 * offset));
                    break;
                case 'weekly':
                    currentDate.setDate(currentDate.getDate() + (7 * offset));
                    break;
                case 'monthly':
                    currentDate.setMonth(currentDate.getMonth() + offset);
                    break;
                case 'annually':
                    currentDate.setFullYear(currentDate.getFullYear() + offset);
                    break;
                default:
                    break;
            }
            var day = currentDate.getDate().toString().padStart(2, '0');
            var month = (currentDate.getMonth() +1).toString().padStart(2, '0');
            var year = currentDate.getFullYear();
            return day + '/' + month + '/' + year;
        }

        function getCurrencySymbol(currencyCode) {
            switch (currencyCode) {
                case 'EUR':
                    return '€';
                case 'USD':
                    return '$';
                case 'GBP':
                    return '£';
                case 'JPY':
                    return '¥';
                default:
                    return '';
            }
        }

        function updateGlobalArrays() {
            dueDateValues = [];
            amountValues = [];

            $('.importoInput').each(function() {
                amountValues.push($(this).val());
            });

            $('.dovutoIlInput').each(function() {
                dueDateValues.push($(this).val());
            });

            $('#ImpostaTable1 table tbody tr').each(function() {
                var dateCell = $(this).find('.dovutoIlInput');
                if (dateCell.length === 0) {
                    var dateText = $(this).find('td').eq(4).text();
                    if (dateText) {
                        var parts = dateText.split('/');
                        var yyyy_mm_dd = parts[2] + '-' + parts[1].padStart(2, '0') + '-' + parts[0].padStart(2, '0');
                        dueDateValues.push(yyyy_mm_dd);
                    }
                }
            });
        }

        function collectPriceValues() {
            updateGlobalArrays();

            var includeOnPrice = priceJsonData.price === "true";

            var priceValues = {
                dynamicminRange: priceJsonData.dynamicminRange || null,
                fixedvalue: priceJsonData.fixedvalue || null,
                paymentMinRange: priceJsonData.paymentMinRange,
                paymentMaxRange: priceJsonData.paymentMaxRange,
                currency: priceJsonData.currency,
                frequency: priceJsonData.frequency,
                includeonprice: includeOnPrice,
                vatpercentage: priceJsonData.vatPercentage,
                payments: []
            };

            $('#sales-variable tbody tr').each(function(index, row) {
                var cells = $(row).find('td');
                if (cells.length > 1) {
                    var payment = {
                        description: cells.eq(0).text(),
                        amount: parseFloat(cells.eq(1).find('input').val()),
                        vatIncluded: cells.eq(2).text(),
                        dueDate: cells.eq(4).find('input').val() || cells.eq(4).text()
                    };
                    priceValues.payments.push(payment);
                }
            });

            priceValues.dueDateValues = dueDateValues;
            priceValues.amountValues = amountValues;

            return priceValues;
        }

        function getAllPriceLists(selectedContract) {
            $.ajax({
                url: '/get-all-priceLists',
                type: 'GET',
                data: {
                    selectedContractId: selectedContract
                },
                success: function(response) {
                    priceJsonData = response;

                    var fixedvalue = response.fixedvalue;
                    var currency = response.currency;
                    var frequency = response.frequency;
                    var EditableDates = response.EditableDates;
                    var dynamicMinRange = response.dynamicminRange;

                    var selectionValue = dynamicMinRange !== null ? 'dynamic' : 'fixed';

                    var includeOnPrice = response.price === "true";
                    var vatPercentage = response.vatPercentage;
                    var enableVat = response.enableVat === "true";

                    includeOnPrice = Boolean(includeOnPrice);
                    enableVat = Boolean(enableVat);

                    paymentMinRange = response.paymentMinRange;
                    paymentMaxRange = response.paymentMaxRange;

                    let currentMaxRange = paymentMaxRange !== undefined ? paymentMaxRange : response.paymentMaxRange;
                    let currentDynamicMinRange = response.dynamicminRange;

                    if (response.dynamicminRange !== null) {
                        var withtaxon = response.dynamicminRange;
                        if (selectionValue === 'dynamic' && includeOnPrice && enableVat) {
                            var minRangeSliderCAL = parseFloat(response.dynamicminRange) + (vatPercentage * parseFloat(response.dynamicminRange)) / 100;
                            withtaxon = minRangeSliderCAL;
                        }

                        var newRow = $('<tr>');

                        var pricenameCell = $('<td>').html("Price Name: " + response.pricename + "<br> Min Range: " + response.dynamicminRange + " ,Max range: " + response.dynamicmaxRange);

                        var currencySymbol = getCurrencySymbol(response.currency);

                        var dynamicminRangeInput = $('<input>').attr({
                            type: 'text',
                            class: 'form-control dynamicminRangeInput',
                            value: currencySymbol + ' ' + withtaxon.toFixed(2)
                        });

                        dynamicminRangeInput.on('change', function() {
                            var inputValue = parseFloat($(this).val().replace(currencySymbol, '').trim());

                            if (inputValue < response.dynamicminRange || inputValue > response.dynamicmaxRange) {
                                alert('The value must be between ' + response.dynamicminRange + ' and ' + response.dynamicmaxRange);
                                $(this).val(currencySymbol + ' ' + withtaxon.toFixed(2));
                            } else {
                                currentDynamicMinRange = inputValue;
                                priceJsonData.dynamicminRange = inputValue;
                                updateTable(currentMaxRange, currentDynamicMinRange, response, priceJsonData);
                            }
                        });

                        var dynamicminRangeCell = $('<td>').append(dynamicminRangeInput);
                        newRow.append(pricenameCell, dynamicminRangeCell);
                        $('#sales-variable tbody').append(newRow);
                    }

                    if (response.fixedvalue !== null) {
                        var taxon = response.fixedvalue;
                        if (selectionValue !== 'dynamic' && includeOnPrice && enableVat) {
                            var newCalculation = parseFloat(response.fixedvalue) + (vatPercentage * parseFloat(response.fixedvalue)) / 100;
                            taxon = newCalculation;
                        }

                        var newRow = $('<tr>');
                        var pricenameCell = $('<td>').html("Price Name: " + response.pricename + "<br> Min Range: " + response.dynamicminRange + " ,Max range: " + response.dynamicmaxRange);

                        var currencySymbol = getCurrencySymbol(response.currency);
                        var fixedValueInput = taxon;
                        var fixedvalueInput = $('<input>').attr({
                            type: 'text',
                            class: 'form-control',
                            value: currencySymbol + ' ' + taxon.toFixed(2),
                            readonly: true
                        });

                        var fixedvalueCell = $('<td>').append(fixedvalueInput);
                        newRow.append(pricenameCell, fixedvalueCell);
                        $('#sales-variable tbody').append(newRow);
                    }

                    if (response.multiplePayments === "true") {
                        var newRow = $('<tr>');
                        var sliderCell = $('<td>').attr('colspan', '2').css('width', '100%');

                        var valueLabel = $('<label>').text('Payment Number: ').css('display', 'inline-block').css('margin-right', '10px');
                        var valueSpan = $('<span>').attr('id', 'sliderValue').css('display', 'none').css('margin-left', '10px');

                        valueLabel.appendTo(sliderCell);
                        var sliderContainer = $('<div>').css({
                            'display': 'inline-block',
                            'width': '70%',
                            'vertical-align': 'middle'
                        });
                        var slider = $('<div>').css('width', '100%');
                        slider.appendTo(sliderContainer);
                        sliderContainer.appendTo(sliderCell);
                        valueSpan.appendTo(sliderCell);
                        sliderCell.appendTo(newRow);
                        $('#sales-variable tbody').append(newRow);

                        noUiSlider.create(slider[0], {
                            start: [currentMaxRange],
                            connect: [true, false],
                            range: {
                                'min': paymentMinRange !== undefined ? paymentMinRange : response.paymentMinRange,
                                'max': currentMaxRange
                            },
                            behaviour: 'unconstrained-tap',
                            tooltips: {
                                to: function(value) {
                                    return Math.round(value);
                                },
                                from: function(value) {
                                    return Math.round(value);
                                }
                            }
                        });

                        slider[0].noUiSlider.on('update', function(values, handle) {
                            currentMaxRange = Math.round(values[0]);
                            updateTable(currentMaxRange, currentDynamicMinRange, response, priceJsonData);
                        });
                    }

                    function updateTable(maxRange, dynamicMinRange, response, priceJsonData) {

                        $('#sliderValue').text('(Max value: ' + maxRange + ')').attr('title', 'Current Max Value: ' + maxRange);
                        priceJsonData.paymentMaxRange = maxRange;

                        var selectedFixedValueDiv = document.getElementById("ImpostaTable1");
                        selectedFixedValueDiv.innerHTML = '';

                        var fixedvalue = response.fixedvalue;

                        var minrata= response.minrata;
                        console.log('ajker minrata', minrata  );

                        var currency = response.currency;
                        var frequency = response.frequency;
                        var EditableDates = response.EditableDates;

                        var selectionValue = dynamicMinRange !== null ? 'dynamic' : 'fixed';
                        var includeOnPrice = response.price === "true";
                        var vatPercentage = response.vatPercentage;
                        var enableVat = response.enableVat === "true";

                        includeOnPrice = Boolean(includeOnPrice);
                        enableVat = Boolean(enableVat);

                        var minRangeSliderCAL = null;
                        var newCalculation = null;
                        var vatAmount = null;

                        if (selectionValue === 'dynamic' && includeOnPrice && enableVat) {
                            minRangeSliderCAL = parseFloat(dynamicMinRange) + (vatPercentage * parseFloat(dynamicMinRange)) / 100;
                            vatAmount = minRangeSliderCAL - parseFloat(dynamicMinRange);
                        }

                        if (selectionValue === 'fixed' && includeOnPrice && enableVat) {
                            newCalculation = parseFloat(fixedvalue) + (vatPercentage * parseFloat(fixedvalue)) / 100;
                            vatAmount = newCalculation - parseFloat(fixedvalue);
                        }

                        var importo;
                        if (selectionValue === 'dynamic') {
                            if (minRangeSliderCAL !== null) {
                                importo = (minRangeSliderCAL / maxRange).toFixed(2);
                            } else {
                                importo = (dynamicMinRange / maxRange).toFixed(2);
                            }
                        } else {
                            if (newCalculation !== null) {
                                importo = (newCalculation / maxRange).toFixed(2);
                            } else {
                                importo = (fixedvalue / maxRange).toFixed(2);
                            }
                        }

                        var maxRangeVal = Math.round(maxRange);

                        var table = document.createElement('table');
                        table.className = 'table';

                        var thead = table.createTHead();
                        var headerRow = thead.insertRow();

                        var colName;
                        var totalhere;
                        if (selectionValue === 'dynamic') {
                            if (minRangeSliderCAL !== null) {
                                colName = 'Il costo totale di ' + getCurrencySymbol(currency) + ' ' + minRangeSliderCAL.toFixed(2) + ' (IVA Compresa ' + getCurrencySymbol(currency) + ' ' + vatAmount.toFixed(2) + ') sarà corrisposto con le seguenti modalità:';
                                totalhere = parseFloat(minRangeSliderCAL.toFixed(2));
                            } else {
                                colName = 'Il costo totale di ' + getCurrencySymbol(currency) + ' ' + dynamicMinRange + ' + IVA sarà corrisposto con le seguenti modalità:';
                                totalhere = parseFloat(dynamicMinRange);
                            }
                        } else {
                            if (newCalculation !== null) {
                                colName = 'Il costo totale di ' + getCurrencySymbol(currency) + ' ' + newCalculation.toFixed(2) + ' (IVA Compresa ' + getCurrencySymbol(currency) + ' ' + vatAmount.toFixed(2) + ') sarà corrisposto con le seguenti modalità:';
                                totalhere = parseFloat(newCalculation.toFixed(2));
                            } else {
                                colName = 'Il costo totale di ' + getCurrencySymbol(currency) + ' ' + fixedvalue + ' + IVA sarà corrisposto con le seguenti modalità:';
                                totalhere = parseFloat(fixedvalue);
                            }
                        }

                        var th = document.createElement('th');
                        th.textContent = colName;
                        th.colSpan = 5;
                        headerRow.appendChild(th);

                        var tbody = table.createTBody();
                        for (var i = 1; i <= maxRangeVal; i++) {
                            var row = tbody.insertRow();
                            var descrizione = 'Rata ' + i + ' in ' + getCurrencySymbol(currency);

                            var importoInput = document.createElement('input');
                            importoInput.classList.add('form-control', 'importoInput');
                            importoInput.type = 'number';
                            importoInput.style = "width: 100px;";
                            importoInput.value = importo;

                            importoInput.addEventListener('input', function() {
                                var newValue = parseFloat(this.value);


                                  // Check if the new value is less than minrata
                                if (newValue < minrata) {
                                    // Show a SweetAlert in Italian and English
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Errore / Error',
                                        text: 'Il valore della rata non può essere inferiore a ' + minrata + ' / The minimum payment cannot be less than ' + minrata,
                                        confirmButtonText: 'OK'
                                    });

                                    // Reset the input value to minrata or previous valid value
                                    this.value = minrata;
                                    return;  // Exit the function
                                }

                                var currentRow = this.closest('tr');
                                var previousRows = $(currentRow).prevAll();
                                var subsequentRows = $(currentRow).nextAll();

                                var previousTotal = 0;
                                previousRows.each(function() {
                                    var prevValue = parseFloat($(this).find('.importoInput').val());
                                    previousTotal += isNaN(prevValue) ? 0 : prevValue;
                                });

                                var remainingAmount = totalhere - (newValue + previousTotal);
                                var remainingRowsCount = subsequentRows.length;
                                var distributedValue = remainingRowsCount > 0 ? (remainingAmount / remainingRowsCount).toFixed(2) : 0;

                                subsequentRows.each(function() {
                                    $(this).find('.importoInput').val(distributedValue);
                                });

                                updateGlobalArrays();
                            });

                            var dovutoIl = getDateByFrequency(frequency, i);

                            var dovutoIlInput = document.createElement('input');
                            dovutoIlInput.classList.add('form-control', 'dovutoIlInput');
                            dovutoIlInput.style = "width: 128px;";
                            dovutoIlInput.type = 'date';

                            var parts = dovutoIl.split('/');
                            var yyyy_mm_dd = parts[2] + '-' + parts[1].padStart(2, '0') + '-' + parts[0].padStart(2, '0');
                            dovutoIlInput.value = yyyy_mm_dd;

                            dovutoIlInput.addEventListener('change', function() {
                                updateGlobalArrays();
                            });

                            dovutoIlInput.addEventListener('change', function() {
                                var rowIndex = Array.from(this.parentNode.parentNode.parentNode.children).indexOf(this.parentNode.parentNode);
                                var selectedDate = new Date(this.value);

                                for (var k = rowIndex + 1; k < tbody.rows.length; k++) {
                                    selectedDate.setMonth(selectedDate.getMonth() + 1);

                                    var nextRow = tbody.rows[k];
                                    var nextInput = nextRow.querySelector('.dovutoIlInput');

                                    var nextDay = String(selectedDate.getDate()).padStart(2, '0');
                                    var nextMonth = String(selectedDate.getMonth() + 1).padStart(2, '0');
                                    var nextYear = selectedDate.getFullYear();
                                    var nextDate = `${nextYear}-${nextMonth}-${nextDay}`;

                                    nextInput.value = nextDate;
                                }
                                updateGlobalArrays();
                            });

                            var descrizioneCell = row.insertCell();
                            descrizioneCell.textContent = descrizione;
                            var importoCell = row.insertCell();
                            importoCell.appendChild(importoInput);
                            var vatCell = row.insertCell();

                            includeOnPrice = Boolean(includeOnPrice);
                        enableVat = Boolean(enableVat);

                           


                            if (enableVat && includeOnPrice) {
                                vatCell.textContent = "IVA Inc";
                            } else if (!includeOnPrice && enableVat) {
                                vatCell.textContent = "+ IVA " + vatPercentage + "%";
                            } else {
                                vatCell.textContent = "";
                            }

                            
                            var doveCell = row.insertCell();
                            doveCell.textContent = "entro il";
                            var dovutoIlCell = row.insertCell();
                            if (EditableDates) {
                                dovutoIlCell.appendChild(dovutoIlInput);
                            } else {
                                dovutoIlCell.textContent = dovutoIl;
                            }
                        }

                        selectedFixedValueDiv.appendChild(table);
                        updateGlobalArrays();
                    }


                    updateTable(currentMaxRange, currentDynamicMinRange, response, priceJsonData);
                }
            });
        }

    });
</script>
@endsection
