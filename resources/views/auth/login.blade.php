@extends('layouts.app')

@section('title', 'Sign In — Alexa')

@section('content')
<div class="container-custom" style="padding-top: 5rem; padding-bottom: 7rem; max-width: 480px;">
    <div style="background: #FFFFFF; border: 1px solid var(--color-border); padding: 3rem 2.5rem;">
        <span class="eyebrow" style="text-align: center; display: block;">Account Access</span>
        <h1 class="serif" style="font-size: 2rem; font-weight: 400; text-align: center; margin-bottom: 2rem; letter-spacing: -0.01em;">
            Sign In to Alexa
        </h1>

        @if($errors->any())
            <div style="background: #FDF2F2; border: 1px solid #F8D7DA; color: #842029; padding: 0.875rem 1rem; font-size: 0.8125rem; margin-bottom: 1.5rem; border-radius: 2px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label for="email" style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); margin-bottom: 0.5rem;">
                    Email Address
                </label>
                <input type="email" name="email" id="email" value="{{ old('email', 'test@example.com') }}" required autofocus
                       style="width: 100%; padding: 0.875rem 1rem; border: 1px solid var(--color-border); font-size: 0.875rem; font-family: var(--font-sans); outline: none;">
            </div>

            <div style="margin-bottom: 2rem;">
                <label for="password" style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); margin-bottom: 0.5rem;">
                    Password
                </label>
                <input type="password" name="password" id="password" value="password" required
                       style="width: 100%; padding: 0.875rem 1rem; border: 1px solid var(--color-border); font-size: 0.875rem; font-family: var(--font-sans); outline: none;">
            </div>

            <button type="submit" class="btn-editorial btn-editorial-primary" style="width: 100%;" id="btn-submit-login">
                Sign In
            </button>
        </form>

        <div style="display: flex; align-items: center; gap: 1rem; margin-top: 2rem; margin-bottom: 1.5rem;">
            <div style="flex: 1; height: 1px; background: var(--color-border);"></div>
            <span style="font-size: 0.75rem; color: var(--color-text-subtle); text-transform: uppercase; letter-spacing: 0.08em;">or</span>
            <div style="flex: 1; height: 1px; background: var(--color-border);"></div>
        </div>

        <p style="text-align: center; font-size: 0.8125rem; color: var(--color-text-muted);">
            New to Alexa?
            <a href="{{ route('register') }}" id="link-create-account"
               style="color: var(--color-text-main); font-weight: 500; text-decoration: underline; text-underline-offset: 3px;">
                Create an Account
            </a>
        </p>
    </div>
</div>
@endsection

