@extends('layouts.app')

@section('title', 'Fill Survey')

@section('content')
<div class="d-flex">
    @include('components.sidebar')

    <div class="flex-grow-1 p-4" style="margin-left: 250px; background: linear-gradient(135deg, #5b4bff, #7b6cff); min-height: 100vh;">
        
        <div class="container mt-3">
            <h2 class="text-white mb-4">Fill Survey</h2>

            <form action="#" method="POST">
                @csrf
                
                @for ($i = 1; $i <= 4; $i++)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white border-0 py-3">
                        <small>Short answer</small>
                        <h6 class="mb-0 mt-1">Apa kesulitan terbesar kamu saat mengumpulkan data?</h6>
                    </div>
                    <div class="card-body">
                        <input type="text" class="form-control bg-light border-0" placeholder="Type here...">
                        <small class="text-danger">Type an error message</small>
                    </div>
                </div>
                @endfor

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="#" class="text-white text-decoration-none">← Back</a>
                    <button type="submit" class="btn btn-light px-5 py-2 fw-bold" style="color: #5b4bff; border-radius: 25px;">SUBMIT</button>
                </div>
            </form>
            
        </div>
    </div>
</div>
@endsection