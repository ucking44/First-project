window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * eeco exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. eeco and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import eeco from 'laravel-eeco';

// window.Pusher = require('pusher-js');

// window.eeco = new eeco({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });
