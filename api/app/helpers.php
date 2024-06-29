<?php

/**
 * Get the front-end URL for the application.
 *
 * @return string
 */
function getFrontUrl() {
    return (config('app.env') == 'local' ? 'http://' : 'https://') . config('app.front_url');
}
