import { tns } from 'tiny-slider/src/tiny-slider'

window.useTinySliderCarousel = (
    containerName,
    xs = 2,
    sm = 3,
    md = 4,
    lg = 5,
    xl = 6,
    xxl = 7) => {
    tns({
        container: containerName,
        items: xs,
        responsive: {
            576: {
                items: sm,
            },
            768: {
                items: md,
            },
            992: {
                items: lg,
            },
            1200: {
                items: xl,
            },
            1400: {
                items: xxl,
            },
        },

        mouseDrag: true,
        controls: false,
        nav: false,
        loop: false,
    })
}

window.useTinySliderItemImageNavigator = (
    sliderContainer = '.slider-container',
    prevButton = '.slider-control-prev',
    nextButton = '.slider-control-next',
    navContainer = '.slider-nav') => {
    tns({
        container: navContainer,
        items: 5,

        // Config
        nav: false,
        loop: false,
        mouseDrag: true,
        controls: false,
    })

    return tns({
        container: sliderContainer,
        items: 1,

        // Container Button
        prevButton: prevButton,
        nextButton: nextButton,

        // Container Nav Bar
        navContainer: navContainer,
        navAsThumbnails: true,

        // Config
        autoplay: true,
        autoplayHoverPause: true,
        autoplayButtonOutput: false,
    })
}
