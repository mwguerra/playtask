<?php

namespace App\Livewire;

use App\Models\BetaSignup;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class LandingPage extends Component
{
    #[Validate('required|email:rfc|max:255|unique:beta_signups,email')]
    public string $email = '';

    public bool $signedUp = false;

    public function subscribe(): void
    {
        $key = 'beta-signup:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Muitas tentativas. Tente novamente em alguns minutos.');

            return;
        }

        $this->validate();

        BetaSignup::create([
            'email' => $this->email,
            'ip' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);

        RateLimiter::hit($key, 60);

        $this->signedUp = true;
        $this->email = '';
    }

    public function render()
    {
        return view('livewire.landing-page');
    }
}
