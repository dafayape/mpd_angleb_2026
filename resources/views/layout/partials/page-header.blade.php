<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-stretch shadow-sm" style="min-height: 54px; border-radius: 4px;">
            <div class="page-header-number">
                {{ $number }}
            </div>
            <div class="page-header-title-bar">
                <h4 class="mb-0 text-white font-size-16 fw-semibold text-uppercase"
                    style="letter-spacing: 0.5px; font-family: 'Poppins', sans-serif;">{{ $title }}</h4>
                <div class="ms-auto flex-shrink-0">
                    {{ $slot ?? '' }}
                </div>
            </div>
        </div>
    </div>
</div>
