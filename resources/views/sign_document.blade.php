@extends('layouts.master-without-nav')

@section('title')
    Sign Document: {{ $pdfSignature->selected_pdf_name }}
@endsection

<!-- intl-tel-input CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />

<!-- intl-tel-input JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>

<!-- Optional: Include SweetAlert2 CSS for better styling -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

 
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Add Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
 


<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Container for PDF with paginated content -->
<div class="pdf-container" id="arifurPDF">
    @php $page = 1; @endphp

    <!-- Split the content into pages -->
    @foreach($paginatedContent as $pageContent)
        <div class="document-page">

            <!-- Header -->
            <div class="document-header">
                {!! $headerContent !!}
            </div>

            <!-- Main Content -->
            <div id="document-content">
                {!! $pageContent !!}
            </div>

            <!-- Footer -->
            <div class="document-footer">
                {!! $footerContent !!}
            </div>

            <!-- Page Number -->
            <div class="page-number">
                Page {{ $page++ }}
            </div>
        </div>

        <!-- Page Separator -->
        @if (!$loop->last)
            <div class="page-separator"></div>
        @endif
    @endforeach
</div>
<style>
    .pdf-container {
        width: 100%;
        max-width: 900px;
        height: calc(108vh - 100px);
        margin: 0 auto;
        margin-top:7vh;
        overflow-y: scroll;
        background-color: #fff;
        border: 1px solid #ccc;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .document-page {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
    }

    .document-header,
    .document-footer,
    .page-number {
        margin-bottom: 20px;
    }

    .page-separator {
        height: 50px;
        background-color: #f8f8f8;
        border-bottom: 2px solid #ddd;
        margin: 20px 0;
    }

    @media print {
        .page-separator {
            display: none;
        }

        .pdf-container {
            height: auto;
            overflow: visible;
        }

        .document-page {
            page-break-after: always;
        }
    }

    @media (max-width: 768px) {
        .pdf-container {
            width: 95%;
            height: calc(100vh - 100px);
        }
    }
</style>
 

<div id="mobile-number-modal" class="custom-modal">
    <div class="custom-modal-content">
        <span class="close-modal" onclick="closeMobileNumberModal()">&times;</span>
        <h4>{{ __('translation.enter_mobile_number') }}</h4>
        <div class="input-group mb-3">
            <span class="input-group-text" id="mobile-addon">{{ __('translation.mobile_number') }}</span>
            <input type="tel" class="form-control" id="recipientMobile" placeholder="{{ __('translation.enter_mobile_number') }}" aria-label="{{ __('translation.mobile_number') }}">
        </div>
        <button id="send-otp" class="btn btn-primary">{{ __('translation.send_otp') }}</button>
    </div>
</div>

<!-- OTP Verification Modal -->
<div id="otp-verification-modal" class="custom-modal">
    <div class="custom-modal-content">
        <span class="close-modal" onclick="closeOTPVerificationModal()">&times;</span>
        <h4>{{ __('translation.enter_otp') }}</h4>
        <p>{{ __('translation.otp_sent_message') }}</p>
        <div class="input-group mb-3">
            <input type="text" class="form-control" id="otpInput" placeholder="{{ __('translation.enter_otp') }}" aria-label="OTP">
        </div>

        <!-- Buttons: Verify OTP and Resend OTP -->
        <div class="d-flex justify-content-between mt-3">
            <button id="verify-otp" class="btn btn-success">{{ __('translation.verify_otp') }}</button>
            <button id="resend-otp" class="btn btn-warning mt-2" style="display:none;">{{ __('translation.resend_otp') }}</button>
        </div>

        <p id="timer" class="mt-2 text-muted">Time remaining: <span id="time">10:00</span> minutes</p>
    </div>
</div>
 

<!-- Signature Modal -------------------------------------------------------------------------------------------->
<div id="signature-modal" class="custom-modal">
    <div class="custom-modal-content">
        <span class="close-modal" onclick="closeSignatureModal()">&times;</span>
        <h4>   @lang('translation.Draw your signature') </h4>
        <canvas id="signature-canvas" style="border: 1px solid black; width: 100%; height: 200px;"></canvas>
        <button id="clear-signature" class="btn btn-warning mt-2">@lang('translation.clear_signature')</button>
        <button id="save-signature" class="btn btn-primary mt-2"> @lang('translation.save_signature')</button>
    </div>
</div>

<!-- Option Modal for Multiple Signatures -->
<div id="option-modal" class="custom-modal">
    <div class="custom-modal-content">
        <span class="close-modal" onclick="closeOptionModal()">&times;</span>
        <h4>{{ __('translation.apply_signature_question') }}</h4>
        <button id="apply-everywhere" class="btn btn-success mt-2">{{ __('translation.apply_everywhere') }}</button>
        <button id="apply-one-by-one" class="btn btn-primary mt-2">{{ __('translation.sign_one_by_one') }}</button>
    </div>
</div>

<!-- Site Header Background -->
<!-- <div id="site-header-background" style="position: fixed; top: 0; left: 0; width: 100%; height: 60px; background-color: #f5f5f5; border-bottom: 1px solid #ccc; z-index: 98;">
    <div style="display: flex; align-items: center; height: 100%; float:left; margin-left:5px;">
       
        <img src="{{ asset('images/logo-light.png') }}" alt="Site Logo" style="height: 40px; margin-right: 10px;">
        <span style="font-size: 18px; font-weight: bold;">GF SRL</span>
    </div>
</div> -->

<!-- Site Header Background -->
<!--
<div id="site-header-background" style="position: fixed; top: 0; left: 0; width: 100%; height: 60px; background-color: #f5f5f5; border-bottom: 1px solid #ccc; z-index: 98;">
    <div style="display: flex; align-items: center; height: 100%; float:left; margin-left:5px;">
        
        <img src="{{ asset('images/logo-light.png') }}" alt="Site Logo" style="height: 40px; margin-right: 10px;">
        <span id="company-name" style="font-size: 18px; font-weight: bold;"></span>
    </div>
</div>
-->
<div id="site-header-background" style="position: fixed; top: 0; left: 0; width: 100%; height: 60px; background-color: #f5f5f5; border-bottom: 1px solid #ccc; z-index: 98; display: flex; justify-content: space-between; align-items: center; padding: 0 10px;">
    <div style="display: flex; align-items: center;">
        <!-- Add your logo -->
        <img src="{{ asset('images/logo-light.png') }}" alt="Site Logo" style="height: 40px; margin-right: 10px;">
        <span id="company-name" style="font-size: 18px; font-weight: bold;">Your Company Name</span>
    </div>

    <!-- Language Dropdown Button (Right Aligned) -->
    <div class="dropdown d-inline-block">
        <button type="button" class="btn header-item waves-effect"
            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            @switch(Session::get('lang'))
                @case('ru')
                    <img src="{{ URL::asset('build/images/flags/russia.jpg') }}" alt="Header Language" style="height: 14px; width: auto;">
                @break
                @case('it')
                    <img src="{{ URL::asset('build/images/flags/italy.jpg') }}" alt="Header Language" style="height: 14px; width: auto;">
                @break
                @case('gr')
                    <img src="{{ URL::asset('build/images/flags/germany.jpg') }}" alt="Header Language" style="height: 14px; width: auto;">
                @break
                @case('sp')
                    <img src="{{ URL::asset('build/images/flags/spain.jpg') }}" alt="Header Language" style="height: 14px; width: auto;">
                @break
                @default
                    <img src="{{ URL::asset('build/images/flags/us.jpg') }}" alt="Header Language" style="height: 14px; width: auto;">
            @endswitch
        </button>  
        
        <div class="dropdown-menu dropdown-menu-end">
            
       <a href="javascript:void(0)" onclick="changeLanguage('en')" class="dropdown-item notify-item language">
            <img src="{{ URL::asset('build/images/flags/us.jpg') }}" alt="user-image" style="height: 14px; width: auto; margin-bottom: 4px;">
            <span class="align-middle">English</span>
        </a>
        
        <a href="javascript:void(0)" onclick="changeLanguage('it')" class="dropdown-item notify-item language">
            <img src="{{ URL::asset('build/images/flags/italy.jpg') }}" alt="user-image" style="height: 14px; width: auto; margin-bottom: 4px;">
            <span class="align-middle">Italian</span>
        </a>

            
            
        </div>
    </div>
</div>



<script>
    // Get the id from the current URL
    var id = window.location.pathname.split('/').pop();

    // Make an AJAX call to get the company name
    $.ajax({
        url: '/signature/get-company-name/' + id,
        method: 'GET',
        success: function(response) {
            // Update the span with the company name
            if (response.company_name) {
                $('#company-name').text(response.company_name);
            } else {
                $('#company-name').text('GF SRL');
            }
        },
        error: function() {
            $('#company-name').text('GF SRL');  // Default to GF SRL if there's an error
        }
    });
</script>



<!-- Continue Button -->
<div id="continue-button" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 99;">
    <button class="btn btn-primary">Continue</button>
</div>

<!-- Modal for Final Confirmation -->
<div id="final-confirmation-modal" class="custom-modal">
    <div class="custom-modal-content">
        <span class="close-modal" onclick="closeFinalConfirmationModal()">&times;</span>
        <h4>{{ __('translation.confirm_action') }}</h4>
        <p>{{ __('translation.choose_option') }}</p>
        <button id="edit-signature" class="btn btn-warning mt-2">{{ __('translation.edit_signature') }}</button>
        <button id="agree-signature" class="btn btn-success mt-2">{{ __('translation.agree_signature') }}</button>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<style>
    /* Custom Modal Styling */
    .custom-modal {
        display: none;
        position: fixed;
        z-index: 999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .custom-modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
        position: relative;
    }

    .close-modal {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close-modal:hover,
    .close-modal:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    /* Highlight and Styling for Click to Sign */
    .sig-container {
        background-color: #ffeb3b;
        padding: 10px;
        cursor: pointer;
        font-weight: bold;
        text-align: center;
        border: 2px dashed #ff9800;
        color: #333;
        border-radius: 5px;
        width: 150px;
        height: 100px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }

    .sig-container:hover {
        background-color: #ffc107;
        border-color: #ff5722;
    }

    .highlight {
        background-color: #ffc107 !important;
        border-color: #ff5722 !important;
    }
</style>

<script>

function changeLanguage(lang) {
    $.ajax({
        url: "{{ route('change.language') }}", // Route to handle language change
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}", // Add CSRF token for security
            lang: lang
        },
        success: function(response) {
            location.reload(); // Reload the page to apply the new language
        },
        error: function(error) {
            console.error('Error changing language:', error);
        }
    });
}


let iti = null;
document.addEventListener("DOMContentLoaded", function () {
    const mobileInput = document.querySelector("#recipientMobile");
    iti = window.intlTelInput(mobileInput, {
        initialCountry: "it", // Set Italy as the default country
        separateDialCode: true,
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
    });
});

function closeMobileNumberModal() {
    document.getElementById('mobile-number-modal').style.display = 'none';
}

// Function to open the Mobile Number Modal  



function openMobileNumberModal() {
        closeFinalConfirmationModal();
        document.getElementById('mobile-number-modal').style.display = 'block';

        // Extract the ID from the current URL
        const url = window.location.pathname;
        const id = url.substring(url.lastIndexOf('/') + 1); // Extract ID from URL

        // Fetch the mobile number associated with this ID from the SalesListDraft table
        fetch(`/contract/${id}/mobile-number`)
            .then(response => response.json())
            .then(data => {
                if (data.mobile_number && data.country_code) {
                    const fullMobileNumber = `+${data.country_code}${data.mobile_number}`;
                    // Set the number using intl-tel-input
                    iti.setNumber(fullMobileNumber);
                }
            })
            .catch(error => {
                console.error('Error fetching mobile number:', error);
            });
    }

// Functions to open and close OTP Verification Modal
function openOTPVerificationModal() {
    document.getElementById('otp-verification-modal').style.display = 'block';
    startTimer();
}

function closeOTPVerificationModal() {
    document.getElementById('otp-verification-modal').style.display = 'none';
    clearInterval(timerInterval);
}


document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('send-otp').addEventListener('click', function () {
        const mobileNumber = iti.getNumber();
        if (!iti.isValidNumber()) {
            Swal.fire('Invalid Mobile Number', 'Please enter a valid mobile number.', 'error');
            return;
        }

        // Disable the send button to prevent multiple clicks
        document.getElementById('send-otp').disabled = true;

        // Extract the ID from the URL
        var id = window.location.pathname.split('/').pop();

        // Check if SMS is enabled before sending OTP
        fetch("{{ route('check.sms.enabled') }}")
            .then(response => response.json())
            .then(data => {
                if (data.sms_enabled) {
                    fetch("{{ route('send.otp') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        },
                        body: JSON.stringify({ 
                            mobile: mobileNumber, // Pass the mobile number
                            id: id                 // Pass the ID from the URL
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('send-otp').disabled = false;
                        if (data.success) {
                            // OTP sent successfully, start the timer
                            handleOTPSendSuccess(); // Function that handles OTP success and timer
                        } else {
                            Swal.fire('Error', data.message || 'Failed to send OTP.', 'error');
                        }
                    })
                    .catch(error => {
                        document.getElementById('send-otp').disabled = false;
                        Swal.fire('Error', 'An error occurred while sending OTP.', 'error');
                    });
                } else {
                    Swal.fire({
                        title:   "@lang('translation.thank_you_signature')",
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitSignatures();
                        }
                    });
                }
            })
            .catch(error => {
                document.getElementById('send-otp').disabled = false;
                Swal.fire('Error', 'An error occurred while checking SMS status.', 'error');
            });
    });
});

// Handle "Verify OTP" button click 
document.getElementById('verify-otp').addEventListener('click', function () {
    const otp = document.getElementById('otpInput').value.trim();
    if (otp === "") {
        Swal.fire('Invalid OTP', 'Please enter the OTP sent to your mobile.', 'error');
        return;
    }

    // Disable the verify button to prevent multiple clicks
    document.getElementById('verify-otp').disabled = true;

    // Send AJAX request to verify OTP
    fetch("{{ route('verify.otp') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
        },
        body: JSON.stringify({ otp: otp }),
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('verify-otp').disabled = false;
        if (data.success) {
            closeOTPVerificationModal();
            Swal.fire({
                title:   "@lang('translation.thank_you_signature')",
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitSignatures();
                }
            });
        } else {
            Swal.fire('Error', data.message || 'Invalid OTP. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('verify-otp').disabled = false;
        Swal.fire('Error', 'An error occurred while verifying OTP.', 'error');
    });
});



let timerInterval;
let alertShown = false; // To ensure the Swal alert only shows once when the timer expires

function startTimer(duration = 600) { // Set default timer to 600 seconds (10 minutes)
    let timeLeft = duration;

    // Update the timer display to show minutes:seconds
    function updateTimerDisplay(time) {
        if (time >= 0) {
            const minutes = Math.floor(time / 60);
            const seconds = time % 60;
            const formattedSeconds = seconds < 10 ? '0' + seconds : seconds;
            document.getElementById('time').innerText = `${minutes}:${formattedSeconds}`;
        }
    }

    updateTimerDisplay(timeLeft); // Display initial time as 10:00

    // Hide the resend button initially
    document.getElementById('resend-otp').style.display = 'none';

    // Reset the alert status to false before starting a new timer
    alertShown = false;

    timerInterval = setInterval(() => {
        if (timeLeft > 0) {
            timeLeft--;
            updateTimerDisplay(timeLeft); // Update the display each second
        } else if (!alertShown) {
            clearInterval(timerInterval);
            alertShown = true; // Ensure only the button appears after 10 minutes

            // After 10 minutes, only display the Resend OTP button without any alert
            document.getElementById('resend-otp').style.display = 'block';
        }
    }, 1000);
}

// Start the timer only when OTP is sent successfully
function handleOTPSendSuccess() {
    closeMobileNumberModal();
    openOTPVerificationModal();
    startTimer(600); // Start the 10-minute timer after sending OTP
}

 

document.getElementById('resend-otp').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent default form submission if inside a form

    const mobileNumber = iti.getNumber(); // Retrieve mobile number again

    // Disable the resend button to prevent multiple clicks
    document.getElementById('resend-otp').disabled = true;

    // Extract the ID from the URL
    var id = window.location.pathname.split('/').pop();

    // Function to set up a timeout for fetch
    const fetchWithTimeout = (url, options, timeout = 10000) => {
        return Promise.race([
            fetch(url, options),
            new Promise((_, reject) => 
                setTimeout(() => reject(new Error('Request timeout')), timeout)
            )
        ]);
    };

    fetchWithTimeout("{{ route('resend.otp') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
        },
        body: JSON.stringify({ 
            mobile: mobileNumber, // Pass the mobile number
            id: id                // Pass the ID from the URL
        }),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok'); // Handle non-200 responses
        }
        return response.json(); // Parse JSON
    })
    .then(data => {
        if (data.success) {
            Swal.fire('OTP Resent', 'A new OTP has been sent to your mobile number.', 'success');
            document.getElementById('resend-otp').disabled = false;
            document.getElementById('resend-otp').style.display = 'none'; // Hide the resend button again
            startTimer(600); // Restart the 10-minute timer after resending OTP
        } else {
            throw new Error(data.message || 'Failed to resend OTP.'); // Handle logical errors
        }
    })
    .catch(error => {
        console.error('Error:', error);

        if (error.message === 'Request timeout') {
            Swal.fire('Error', 'The request took too long. Please try again.', 'error');
        } else {
            Swal.fire('Error', error.message || 'An error occurred while resending OTP.', 'error');
        }
        
        document.getElementById('resend-otp').disabled = false;
    });
});




 
    function showContinueButton() {
        document.getElementById('continue-button').style.display = 'block';
    }

    function openFinalConfirmationModal() {
        document.getElementById('final-confirmation-modal').style.display = 'block';
    }

    function closeFinalConfirmationModal() {
        document.getElementById('final-confirmation-modal').style.display = 'none';
    }

    document.getElementById('continue-button').addEventListener('click', function() {
        openFinalConfirmationModal();
    });
 

    document.getElementById('agree-signature').addEventListener('click', function () {
    console.log('Agree button clicked'); // Debugging line

    // Ensure all signature placeholders are signed
    if (signatureContainers.length !== Object.keys(signatures).length) {
        Swal.fire({
            title: 'Signature Mismatch Detected',
            text: 'There are some signature placeholders that are not yet signed. Please complete all signatures.',
            icon: 'warning',
            confirmButtonText: 'OK'
        }).then(() => {
            highlightNextSignature();  // Highlight the next unsigned signature placeholder
        });
        return;
    }

    // Check if SMS is enabled before opening mobile number modal
    fetch("{{ route('check.sms.enabled') }}")
        .then(response => response.json())
        .then(data => {
            if (data.sms_enabled) {
                // Open the mobile number modal if SMS is enabled
                openMobileNumberModal();
            } else {
                // If SMS is not enabled, directly thank the user
                Swal.fire({
                    title:   "@lang('translation.thank_you_signature')",    
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitSignatures();
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error checking SMS status:', error);
            Swal.fire('Error', 'An error occurred while checking SMS status.', 'error');
        });
});




    document.getElementById('edit-signature').addEventListener('click', function () {
        // Remove all signatures from the placeholders
        signatures = {};
        signatureIndex = 0;
        signatureContainers.forEach(container => container.innerHTML = '');
        highlightNextSignature();
        closeFinalConfirmationModal();
    });

    function sendConfirmationEmail() {
        fetch("{{ route('send.confirmation.email', ['id' => $pdfSignature->id]) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = "{{ route('view.signed.document', ['id' => $pdfSignature->id]) }}";
            } else {
                alert("Failed to send confirmation email: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred while sending the confirmation email.");
        });
    }

    // for submit the signature 

    function submitSignatures() {
       
        const mobileNumber = iti.getNumber(); 

        fetch("{{ route('submit.signature', ['id' => $pdfSignature->id]) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
            },
            body: JSON.stringify({
                signatures: signatures, // Pass all signatures as an array
                recipientMobile: mobileNumber
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to the signed document view
               // window.location.href = data.redirectUrl;
               window.location.reload();
            } else {

                window.location.reload();
               // alert("Failed to sign the document: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred while signing the document.");
        });
    }

    let currentSignatureElement;
    let signatures = {}; // Store each signature separately
    let signatureIndex = 0;
    let signatureContainers;

    document.addEventListener("DOMContentLoaded", function () {
        signatureContainers = document.querySelectorAll('.sig-container');
        highlightNextSignature();
    });

    function openSignatureModal(element) {
        currentSignatureElement = element;
        document.getElementById('signature-modal').style.display = 'block';
        resizeCanvas();
    }

    function closeSignatureModal() {
        document.getElementById('signature-modal').style.display = 'none';
    }

    function openOptionModal() {
        document.getElementById('option-modal').style.display = 'block';
    }

    function closeOptionModal() {
        document.getElementById('option-modal').style.display = 'none';
    }

    const canvas = document.getElementById('signature-canvas');
    const signaturePad = new SignaturePad(canvas);

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
    }

    window.addEventListener("resize", resizeCanvas);

    document.getElementById('clear-signature').addEventListener('click', function () {
        signaturePad.clear();
    });

    document.getElementById('save-signature').addEventListener('click', function () {
        if (signaturePad.isEmpty()) {
            alert("Please provide a signature.");
            return;
        }

        const signatureData = signaturePad.toDataURL("image/png");
        signatures[signatureIndex - 1] = signatureData;
        currentSignatureElement.innerHTML = '<img src="' + signatureData + '" width="150" height="100">';
        closeSignatureModal();
        openOptionModal();
    });

    document.getElementById('apply-everywhere').addEventListener('click', function () {
        for (let i = signatureIndex - 1; i < signatureContainers.length; i++) {
            signatureContainers[i].innerHTML = '<img src="' + signatures[signatureIndex - 1] + '" width="150" height="100">';
            signatures[i] = signatures[signatureIndex - 1]; // Update the signatures array
        }

        closeOptionModal();
        showContinueButton(); // Show the Continue button immediately after applying the signature everywhere
    });

    document.getElementById('apply-one-by-one').addEventListener('click', function () {
        closeOptionModal();
        highlightNextSignature();
    });

    function highlightNextSignature() {
        let foundUnsigned = false;

        for (let i = 0; i < signatureContainers.length; i++) {
            if (!signatures[i]) {
                foundUnsigned = true;
                signatureContainers.forEach(container => container.classList.remove('highlight'));
                
                const nextSignature = signatureContainers[i];
                nextSignature.classList.add('highlight');
                nextSignature.scrollIntoView({ behavior: "smooth", block: "center" });
                
                nextSignature.addEventListener('click', function () {
                    openSignatureModal(nextSignature);
                }, { once: true });

                signatureIndex = i + 1;
                break;
            }
        }

        if (!foundUnsigned) {
            showContinueButton();
        }
    }

 
</script>
