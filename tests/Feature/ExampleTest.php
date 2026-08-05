<?php

// "/" is a redirect to the dashboard, which in turn requires auth — so a
// guest hitting the root should land on the login screen.
test('the root url sends guests to login', function () {
    $this->get(route('home'))
        ->assertRedirect(route('dashboard'));

    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});
