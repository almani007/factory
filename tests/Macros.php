<?php

use Illuminate\Foundation\Testing\TestResponse;

TestResponse::macro('assertInertia', function (callable $callback) {
    $page = $this->session['inertia']->data['page'] ?? null;

    expect($page)->not->toBeNull();

    $pageData = $page['data'] ?? [];
    $pageProps = $pageData['props'] ?? [];

    $callback($pageProps);

    return $this;
});

TestResponse::macro('assertViewHas', function ($key, $value = null) {
    $viewData = $this->getOriginalContent()->getData() ?? [];

    expect($viewData[$key] ?? null)->not->toBeNull();

    if ($value !== null) {
        expect($viewData[$key])->toEqual($value);
    }

    return $this;
});
