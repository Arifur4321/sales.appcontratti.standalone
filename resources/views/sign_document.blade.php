@extends('layouts.master-without-nav')

@section('title')
    Sign Document: {{ $pdfSignature->selected_pdf_name }}
@endsection

<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- 
now this div  class="container" id="arifurPDF"  appears   $headerContent  $htmlContent $footerContent in look like 
one page user can also scrool down . i want it there should be just  page 1 , page 2 etc each page with  headerContent and footerContent.
so so user can scrool down also  . just like same now just initilize page 1 , page , page 3 etc
modify whatever neccesaary in   in blade.php page and write in details . u can do javascript for loops with htmlContent for each page with
headerContent and  footerContent . is it possible with only modify the blade.php 
-->

<div class="container" id="arifurPDF" style="margin-top:60px">
    @php $page = 1; @endphp

    <!-- Split the content into pages -->
    @foreach($paginatedContent as $pageContent)
        <div class="document-page" style="page-break-after: always;">

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
            <div style="text-align: right; margin-top: 10px;">
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
    .page-separator {
        height: 50px; /* Height of the space between pages */
        background-color: #f8f8f8; /* Light gray background to highlight separation */
        border-bottom: 2px solid #ddd; /* Optional border to make the separation clearer */
        margin: 20px 0; /* Optional margin to give extra spacing */
    }

    @media print {
        .page-separator {
            display: none; /* Hide the separator when printing */
        }

        .document-page {
            page-break-after: always;
        }
    }
</style>


<!-- Signature Modal -->
<div id="signature-modal" class="custom-modal">
    <div class="custom-modal-content">
        <span class="close-modal" onclick="closeSignatureModal()">&times;</span>
        <h4>Draw Your Signature</h4>
        <canvas id="signature-canvas" style="border: 1px solid black; width: 100%; height: 200px;"></canvas>
        <button id="clear-signature" class="btn btn-warning mt-2">Clear</button>
        <button id="save-signature" class="btn btn-primary mt-2">Save Signature</button>
    </div>
</div>

<!-- Option Modal for Multiple Signatures -->
<div id="option-modal" class="custom-modal">
    <div class="custom-modal-content">
        <span class="close-modal" onclick="closeOptionModal()">&times;</span>
        <h4>Do you want to apply this signature everywhere?</h4>
        <button id="apply-everywhere" class="btn btn-success mt-2">Yes, apply everywhere</button>
        <button id="apply-one-by-one" class="btn btn-primary mt-2">No, sign one by one</button>
    </div>
</div>

<!-- Continue Button 
<div id="continue-button" style="display: none; position: fixed; top: 20px; right: 20px;">
    <button class="btn btn-primary">Continue</button>
</div>
-->

 <!-- Site Header Background -->
<div id="site-header-background" style="position: fixed; top: 0; left: 0; width: 100%; height: 60px; background-color: #f5f5f5; border-bottom: 1px solid #ccc; z-index: 98;">
    <div style="display: flex; align-items: center; height: 100%; float:left; margin-left:5px; ">
        <!-- Add your logo -->
        <img src="{{ asset('images/logo-light.png') }}" alt="Site Logo" style="height: 40px; margin-right: 10px;">
        <span style="font-size: 18px; font-weight: bold;">GF SRL</span>
    </div>
</div>

<!-- Continue Button -->
<div id="continue-button" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 99;">
    <button class="btn btn-primary">Continue</button>
</div>




<!-- Modal for Final Confirmation -->
<div id="final-confirmation-modal" class="custom-modal">
    <div class="custom-modal-content">
        <span class="close-modal" onclick="closeFinalConfirmationModal()">&times;</span>
        <h4>Confirm your action</h4>
        <p>Please choose one of the following options:</p>
        <button id="edit-signature" class="btn btn-warning mt-2">Edit Signature</button>
        <button id="agree-signature" class="btn btn-success mt-2">I Agree</button>
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

    document.getElementById('agree-signature').addEventListener('click', function() {
        if (Object.keys(signatures).length === 0) {
            Swal.fire('Please sign the document before finalizing.');
            return;
        }

        Swal.fire({
            title: 'Thank you for your signature!',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                submitSignatures();
            }
        });
    });



    document.getElementById('edit-signature').addEventListener('click', function() {
        closeFinalConfirmationModal();
        signatureIndex = 0;
        highlightNextSignature();
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

  
    function submitSignatures() {
        fetch("{{ route('submit.signature', ['id' => $pdfSignature->id]) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
            },
            body: JSON.stringify({
                signatures: signatures, // Pass all signatures as an array
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to the signed document view
                window.location.href = data.redirectUrl;
            } else {
                alert("Failed to sign the document: " + data.message);
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
        signatureContainers.forEach(function (container, index) {
            container.innerHTML = '<img src="' + signatures[0] + '" width="150" height="100">';
        });

        closeOptionModal();
        showContinueButton(); // Show the Continue button immediately after applying the signature everywhere
    });

    document.getElementById('apply-one-by-one').addEventListener('click', function () {
        closeOptionModal();
        highlightNextSignature();
    });

    function highlightNextSignature() {
        if (signatureIndex < signatureContainers.length) {
            signatureContainers.forEach(container => container.classList.remove('highlight'));
            const nextSignature = signatureContainers[signatureIndex];
            nextSignature.classList.add('highlight');
            nextSignature.scrollIntoView({ behavior: "smooth", block: "center" });
            nextSignature.addEventListener('click', function () {
                openSignatureModal(nextSignature);
            }, { once: true });

            signatureIndex++;
        } else {
            showContinueButton(); // Show the Continue button when all signatures are completed
        }
    }
</script>
