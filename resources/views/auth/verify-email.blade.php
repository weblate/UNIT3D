@extends('layout.guest')

@section('title')
    <title>{{ __('auth.verify-email') }} - {{ config('other.title') }}</title>
@endsection

@section('content')
    <section class="auth-form">
        <form class="auth-form__form" method="POST" action="{{ route('verification.send') }}">
            @csrf
            <a class="auth-form__branding" href="{{ route('home.index') }}">
                <i class="fal fa-tv-retro"></i>
                <span class="auth-form__site-logo">{{ \config('other.title') }}</span>
            </a>
            <ul class="auth-form__important-infos">
                <li class="auth-form__important-info">Almost done...</li>
                <li class="auth-form__important-info">
                    Click the verification link sent to your email to activate your account.
                </li>
            </ul>
            @if (config('captcha.enabled'))
                @hiddencaptcha
            @endif

            <details class="auth-form__dropdown">
                <summary class="auth-form__dropdown-text">Having issues?</summary>
                <button class="auth-form__primary-button">Resend verification email</button>
            </details>
            @if (Session::has('status'))
                <ul class="auth-form__errors">
                    @if (session('status') == 'verification-link-sent')
                        <li class="auth-form__error">
                            {{ __('auth.email-verification-link') }}
                        </li>
                    @endif
                </ul>
            @endif
        </form>
    </section>
@endsection
