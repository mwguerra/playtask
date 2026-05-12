<?php

use App\Livewire\LandingPage;
use App\Models\BetaSignup;
use Livewire\Livewire;

test('a landing page renders', function () {
    $this->get('/')->assertOk()->assertSeeText('PlayTask');
});

test('a visitor can submit their email to join the beta', function () {
    Livewire::test(LandingPage::class)
        ->set('email', 'novo@exemplo.com')
        ->call('subscribe')
        ->assertHasNoErrors()
        ->assertSet('signedUp', true);

    expect(BetaSignup::where('email', 'novo@exemplo.com')->exists())->toBeTrue();
});

test('duplicate emails are rejected', function () {
    BetaSignup::factory()->create(['email' => 'jaexiste@exemplo.com']);

    Livewire::test(LandingPage::class)
        ->set('email', 'jaexiste@exemplo.com')
        ->call('subscribe')
        ->assertHasErrors(['email']);
});

test('invalid emails are rejected', function () {
    Livewire::test(LandingPage::class)
        ->set('email', 'not-an-email')
        ->call('subscribe')
        ->assertHasErrors(['email']);
});
