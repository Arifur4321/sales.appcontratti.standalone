@extends('layouts.master-without-nav')

@section('title')
    AppContratti
@endsection

@section('content')

<div class="d-flex justify-content-center align-items-center" style="height: 100vh; flex-direction: column; text-align: center;">
    <h3>Your account is disactive now. Contact GF SRL. Thanks</h3>
    <h3>Il tuo account è disattivato. Contatta GF SRL. Grazie</h3>
    
    <!-- Button to log out instantly -->
    <div style="margin-top: 20px;">
        <button id="logout-button" class="btn btn-primary">Go Back</button>
    </div>
</div>

<!-- Hidden form to log out -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<!-- JavaScript for automatic logout after 5 minutes and instant logout on button click -->
<script>
    // Set the automatic logout after 5 minutes (300 seconds)
    setTimeout(function() {
        document.getElementById('logout-form').submit(); // Log out automatically after 5 minutes
    }, 300000); // 300,000 milliseconds = 5 minutes

    // Handle instant logout when the "Go Back" button is clicked
    document.getElementById('logout-button').addEventListener('click', function() {
        document.getElementById('logout-form').submit(); // Log out instantly
    });
</script>

@endsection
