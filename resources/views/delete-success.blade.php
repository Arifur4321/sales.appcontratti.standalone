<!-- delete-success.blade.php -->
@extends('layouts.master')

@section('content')
    <script>
        // Display a pop-up with the success message
        window.onload = function () {
            alert('Delete successfully!');
        };
    </script>
@endsection
