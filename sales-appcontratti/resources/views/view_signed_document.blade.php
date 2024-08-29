@extends('layouts.master-without-nav')

@section('title')
    @lang('translation.Coming_Soon')
@endsection


<div class="container">
        <h3>Signed Document: {{ $pdfSignature->selected_pdf_name }}</h3>

        <!-- PDF Container -->
        <div id="pdf-container" style="width: 100%; height: 100%; border: 1px solid #ccc; margin-bottom: 20px;">
            <embed src="{{ asset('storage/pdf/' . $pdfSignature->selected_pdf_name) }}" type="application/pdf" style="width:100%; height:100%;" id="pdf-embed">
        </div>
</div>
 
