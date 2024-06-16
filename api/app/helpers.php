<?php

/**
 * Get the front-end URL for the application.
 *
 * @return string
 */
function getFrontUrl() {
    return (env('APP_ENV') == 'local' ? 'http://' : 'https://') . env('SANCTUM_STATEFUL_DOMAINS');
}
