import WOW from 'wow.js/dist/wow.min'

window.useWowJs = () => {
    new WOW({
        animateClass: 'animate__animated'
    }).init()
}
