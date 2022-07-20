window._ = require('lodash')
window.axios = require('axios')
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

try {
    window.$ = window.jQuery = require('jquery')
    require('./plugin/jquery.ba-throttle-debounce')
    window.bootstrap = require('bootstrap')
} catch (error) {
    console.error(error)
}

import { tns } from 'tiny-slider/src/tiny-slider'
window.tinySlider = tns
