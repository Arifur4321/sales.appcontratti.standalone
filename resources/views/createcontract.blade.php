@extends('layouts.master')

@section('title') @lang('translation.createcontract') @endsection

@section('content')

    @component('components.breadcrumb')
        @slot('li_1') Forms @endslot
        @slot('title') Codice 1% Form Editors @endslot
    @endcomponent
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Add this line in your HTML file before your own JavaScript code -->
    <!-- Include jsPDF library -->
 

    
    <!-- Include Bootstrap JS (for demo purposes only) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.943/pdf.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
    

    <div class="row justify-content-end">
        <!-- First button 
        <div class="col-auto">
            <div class="text-end">
                <button type="button" class="btn btn-primary"  onclick="openModalNew()">Create New Contract</button>
            </div>
        </div> -->


<!-- variable Modal -->
<div class="modal" id="exampleModalNew" tabindex="-1" aria-labelledby="exampleModalLabelNew" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabelNew">Variable List</h5>
                <div class="col-sm">
                    <div class="search-box me-2 d-inline-block">
                        <div class="position-relative">
                            <input type="text" class="form-control" autocomplete="off" id="searchInput" placeholder="Search...">
                            <i class="bx bx-search-alt search-icon"></i>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="productFormNew" action="/createcontract" method="POST" >
                    <!-- for table -->
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <!-- Wrap the table inside a div with fixed height and auto scroll -->
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table id="ContractList" action="/createcontract" method="POST" class="table">
                            <thead>
                                <tr>
                                    <th>VariableID</th>
                                    <th>Var Name</th>
                                    <th>Var Type</th>
                                    <th>Description</th>
                                    <th>CreatedDate</th>
                                    <th>UpdatedDate</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($variables as $contract)
                                    <tr>
                                        <td>{{ $contract->VariableID }}</td>
                                        <td>{{ $contract->VariableName }}</td>
                                        <td>{{ $contract->VariableType }}</td>
                                        <td>{{ $contract->Description }}</td>
                                        <td>{{ $contract->created_at }}</td>
                                        <td>{{ $contract->updated_at }}</td>
                                        <td>
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input add-checkbox"
                                                 onchange="checkCheckbox(this, '{{ $contract->VariableName }}') ">
                                            </label>
                                            <button type="button" class="btn btn-primary add-button"
                                             data-bs-dismiss="modal" onclick="insertVariable('{{ $contract->VariableName }}')" disabled> Add </button>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                <!-- <button type="button" class="btn btn-primary" onclick="">Save Product</button> -->
            </div>
        </div>
    </div>
</div>
 

 

  <!-- Header Or Footer modal working one  with below script-->
 <!--
    <div class="modal" id="HeaderOrFooterModal" tabindex="-1" aria-labelledby="HeaderOrFooterModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="HeaderOrFooterModalLabel">Header/Footer Entries</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">  
                    <input type="checkbox" id="checkbox1" onchange="toggleDropdown('dropdown1')"> Header
                    <select id="dropdown1" class="form-select" style="display: none;">
                        @foreach($headerEntries as $id => $header)
                            <option value="{{ $id }}">{{ $header }}</option>
                        @endforeach
                    
                    </select>
                </div>
         
                <div class="mb-3"> 
                    <input type="checkbox" id="checkbox2" onchange="toggleDropdown('dropdown2')"> Footer
                    <select id="dropdown2" class="form-select" style="display: none;">
                        @foreach($footerEntries as $id => $footer)
                            <option value="{{ $id }}">{{ $footer }}</option>
                        @endforeach
                
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close </button>
               
            </div>

        </div>
    </div>
</div> 
 
<script>
    function openHeaderOrFooterModal() {
        var myModal = new bootstrap.Modal(document.getElementById('HeaderOrFooterModal'));
        myModal.show();
    }
    
    function toggleDropdown(dropdownId) {
        var dropdown = document.getElementById(dropdownId);
        var checkboxId = dropdownId.replace('dropdown', 'checkbox');
        var checkbox = document.getElementById(checkboxId);
        if (checkbox.checked) {
            dropdown.style.display = 'block';
            dropdown.disabled = false;
        } else {
            dropdown.style.display = 'none';
            dropdown.disabled = true;
        }
    }   
</script>   -->

 
    <!-- Header Or Footer modal working one  with below script-->
    <div class="modal" id="HeaderOrFooterModal" tabindex="-1" aria-labelledby="HeaderOrFooterModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="HeaderOrFooterModalLabel">Header/Footer Entries</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="checkbox" id="checkbox1" onchange="toggleDropdowns('checkbox1', 'dropdown1', 'dropdown2')"> Header
                        <div class="row">
                            <div class="col">
                                <select id="dropdown1" class="form-select" style="display: none;">
                                    @foreach($headerEntries as $id => $header)
                                        <option value="{{ $id }}">{{ $header }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <select id="dropdown2" class="form-select" style="display: none;">
                                    <option value="first">First Page</option>
                                    <option value="every">Every Page</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="checkbox" id="checkbox2" onchange="toggleDropdowns('checkbox2', 'dropdown3', 'dropdown4')"> Footer
                        <div class="row">
                            <div class="col">
                                <select id="dropdown3" class="form-select" style="display: none;">
                                    @foreach($footerEntries as $id => $footer)
                                        <option value="{{ $id }}">{{ $footer }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <select id="dropdown4" class="form-select" style="display: none;">
                                    <option value="first">First Page</option>
                                    <option value="every">Every Page</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Save</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openHeaderOrFooterModal() {
            var myModal = new bootstrap.Modal(document.getElementById('HeaderOrFooterModal'));
            myModal.show();
        }

        function toggleDropdowns(checkboxId, dropdownId1, dropdownId2) {
            var checkbox = document.getElementById(checkboxId);
            var dropdown1 = document.getElementById(dropdownId1);
            var dropdown2 = document.getElementById(dropdownId2);

            if (checkbox.checked) {
                dropdown1.style.display = 'block';
                dropdown2.style.display = 'block';
            } else {
                dropdown1.style.display = 'none';
                dropdown2.style.display = 'none';
            }
        }
    </script>




    <!-- Product Modal -->
    <div class="modal" id="exampleModalProduct" tabindex="-1" aria-labelledby="exampleModalLabelNew" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabelNew">Product List</h5>
                    <div class="col-sm">
                        <div class="search-box me-2 d-inline-block">
                            <div class="position-relative">
                                <input type="text" class="form-control" autocomplete="off" id="searchInputproduct" placeholder="Search...">
                                <i class="bx bx-search-alt search-icon"></i>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <form id="productFormNew" action="/createcontract" method="POST">
                        <!-- for table -->
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <table id="ProductList" action="/createcontract" method="POST" class="table">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ $product->description }}</td>
                                    <td>
                                        <!-- Checkbox to select product -->
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="{{ $product->id }}" id="productCheckbox_{{ $product->id }}" name="selectedProduct">
                                            <label class="form-check-label" for="productCheckbox_{{ $product->id }}">
                                                Add
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    <!-- <button type="button" class="btn btn-primary" onclick="">Save Product</button> -->
                </div>
            </div>
        </div>
    </div>

<script>
 document.addEventListener("DOMContentLoaded", function() {
    // Get all checkboxes within the modal content
    var checkboxes = document.querySelectorAll('#exampleModalProduct input[type="checkbox"]');
    
    // Add event listener to each checkbox
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            // Uncheck all checkboxes except the one that was just checked
            checkboxes.forEach(function(cb) {
                if (cb !== checkbox) {
                    cb.checked = false;
                }
            });
        });
    });
});
</script>

 
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 

<script>
         

    $(document).ready(function() {
        // Delegate the change event handling to the tbody element
        $('tbody').on('change', '.add-checkbox', function() {
            // Find the parent row of the checkbox
            var $row = $(this).closest('tr');
            // Find the add button within the same row and enable/disable it based on the checkbox state
            $row.find('.add-button').prop('disabled', !$(this).prop('checked'));
        });
    });
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

    $(document).ready(function () {
        // Reference to the input field and the table
        var $searchInput = $('#searchInputproduct');
        var $table = $('#ProductList');

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

 

<div class="card">
    <div class="card-body">
        <form id="contractForm" action="/createcontract" method="POST">
     
            <div class="d-flex align-items-center mb-3 fixed-buttons">
                <label for="title" class="form-label me-2" style="width: 120px;">Contract Name</label>
                <input type="text" class="form-control w-75" id="title" name="title">
                
                <div class="ms-2 d-flex"  >

                    <!-- open pdf previews button -->
                
            
                    <!-- <button type="button" id="preview-button" class="btn btn-primary me-2 btn-lg" 
                    onclick="previewPDF()">Preview</button> -->

                    <button type="button" onclick="previewPDF()" id="preview-button" 
                    class="btn btn-primary me-2 btn-lg">Preview</button> 
    
                    <button type="button" id="HeaderOrFooter" class="btn btn-primary me-2 btn-lg" 
                    onclick="openHeaderOrFooterModal()">Header/Footer</button>
                
                    <button type="button" class="btn btn-primary me-2 btn-lg" onclick="openproductmodal()">Product</button>
                    <!-- open modal to show all table 

                    <button type="button" class="btn btn-primary me-2 btn-lg" onclick="saveData('variable')">Variable</button> -->

                    <button type="button" class="btn btn-primary me-2 btn-lg"  onclick="openModalNew()">Variable</button>
                    
                    <button type="button" id="signbutton" class="btn btn-primary me-2 btn-lg" >  Signature </button>
                    <!-- Button to insert dummy  text 
                    <button id="insertTextBtn"  >Insert Text</button> -->    
            
                    <!-- Include the CKEditor library -->
                </div>
                
                <div class="ms-2">
                    <button type="button" id="savebutton" class="btn btn-primary me-2 btn-lg" 
                    onclick="saveData('save')">Save</button>
                </div>
        
            </div>
  
            <!-- arifur CSKEdiotr  -->
             <textarea id="editor" name="editor" style="width: 100%; max-width: 500px;"></textarea>
                   
            <!--  for licenced ckeditor 
                <div id="presence-list-container"></div>

            <div id="editor-container">
                <div class="container">
                    <div id="outline" class="document-outline-container"></div>
                    <div id="editor"></div>
                     <div class="sidebar" id="sidebar"></div> 
                </div>
            </div> -->

            <!-- <div id="revision-viewer-container">
                <div class="container">
                    <div id="revision-viewer-editor"></div>
                    <div class="sidebar" id="revision-viewer-sidebar"></div>
                </div>
            </div> -->


            <style>
                .ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) {
                    border-color: var(--ck-color-base-border);
                    height: 500px !important;
                
                }
                .ck.ck-editor__editable_inline>:last-child {
                    margin-bottom: var(--ck-spacing-large);
                    height: 500px;
                }

                .ck-editor__editable {
                    min-height: 500px;
                }

                .fixed-buttons {
                    position: sticky;
                    top: 0;
                }

              

            </style>
        </form>
 

    </div>
</div>





<script>
function fetchVariables() {
    $.ajax({
        url: '/fetch-variables',
        type: 'GET',
        success: function (response) {
            populateModal(response);
        },
        error: function (error) {
            console.error('Error fetching data:', error);
        }
    });
}

    function populateModal(data) {
        // Populate your modal with the fetched data
        // For example, you can loop through the data and create table rows
        // Then display the modal
    }
    function openproductmodal() {
            // Using Bootstrap's JavaScript to open the product list modal
            var myModal = new bootstrap.Modal(document.getElementById('exampleModalProduct'));
            myModal.show();
        }
    $('#exampleModalProduct').modal('hide');

    function openModalNew() {
            // Using Bootstrap's JavaScript to open the product list modal
            var myModal = new bootstrap.Modal(document.getElementById('exampleModalNew'));
            myModal.show();
        }

    $('#exampleModalNew').modal('hide');
</script>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.2/jspdf.min.js"></script>
<!--<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script> -->


<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.943/pdf.min.js"></script>

<!-- <script src="https://cdn.ckbox.io/CKBox/2.2.0/ckbox.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/super-build/ckeditor.js"></script> -->

<!--  classic CSKEDitor custom build  
<script src="{{ asset('js/ckeditor/build/ckeditor.js') }}"></script>-->
<script>
        // working cskditor 
        let editor; // Global variable for main CKEditor instance        
        
        ClassicEditor
            .create(document.querySelector('#editor') ,{
                
                ckfinder: {
                        uploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
                    }
            }
            )
            .then(createdEditor => {
                // Assign the created editor to the global variable
                editor = createdEditor;

                // Define a function to insert text into CKEditor 5
                window.insertVariable = function(variableName) {
                    // Insert variableName at the current cursor position
                    const currentPosition = editor.model.document.selection.getLastPosition();
                    if (currentPosition) {
                        editor.model.change(writer => {
                            writer.insertText("%"+variableName+"%", currentPosition);
                        });
                    }
                }
            })
            .catch(error => {
                console.error(error);
            });

            //   $(document).ready(function() {
            //     $('#signbutton').click(function() {
            //         const imageUrl = 'public/signature/firma.jpg';
            //         const img = "<img src='" + imageUrl + "'/>";
            //         //config.allowedContent = 'img[src,alt,width,height]'; editor.data.set
                    
            //         const currentPosition = editor.model.document.selection.getLastPosition();
            //         if (currentPosition) {
            //            // editor.model.change(writer => {
            //                 editor.data.set(img, currentPosition);
            //            // });

            //         }
            //     });
            // });

            $(document).ready(function() {
                $('#signbutton').click(function() {
                    const imageUrl = 'firma.jpg';
                   // const img = "<img src='" + imageUrl + "'/>";
                  //  const img = "<img src='https://www.redballoontoystore.com/cdn/shop/products/Playground-Ball-OutdoorActive-Schylling_460x@2x.jpg?v=1651770584'/>";
                  const img = "<img src='https://i.ibb.co/71g553C/FIRMA-QUI.jpg'/>";
                    const previousContent = editor.getData();
                    const currentPosition = editor.model.document.selection.getLastPosition();
                    
                    if (currentPosition) {
                        const newData = previousContent + img;
                        editor.data.set(newData, { position: currentPosition });
                    }
                });
            });



 
    function previewPDF() {
    // Get data from CKEditor
    var editorData = editor.getData();

    // Get selected header or footer data
    var headerDropdown = document.getElementById('dropdown1');
    var footerDropdown = document.getElementById('dropdown3');
    var headerValue = headerDropdown.options[headerDropdown.selectedIndex].text;
    var footerValue = footerDropdown.options[footerDropdown.selectedIndex].text;

    // Determine if header or footer is selected for first or every page
    var headerLocation = document.getElementById('dropdown2').value;
    var footerLocation = document.getElementById('dropdown4').value;

    // Check if header checkbox is checked
    var headerCheckbox = document.getElementById('checkbox1').checked;

    // Check if footer checkbox is checked
    var footerCheckbox = document.getElementById('checkbox2').checked;

    // Construct title text including selected header or footer values
    var titleHeader = "";
    var titleFooter = "";

    // Construct header and footer based on selection
    var headerHTML = '';
    var footerHTML = '';

    // Include header if checkbox is checked and it's selected for the first page or every page
    if (headerCheckbox) {
        if (headerLocation === 'first' || headerLocation === 'every') {
            titleHeader += headerValue  ;
            headerHTML = '<div style="position: fixed; top: 40px; right: 10px;">' + headerValue + '</div>';
        }
    }

    // Include footer if checkbox is checked and it's selected for the first page or every page
    if (footerCheckbox) {
        if (footerLocation === 'first' || footerLocation === 'every') {
            titleFooter += footerValue + " - " + footerLocation;
            footerHTML = '<div style="position: fixed; bottom: 5px; right: 20px;">' + footerValue +  '</div>';
        }
    }

    // Convert HTML content to PDF using jQuery
    var myWindow = window.open('', 'PRINT', 'height=600,width=800');

    myWindow.document.write('<html><title style="text-align:right">' + 
 "Codice 1% PDF &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+  titleHeader +'</title>');
    myWindow.document.write('<style>@page { margin: 100px; counter-reset: page-counter; }</style>'); // Reset page counter
    myWindow.document.write('</head><body>');

    // Increment page counter for each page
    myWindow.document.write('<div style="position: absolute; top: -50px; right: 50px;">Page: <span style="counter-increment: page-counter; content: counter(page-counter);"></span></div>');

    // Write editor data
    myWindow.document.write(editorData);

    // Include footer based on selection
    myWindow.document.write('</body><footer>' + footerHTML + '</footer></html>');

    myWindow.document.close(); // necessary for IE >= 10
    myWindow.onload = function () {
        myWindow.print();
        myWindow.close();
    };
}


// testing previewPDF 

// Include header if checkbox is checked and it's selected for the first page only







// Example usage:
//previewPDF("Your Title Text Here");

    //     let editor; // Global variable for main CKEditor instance        

    //     CKEDITOR.ClassicEditor.create( document.querySelector( '#editor' ), {
    //     // https://ckeditor.com/docs/ckeditor5/latest/features/toolbar/toolbar.html#extended-toolbar-configuration-format
    //     //plugins: [ EasyImage, Image, /* ... */ ],
    //     extraPlugins: 'EasyImage',
    //     toolbar: {
    //         items: [
    //             'aiCommands', 'aiAssistant', '|',
    //             'ckbox', 'uploadImage', '|',
    //             'exportPDF','exportWord', '|',
    //             'comment', 'trackChanges', 'revisionHistory', '|',
    //             'findAndReplace', 'selectAll', 'formatPainter', '|',
    //             'undo', 'redo',
    //             '-',
    //             'bold', 'italic', 'strikethrough', 'underline', 'removeFormat', '|',
    //             'bulletedList', 'numberedList', 'todoList', '|',
    //             'outdent', 'indent', '|',
    //             'alignment', '|',
    //             '-',
    //             'heading', '|',
    //             'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
    //             'link', 'blockQuote', 'insertTable', 'mediaEmbed', 'codeBlock', 'htmlEmbed', 'tableOfContents', 'insertTemplate', '|',
    //             'specialCharacters', 'horizontalLine', 'pageBreak', '|',
    //             // Intentionally skipped buttons to keep the toolbar smaller, feel free to enable them:
    //             // 'code', 'subscript', 'superscript', 'textPartLanguage', '|',
    //             // ** To use source editing remember to disable real-time collaboration plugins **
    //             // 'sourceEditing'
    //         ],
    //         shouldNotGroupWhenFull: true
    //     },
    //     // Changing the language of the interface requires loading the language file using the <script> tag.
    //     // language: 'es',
    //     list: {
    //         properties: {
    //             styles: true,
    //             startIndex: true,
    //             reversed: true
    //         }
    //     },
    //     // https://ckeditor.com/docs/ckeditor5/latest/features/headings.html#configuration
    //     heading: {
    //         options: [
    //             { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
    //             { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
    //             { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
    //             { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
    //             { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
    //             { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
    //             { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
    //         ]
    //     },
    //     // https://ckeditor.com/docs/ckeditor5/latest/features/font.html#configuring-the-font-family-feature
    //     fontFamily: {
    //         options: [
    //             'default',
    //             'Arial, Helvetica, sans-serif',
    //             'Courier New, Courier, monospace',
    //             'Georgia, serif',
    //             'Lucida Sans Unicode, Lucida Grande, sans-serif',
    //             'Tahoma, Geneva, sans-serif',
    //             'Times New Roman, Times, serif',
    //             'Trebuchet MS, Helvetica, sans-serif',
    //             'Verdana, Geneva, sans-serif'
    //         ],
    //         supportAllValues: true
    //     },
    //     // https://ckeditor.com/docs/ckeditor5/latest/features/font.html#configuring-the-font-size-feature
    //     fontSize: {
    //         options: [ 10, 12, 14, 'default', 18, 20, 22 ],
    //         supportAllValues: true
    //     },
    //     // Be careful with the setting below. It instructs CKEditor to accept ALL HTML markup.
    //     // https://ckeditor.com/docs/ckeditor5/latest/features/general-html-support.html#enabling-all-html-features
    //     // htmlSupport: {
    //     // 	allow: [
    //     // 		{
    //     // 			name: /.*/,
    //     // 			attributes: true,
    //     // 			classes: true,
    //     // 			styles: true
    //     // 		}
    //     // 	]
    //     // },
    //     // Be careful with enabling previews
    //     // https://ckeditor.com/docs/ckeditor5/latest/features/html-embed.html#content-previews
    //     htmlEmbed: {
    //         showPreviews: true
    //     },
    //     // https://ckeditor.com/docs/ckeditor5/latest/features/mentions.html#configuration
    //     mention: {
    //         feeds: [
    //             {
    //                 marker: '@',
    //                 feed: [
    //                     '@apple', '@bears', '@brownie', '@cake', '@cake', '@candy', '@canes', '@chocolate', '@cookie', '@cotton', '@cream',
    //                     '@cupcake', '@danish', '@donut', '@dragée', '@fruitcake', '@gingerbread', '@gummi', '@ice', '@jelly-o',
    //                     '@liquorice', '@macaroon', '@marzipan', '@oat', '@pie', '@plum', '@pudding', '@sesame', '@snaps', '@soufflé',
    //                     '@sugar', '@sweet', '@topping', '@wafer'
    //                 ],
    //                 minimumCharacters: 1
    //             }
    //         ]
    //     },
    //     template: {
    //         definitions: [
    //             {
    //                 title: 'The title of the template',
    //                 description: 'A longer description of the template',
    //                 data: '<p>Data inserted into the content</p>'
    //             },
    //             {
    //                 title: 'Annual financial report',
    //                 description: 'A report that spells out the company\'s financial condition.',
    //                 data: `<figure class="table">
	// 					<table style="border:2px solid hsl(0, 0%, 0%);">
	// 						<thead>
	// 							<tr>
	// 								<th style="text-align:center;" rowspan="2">Metric name</th>
	// 								<th style="text-align:center;" colspan="4">Year</th>
	// 							</tr>
	// 							<tr>
	// 								<th style="background-color:hsl(90, 75%, 60%);text-align:center;">2019</th>
	// 								<th style="background-color:hsl(90, 75%, 60%);text-align:center;">2020</th>
	// 								<th style="background-color:hsl(90, 75%, 60%);text-align:center;">2021</th>
	// 								<th style="background-color:hsl(90, 75%, 60%);text-align:center;">2022</th>
	// 							</tr>
	// 						</thead>
	// 						<tbody>
	// 							<tr>
	// 								<th><strong>Revenue</strong></th>
	// 								<td>$100,000.00</td>
	// 								<td>$120,000.00</td>
	// 								<td>$130,000.00</td>
	// 								<td>$180,000.00</td>
	// 							</tr>
	// 							<tr>
	// 								<th style="background-color:hsl(0, 0%, 90%);"><strong>Operating expenses</strong></th>
	// 								<td>&nbsp;</td>
	// 								<td>&nbsp;</td>
	// 								<td>&nbsp;</td>
	// 								<td>&nbsp;</td>
	// 							</tr>
	// 							<tr>
	// 								<th><strong>Interest</strong></th>
	// 								<td>&nbsp;</td>
	// 								<td>&nbsp;</td>
	// 								<td>&nbsp;</td>
	// 								<td>&nbsp;</td>
	// 							</tr>
	// 							<tr>
	// 								<th style="background-color:hsl(0, 0%, 90%);"><strong>Net profit</strong></th>
	// 								<td>&nbsp;</td>
	// 								<td>&nbsp;</td>
	// 								<td>&nbsp;</td>
	// 								<td>&nbsp;</td>
	// 							</tr>
	// 						</tbody>
	// 					</table>
	// 				</figure>`
    //             },
    //         ]
    //     },
    //     // https://ckeditor.com/docs/ckeditor5/latest/features/editor-placeholder.html#using-the-editor-configuration
    //     placeholder: 'Welcome to CKEditor 5!',
    //     // Used by real-time collaboration
    //    cloudServices: {
    //         // Be careful - do not use the development token endpoint on production systems!
    //         tokenUrl: 'https://106148.cke-cs.com/token/dev/1qI5VmPbaqDXGW5M1eC5eimn9UpgbehBUars?limit=10',
    //         webSocketUrl: 'wss://106148.cke-cs.com/ws',
    //         uploadUrl: 'https://106148.cke-cs.com/easyimage/upload/'
    //     },
    //     collaboration: {
    //         // Modify the channelId to simulate editing different documents
    //         // https://ckeditor.com/docs/ckeditor5/latest/features/collaboration/real-time-collaboration/real-time-collaboration-integration.html#the-channelid-configuration-property
    //         channelId: 'document-id-7'
    //     },
    //     // https://ckeditor.com/docs/ckeditor5/latest/features/collaboration/annotations/annotations-custom-configuration.html#sidebar-configuration
    //     sidebar: {
    //         container: document.querySelector( '#sidebar' )
    //     },
    //     documentOutline: {
    //         container: document.querySelector( '#outline')
    //     },
    //     // https://ckeditor.com/docs/ckeditor5/latest/features/collaboration/real-time-collaboration/users-in-real-time-collaboration.html#users-presence-list
    //     presenceList: {
    //         container: document.querySelector( '#presence-list-container' )
    //     },
    //     // Add configuration for the comments editor if the Comments plugin is added.
    //     // https://ckeditor.com/docs/ckeditor5/latest/features/collaboration/annotations/annotations-custom-configuration.html#comment-editor-configuration
    //     comments: {
    //         editorConfig: {
    //             extraPlugins: CKEDITOR.ClassicEditor.builtinPlugins.filter( plugin => {
    //                 // Use e.g. Ctrl+B in the comments editor to bold text.
    //                 return [ 'Bold', 'Italic', 'Underline', 'List', 'Autoformat', 'Mention' ].includes( plugin.pluginName );
    //             } ),
    //             // Combine mentions + Webhooks to notify users about new comments
    //             // https://ckeditor.com/docs/cs/latest/guides/webhooks/events.html
    //             mention: {
    //                 feeds: [
    //                     {
    //                         marker: '@',
    //                         feed: [
    //                             '@Baby Doe', '@Joe Doe', '@Jane Doe', '@Jane Roe', '@Richard Roe'
    //                         ],
    //                         minimumCharacters: 1
    //                     }
    //                 ]
    //             },
    //         }
    //     },
    //     // Do not include revision history configuration if you do not want to integrate it.
    //     // Remember to remove the 'revisionHistory' button from the toolbar in such a case.
    //     revisionHistory: {
    //         editorContainer: document.querySelector( '#editor-container' ),
    //         viewerContainer: document.querySelector( '#revision-viewer-container' ),
    //         viewerEditorElement: document.querySelector( '#revision-viewer-editor' ),
    //         viewerSidebarContainer: document.querySelector( '#revision-viewer-sidebar' ),
    //     },
    //     // https://ckeditor.com/docs/ckeditor5/latest/features/images/image-upload/ckbox.html
    //     ckbox: {
    //         // Be careful - do not use the development token endpoint on production systems!
    //         tokenUrl: 'https://106148.cke-cs.com/token/dev/1qI5VmPbaqDXGW5M1eC5eimn9UpgbehBUars?limit=10'
    //     },
    //     // AI Assistant feature configuration.
    //     // https://ckeditor.com/docs/ckeditor5/latest/features/ai-assistant.html
    //     aiAssistant: {
    //     //     // Provide the URL to the OpenAI proxy endpoint in your application.
    //     //     apiUrl: 'http://url-to-your-openai-proxy-endpoint/'
    //     },
    //     style: {
    //         definitions: [
    //             {
    //                 name: 'Article category',
    //                 element: 'h3',
    //                 classes: [ 'category' ]
    //             },
    //             {
    //                 name: 'Info box',
    //                 element: 'p',
    //                 classes: [ 'info-box' ]
    //             },
    //         ]
    //     },
    //     // License key is required only by the Pagination plugin and non-realtime Comments/Track changes.
    //     licenseKey: 'dmkwQkd1OU1DUDM1ZjF6ZUZySjRiRU1jSmNJemIxNi9Ta3Z3SDZHNGZRZjlhWlpFVkhYekJmSHpGZnlFLU1qQXlOREF6TWpjPQ==',
    //     removePlugins: [
    //         // Before enabling Pagination plugin, make sure to provide proper configuration and add relevant buttons to the toolbar
    //         // https://ckeditor.com/docs/ckeditor5/latest/features/pagination/pagination.html
    //         'Pagination',
    //         // Intentionally disabled, file uploads are handled by CKBox
    //         'Base64UploadAdapter',
    //         // Intentionally disabled, file uploads are handled by CKBox
    //         'CKFinder',
    //         // Intentionally disabled, file uploads are handled by CKBox
    //         //'EasyImage',
    //         // Requires additional license key
    //         'WProofreader',
    //         // Incompatible with real-time collaboration
    //         'SourceEditing',
    //         // Careful, with the Mathtype plugin CKEditor will not load when loading this sample
    //         // from a local file system (file://) - load this site via HTTP server if you enable MathType
    //         'MathType'
    //         // If you would like to adjust enabled collaboration features:
    //         // 'RealTimeCollaborativeComments',
    //         // 'RealTimeCollaborativeTrackChanges',
    //         // 'RealTimeCollaborativeRevisionHistory',
    //         // 'PresenceList',
    //         // 'Comments',
    //         // 'TrackChanges',
    //         // 'TrackChangesData',
    //         // 'RevisionHistory',
    //     ],
  
    // } )
    //         .then(createdEditor => {
    //             // Assign the created editor to the global variable
    //             editor = createdEditor;

    //             // Define a function to insert text into CKEditor 5
    //             window.insertVariable = function(variableName) {
    //                 // Insert variableName at the current cursor position
    //                 const currentPosition = editor.model.document.selection.getLastPosition();
    //                 if (currentPosition) {
    //                     editor.model.change(writer => {
    //                         writer.insertText("%"+variableName+"%", currentPosition);
    //                     });
    //                 }
    //             }
    //         window.editor = editor;
    //         const annotationsUIs = editor.plugins.get( 'AnnotationsUIs' );
    //         const sidebarElement = document.querySelector( '.sidebar' );
    //         let currentWidth;

    //         function refreshDisplayMode() {
    //             // Check the window width to avoid the UI switching when the mobile keyboard shows up.
    //             if ( window.innerWidth === currentWidth ) {
    //                 return;
    //             }
    //             currentWidth = window.innerWidth;

    //             if ( currentWidth < 1000 ) {
    //                 sidebarElement.classList.remove( 'narrow' );
    //                 sidebarElement.classList.add( 'hidden' );
    //                 annotationsUIs.switchTo( 'inline' );
    //             }
    //             else if ( currentWidth < 1300 ) {
    //                 sidebarElement.classList.remove( 'hidden' );
    //                 sidebarElement.classList.add( 'narrow' );
    //                 annotationsUIs.switchTo( 'narrowSidebar' );
    //             }
    //             else {
    //                 sidebarElement.classList.remove( 'hidden', 'narrow' );
    //                 annotationsUIs.switchTo( 'wideSidebar' );
    //             }
    //         }
    //         editor.ui.view.listenTo( window, 'resize', refreshDisplayMode );
    //         refreshDisplayMode();

    //         return editor;
            
    //         })
    //         .catch(error => {
    //             console.error(error);
    //         });

         let dataSaved = false;
            
        // Function to save data when Save button is clicked
        function saveData() {
            // Check if CKEditor is initialized
            if (!editor) {
                console.error('CKEditor is not initialized.');
                return;
            }

            // Get the contract name from the input field
            const contractName = document.querySelector('#title').value;

            // Check if the contract name is empty

            if (!contractName.trim()) {
                console.error('Contract name cannot be empty.');
                return;
            }
            // to get the image 
            //const imageFile = document.querySelector('#image').files[0];
            
            const imageUrl = editor.plugins.get('EasyImage') ;
            // Get all form fields
            const formData = {
                _token : "{{csrf_token() }}",
                contract_name : contractName ,
                editor_content : editor.getData(),
            }
       

            saveContent(formData); // Call saveContent function to save data
            dataSaved = true;
        }
        
        window.addEventListener('beforeunload', function (e) {
            if (!dataSaved) {
                // Prompt the user if they want to exit without saving
                e.preventDefault();
                e.returnValue = ''; // This is for older browsers
                return 'The data is not saved. Do you want to exit?';
            }
        });

        function saveContent(formData) {
            $.ajax({
                url: '/savecontract',
                type: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    window.location.href = "/Contract-List";
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }


        //    function previewPDF() {
        //     // Get the content of the CKEditor
        //     var editorContent = editor.getData();

        //     // Send the content to the server for PDF generation
        //     fetch('/generate-pdf', {
        //         method: 'POST',
        //         headers: {
        //             'Content-Type': 'application/json',
        //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
        //         },
        //         body: JSON.stringify({ content: editorContent })
        //     })
        //     .then(function(response) {
        //         return response.blob();
        //     })
        //     .then(function(blob) {
        //         // Create a blob URL for the PDF
        //         var pdfUrl = URL.createObjectURL(blob);

        //         // Open the PDF in a new tab
        //         window.open(pdfUrl);
        //     })
        //     .catch(function(error) {
        //         console.error('Error generating PDF:', error);
        //     });
        // }

     

 
        

            function countOccurrences(str, searchValue) {
            var regex = new RegExp(searchValue, 'g');
            var matches = str.match(regex);
            return matches ? matches.length : 0;
            }

        

            // Function to handle checkbox change event
            // Function to handle checkbox change event
            function checkCheckbox(checkbox, variableName) {
            if (!checkbox.checked) {
                editor.model.change(writer => {
                    // Get the current editor data
                    var editorData = editor.getData();
                    // Calculate the count of occurrences of the variable
                    var count = countOccurrences(editorData, variableName);

                    if (count > 0) {
                        Swal.fire({
                            title: 'Variable Found!',
                            text: 'The variable ' + variableName + ' appears ' + count + ' times in the editor content. Do you want to delete it?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, delete it',
                            cancelButtonText: 'No, cancel',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Perform delete operation
                                var latestvar = "%" + variableName + "%";
                                editorData = deleteSubstring(editorData, latestvar);
                                // Set the modified content back to the editor
                                //editor.setData(editorData);
                                editor.data.set(  editorData, { suppressErrorInCollaboration: true } )
                            } else {
                                // If user cancels deletion, recheck the checkbox
                                checkbox.checked = true;
                            }
                        });
                    }
                });
            }
        }

        
            //     // Function to delete a substring from a string
            // function deleteSubstring(str, searchValue) {
            //     var regex = new RegExp(searchValue, 'g');
            //     return str.replace(regex, '');
            // }
                // Function to delete a substring from a string
            function deleteSubstring(str, searchValue) {
                // Escape special characters in the searchValue
                var escapedSearchValue = searchValue.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                var regex = new RegExp(escapedSearchValue, 'g');
                return str.replace(regex, '');
            }



         
        

        //Function to insert a variable into CKEditor
        function insertVariable(variableName) {
            // Get the current content of the editor
            const currentContent = editor.getData();
            // Append the variable to the content with % signs
            const newContent = currentContent + `%${variableName}%`;
            // Set the updated content back to the editor
            editor.setData(newContent);
        }
        
 
         
</script>

<style>
        /* body {
            max-width: 1400px;
            padding: 0 20px;
            margin: 20px auto;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            overflow-y: scroll;
        } */
        /* Set the minimum height of Classic editor */
        .ck.ck-editor__editable_inline:not(.ck-editor__nested-editable) {
            min-height: 400px;
        }
        /* Styles to render an editor with a sidebar for comments & suggestions */
        .container {
            display: flex;
            flex-direction: row;
        }
        .document-outline-container {
            background-color: #f3f7fb;
            width: 20px;
        }
        .sidebar {
            width: 320px;
        }
        #editor-container .ck.ck-editor {
            width: 860px;
        }
        #editor-container .sidebar {
            margin-left: 20px;
        }
        #editor-container .sidebar.narrow {
            width: 30px;
        }
        /* Keep the automatic height of the editor for adding comments */
        .ck-annotation-wrapper .ck.ck-editor__editable_inline {
            min-height: auto;
        }
        /* Styles for viewing revision history */
        #revision-viewer-container {
            display: none;
        }
        #revision-viewer-container .ck.ck-editor {
            width: 860px;
        }
        #revision-viewer-container .ck.ck-content {
            min-height: 400px;
        }
        #revision-viewer-container .sidebar {
            border: 1px #c4c4c4 solid;
            margin-left: -1px;
            width: 320px;
        }
        #revision-viewer-container .ck.ck-revision-history-sidebar__header {
            height: 39px;
            background: #FAFAFA;
        }
        .hidden {
            display: none!important;
        }
        .ck.ck-user {
            display: none;
        }
        .ck.ck-reset.ck-document-outline {
            display: none;
        }
       
            


    </style>
@endsection

