<div class="slider-main-container mb-3">
    @if($item->getTotalImageCount() != 0)
        <button class="slider-control-prev">
            <
        </button>
    @endif

    <div class="slider-container">
        @if(sizeof($item->images) != 0)
            @foreach($item->images as $image)
                <img class="img-fluid general-img" src="{{ $image->image }}" loading="lazy" alt="Image">
            @endforeach
        @endif

        @foreach($item->variations as $variation)
            @if($variation->image != null)
                <img class="img-fluid" id="img-{{ $variation->barcode }}" src="{{ $variation->image }}" loading="lazy" alt="Image">
            @endif
        @endforeach
    </div>

    @if($item->getTotalImageCount() != 0)
        <button class="slider-control-next">
            >
        </button>
    @endif
</div>

<ul class="slider-nav">
    @foreach($item->images as $image)
        <li class="me-1">
            <img class="img-fluid" style="max-height: 100px" src="{{ $image->image }}" loading="lazy" alt="Image">
        </li>
    @endforeach

    @foreach($item->variations as $variation)
        @if($variation->image != null)
            <li class="me-1">
                <img class="img-fluid" style="max-height: 100px" src="{{ $variation->image }}" loading="lazy" alt="Image">
            </li>
        @endif
    @endforeach
</ul>

<script>
    // Slider Container
    let slider = tinySlider({
        container: '.slider-container',
        items: 1,

        // Container Button
        prevButton: '.slider-control-prev',
        nextButton: '.slider-control-next',

        // Container Nav Bar
        navContainer: '.slider-nav',
        navAsThumbnails: true,

        // Config
        autoplay: true,
        autoplayHoverPause: true,
        autoplayButtonOutput: false,
    });

    // Start autoplay
    slider.play();

    // Slider Nav Bar
    let sliderNav = tinySlider({
        container: '.slider-nav',
        items: 5,

        // Config
        nav: false,
        loop: false,
        mouseDrag: true,
        controls: false,
    });
</script>
