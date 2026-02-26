<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row shadow-sm page-header-container">
            <div class="page-header-number flex-shrink-0">
                {{ $number }}
            </div>
            <div
                class="page-header-title-bar w-100 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <h4 class="mb-0 text-white font-size-18 fw-bold text-uppercase title-text">{{ $title }}</h4>
                </div>

                @if (isset($slot) && trim($slot) !== '')
                    <div class="page-header-extra ms-md-auto text-md-end mt-2 mt-md-0">
                        {{ $slot }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
