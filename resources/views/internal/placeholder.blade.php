@extends('layouts.internal')

@section('title', $title)
@section('page-title', $title)
@section('breadcrumb', 'Portal foundation')

@section('content')
    <div class="container-fluid px-0">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-5 text-center">
                <div class="mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning-emphasis"
                    style="width:72px;height:72px;">
                    <i class="fas fa-compass-drafting fs-3"></i>
                </div>
                <h4 class="mb-3">{{ $title }}</h4>
                <p class="text-muted mb-0 mx-auto" style="max-width: 720px;">{{ $description }}</p>
            </div>
        </div>
    </div>
@endsection
