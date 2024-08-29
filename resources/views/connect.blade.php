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
            arifur table
        @endslot
    @endcomponent
 
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <meta name="csrf-token" content="{{ csrf_token() }}">
 
  <div class="container">
  


    <div class="card">
      <div class="card-header">
      Codice 1% Limited
      </div>

      @if ($message = Session::get('success'))
      <div class="alert alert-success alert-block">
          <button type="button" class="close" data-dismiss="alert">×</button>    
          <strong>{{ $message }}</strong>
      </div>
    @endif


      <div class="card-body">
        <h5 class="card-title">Docusign Tutorial</h5>
        <p class="card-text">Click the button below to connect your appication with docusign</p>
        @if ($message = Session::get('success'))
          <a href="{{route('docusign.sign')}}" class="btn btn-primary">Click to sign document</a>
        @else
          <a href="{{route('connect.docusign')}}" class="btn btn-primary">Connect Docusign</a>
        @endif
        
      </div>
    </div>
  </div>
  @endsection