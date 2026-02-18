@extends('layout.app')

@section('title', $title ?? 'Halaman')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ $title ?? 'Halaman' }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        @if(isset($breadcrumb))
                            @foreach($breadcrumb as $item)
                                @if($loop->last)
                                    <li class="breadcrumb-item active">{{ $item }}</li>
                                @else
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">{{ $item }}</a></li>
                                @endif
                            @endforeach
                        @else
                            <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                            <li class="breadcrumb-item active">{{ $title ?? 'Halaman' }}</li>
                        @endif
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-layer font-size-48 text-muted"></i>
                    <h5 class="mt-3 text-muted">{{ $title ?? 'Halaman' }}</h5>
                    <p class="text-muted mb-0">Halaman ini sedang dalam pengembangan.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
