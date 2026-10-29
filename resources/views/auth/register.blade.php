@extends('layouts.app')

@section('title', 'Create Account — Alexa')
@section('meta_description', 'Create your Alexa account and start exploring premium, minimal insulated drinkware.')

@section('content')
<div class="container-custom" style="padding-top: 5rem; padding-bottom: 7rem; max-width: 480px;">
    <div style="background: #FFFFFF; border: 1px solid var(--color-border); padding: 3rem 2.5rem;">
        <span class="eyebrow" style="text-align: center; display: block;">New Account</span>
        <h1 class="serif" style="font-size: 2rem; font-weight: 400; text-align: center; margin-bottom: 2rem; letter-spacing: -0.01em;">
            Join Alexa
        </h1>

        @if ($errors->any())
            <div style="background: #FDF2F2; border: 1px solid #F8D7DA; color: #842029; padding: 0.875rem 1rem; font-size: 0.8125rem; margin-bottom: 1.5rem; border-radius: 2px;">
                <ul style="margin: 0; padding-left: 1.1rem; display: flex; flex-direction: column; gap: 0.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" id="register-form">
            @csrf

            <div style="margin-bottom: 1.25rem;">
                <label for="name" style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); margin-bottom: 0.5rem;">
                    Full Name
                </label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Your full name"
                    style="width: 100%; padding: 0.875rem 1rem; border: 1px solid {{ $errors->has('name') ? '#F8D7DA' : 'var(--color-border)' }}; font-size: 0.875rem; font-family: var(--font-sans); outline: none; transition: border-color 0.2s ease; background: #fff; color: var(--color-text-main);"
                    onfocus="this.style.borderColor='var(--color-text-main)'"
                    onblur="this.style.borderColor='{{ $errors->has('name') ? '#F8D7DA' : 'var(--color-border)' }}'">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="email" style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); margin-bottom: 0.5rem;">
                    Email Address
                </label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    placeholder="you@example.com"
                    style="width: 100%; padding: 0.875rem 1rem; border: 1px solid {{ $errors->has('email') ? '#F8D7DA' : 'var(--color-border)' }}; font-size: 0.875rem; font-family: var(--font-sans); outline: none; transition: border-color 0.2s ease; background: #fff; color: var(--color-text-main);"
                    onfocus="this.style.borderColor='var(--color-text-main)'"
                    onblur="this.style.borderColor='{{ $errors->has('email') ? '#F8D7DA' : 'var(--color-border)' }}'">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="password" style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); margin-bottom: 0.5rem;">
                    Password
                </label>
                <div style="position: relative;">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        autocomplete="new-password"
                        placeholder="Min. 8 chars, uppercase &amp; number"
                        style="width: 100%; padding: 0.875rem 2.75rem 0.875rem 1rem; border: 1px solid {{ $errors->has('password') ? '#F8D7DA' : 'var(--color-border)' }}; font-size: 0.875rem; font-family: var(--font-sans); outline: none; transition: border-color 0.2s ease; background: #fff; color: var(--color-text-main);"
                        onfocus="this.style.borderColor='var(--color-text-main)'"
                        onblur="this.style.borderColor='{{ $errors->has('password') ? '#F8D7DA' : 'var(--color-border)' }}'">
                    <button type="button" id="toggle-password"
                        onclick="togglePasswordVisibility('password', 'eye-icon-pw')"
                        aria-label="Toggle password visibility"
                        style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); background: none; border: none; cursor: pointer; padding: 0; display: flex; align-items: center;">
                        <svg id="eye-icon-pw" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                <p style="font-size: 0.725rem; color: var(--color-text-subtle); margin-top: 0.375rem; letter-spacing: 0.02em;">
                    At least 8 characters, one uppercase letter, and one number.
                </p>
            </div>

            <div style="margin-bottom: 2rem;">
                <label for="password_confirmation" style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); margin-bottom: 0.5rem;">
                    Confirm Password
                </label>
                <div style="position: relative;">
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Repeat your password"
                        style="width: 100%; padding: 0.875rem 2.75rem 0.875rem 1rem; border: 1px solid {{ $errors->has('password_confirmation') ? '#F8D7DA' : 'var(--color-border)' }}; font-size: 0.875rem; font-family: var(--font-sans); outline: none; transition: border-color 0.2s ease; background: #fff; color: var(--color-text-main);"
                        onfocus="this.style.borderColor='var(--color-text-main)'"
                        onblur="this.style.borderColor='{{ $errors->has('password_confirmation') ? '#F8D7DA' : 'var(--color-border)' }}'">
                    <button type="button" id="toggle-confirm-password"
                        onclick="togglePasswordVisibility('password_confirmation', 'eye-icon-confirm')"
                        aria-label="Toggle confirm password visibility"
                        style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); background: none; border: none; cursor: pointer; padding: 0; display: flex; align-items: center;">
                        <svg id="eye-icon-confirm" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-editorial btn-editorial-primary" style="width: 100%;" id="btn-submit-register">
                Create Account
            </button>
        </form>

        <div style="display: flex; align-items: center; gap: 1rem; margin-top: 2rem; margin-bottom: 1.5rem;">
            <div style="flex: 1; height: 1px; background: var(--color-border);"></div>
            <span style="font-size: 0.75rem; color: var(--color-text-subtle); text-transform: uppercase; letter-spacing: 0.08em;">or</span>
            <div style="flex: 1; height: 1px; background: var(--color-border);"></div>
        </div>

        <p style="text-align: center; font-size: 0.8125rem; color: var(--color-text-muted);">
            Already have an account?
            <a href="{{ route('login') }}" id="link-sign-in"
               style="color: var(--color-text-main); font-weight: 500; text-decoration: underline; text-underline-offset: 3px;">
                Sign In
            </a>
        </p>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    icon.innerHTML = isHidden
        ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>'
        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
}
</script>
@endsection
