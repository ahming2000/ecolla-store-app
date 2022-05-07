<div class="col-6 col-md-4 col-lg-3 col-xxl-2 mb-3">
    <a href="{{ url('/item/' . $item->id) }}" class="no-anchor-style">
        <div class="card shadow">
            <img src="{{ $item->getCoverImage() }}" class="card-img-top" alt="Cover Image" loading="lazy">

            <div class="card-body">
                <div class="h5 card-title text-truncate">
                    {{ $item->name }}
                </div>

                <span style="color: brown;">
                    {{ $item->getDisplayablePrice() }}
                </span>

                <div class="d-flex justify-content-between">
                    <span>
                        <i class="bi bi-box"></i> {{ $item->getTotalStock() }}
                    </span>

                    <span>
                        <i class="bi bi-eye"></i> {{ $item->view_count }}
                    </span>
                </div>
            </div>
        </div>
    </a>
</div>
