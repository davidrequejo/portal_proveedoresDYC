@section('title', 'Iniciar sesi&oacute;n')

<x-guest-layout>
    <link rel="stylesheet" href="{{ asset('adminlte3/plugins/fontawesome-free/css/all.min.css') }}">

    <style>
        .login-page {
            --login-primary: #0e4366;
            --login-primary-dark: #082f4d;
            --login-accent: #1da4d9;
            --login-line: #dce6ec;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 28px;
            background:
                radial-gradient(circle at 8% 12%, rgba(29, 164, 217, .12), transparent 28%),
                linear-gradient(135deg, #f8fbfd 0%, #edf4f8 100%);
        }

        .login-shell {
            display: grid;
            width: min(100%, 1080px);
            min-height: 650px;
            overflow: hidden;
            grid-template-columns: 1.04fr .96fr;
            border: 1px solid rgba(14, 67, 102, .08);
            border-radius: 26px;
            background: #fff;
            box-shadow: 0 24px 65px rgba(14, 67, 102, .14);
        }

        .login-brand {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 54px;
            color: #fff;
            background:
                linear-gradient(145deg, rgba(8, 47, 77, .98), rgba(14, 67, 102, .93)),
                url("{{ asset('assets/images/brand-logos/Mapa-home-768x875.avif') }}") center / cover;
        }

        .login-brand::before,
        .login-brand::after {
            position: absolute;
            content: "";
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 50%;
        }

        .login-brand::before {
            width: 480px;
            height: 480px;
            top: -170px;
            right: -215px;
        }

        .login-brand::after {
            width: 320px;
            height: 320px;
            bottom: -145px;
            left: -130px;
        }

        .login-brand-content {
            position: relative;
            z-index: 1;
            max-width: 390px;
        }

        .login-brand-mark {
            display: inline-flex;
            width: 82px;
            height: 82px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, .20);
            border-radius: 24px;
            background: rgba(255, 255, 255, .10);
            box-shadow: 0 18px 34px rgba(0, 0, 0, .15);
        }

        .login-brand-mark img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .login-brand h1 {
            margin: 28px 0 14px;
            color: #fff;
            font-size: 2.6rem;
            font-weight: 700;
            letter-spacing: -.045em;
            line-height: 1.05;
        }

        .login-brand h1 span {
            display: block;
            color: #6ec9ec;
        }

        .login-brand p {
            max-width: 350px;
            margin: 0;
            color: rgba(255, 255, 255, .72);
            font-size: .96rem;
            line-height: 1.75;
        }

        .login-brand-footer {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 42px;
            color: rgba(255, 255, 255, .55);
            font-size: .68rem;
            letter-spacing: .11em;
            text-transform: uppercase;
        }

        .login-brand-footer img {
            max-height: 28px;
            filter: brightness(0) invert(1);
            opacity: .88;
        }

        .login-form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 54px 56px;
            background: #fff;
        }

        .login-form-content {
            width: 100%;
            max-width: 380px;
        }

        .login-logo {
            width: 220px;
            height: auto;
            margin-bottom: 36px;
        }

        .login-eyebrow {
            color: var(--login-accent);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .login-title {
            margin: 8px 0 8px;
            color: var(--login-primary-dark);
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -.035em;
        }

        .login-subtitle {
            margin: 0 0 28px;
            color: #7a8d9b;
            font-size: .9rem;
            line-height: 1.6;
        }

        .login-field {
            margin-top: 18px;
        }

        .login-label {
            display: block;
            margin-bottom: 7px;
            color: #345063;
            font-size: .78rem;
            font-weight: 700;
        }

        .login-input-wrap {
            position: relative;
        }

        .login-input-wrap i {
            position: absolute;
            top: 50%;
            left: 15px;
            color: #94a7b4;
            font-size: .88rem;
            transform: translateY(-50%);
        }

        .login-input {
            width: 100%;
            height: 48px;
            padding: 0 14px 0 42px;
            border: 1px solid var(--login-line);
            border-radius: 10px;
            color: #244457;
            background: #fbfdfe;
            font-size: .9rem;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .login-input:focus {
            border-color: var(--login-accent);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(29, 164, 217, .12);
        }

        .login-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 18px;
            font-size: .78rem;
        }

        .login-remember {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #718390;
        }

        .login-link {
            color: var(--login-primary);
            font-weight: 700;
            text-decoration: none;
        }

        .login-link:hover {
            color: var(--login-accent);
        }

        .login-submit {
            display: inline-flex;
            width: 100%;
            height: 48px;
            align-items: center;
            justify-content: center;
            gap: 9px;
            margin-top: 24px;
            border: 0;
            border-radius: 10px;
            color: #fff;
            background: var(--login-primary);
            font-size: .84rem;
            font-weight: 700;
            letter-spacing: .02em;
            cursor: pointer;
            transition: background .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .login-submit:hover {
            background: var(--login-primary-dark);
            box-shadow: 0 10px 22px rgba(14, 67, 102, .20);
            transform: translateY(-1px);
        }

        .login-security {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 22px;
            color: #9aaab5;
            font-size: .72rem;
        }

        .login-security i {
            color: var(--login-accent);
        }

        .login-errors {
            margin-bottom: 18px;
            padding: 12px 14px;
            border: 1px solid #f4c9c9;
            border-radius: 9px;
            background: #fff7f7;
            font-size: .78rem;
        }

        @media (max-width: 820px) {
            .login-page { padding: 18px; }
            .login-shell { max-width: 540px; grid-template-columns: 1fr; }
            .login-brand { min-height: 250px; padding: 36px; }
            .login-brand-mark { width: 68px; height: 68px; border-radius: 20px; }
            .login-brand-mark img { width: 50px; height: 50px; }
            .login-brand h1 { margin-top: 19px; font-size: 2.05rem; }
            .login-brand p { font-size: .86rem; }
            .login-brand-footer { display: none; }
            .login-form-panel { padding: 38px 34px; }
            .login-logo { display: none; }
        }

        @media (max-width: 420px) {
            .login-page { padding: 10px; }
            .login-shell { border-radius: 18px; }
            .login-brand { min-height: 220px; padding: 28px 24px; }
            .login-brand h1 { font-size: 1.8rem; }
            .login-brand p { font-size: .8rem; }
            .login-form-panel { padding: 32px 24px; }
            .login-title { font-size: 1.75rem; }
            .login-options { align-items: flex-start; flex-direction: column; }
        }
    </style>

    <main class="login-page">
        <section class="login-shell">
            <div class="login-brand">
                <div class="login-brand-content">
                    <div class="login-brand-mark">
                        <img src="{{ asset('assets/images/brand-logos/dc-logo_cirsulo_white.png') }}" alt="D&C">
                    </div>
                    <h1>
                        Portal de
                        <span>Homologaci&oacute;n</span>
                    </h1>
                    <p>
                        Gestiona informaci&oacute;n, documentos y procesos de homologaci&oacute;n
                        desde un entorno centralizado y seguro.
                    </p>
                    <a class="login-brand-footer" href="https://optimiza360.pe/" target="_blank" rel="noopener noreferrer">
                        Desarrollado por
                        <img src="{{ asset('assets/images/brand-logos/logo-principal-optimiza.png') }}" alt="Optimiza 360">
                    </a>
                </div>
            </div>

            <div class="login-form-panel">
                <div class="login-form-content">
                    <img
                        class="login-logo"
                        src="{{ asset('assets/images/brand-logos/logo-grpo-inmobiliario-dc_dark.svg') }}"
                        alt="D&C Grupo Inmobiliario"
                    >

                    <span class="login-eyebrow">Acceso al portal</span>
                    <h2 class="login-title">Iniciar sesi&oacute;n</h2>
                    <p class="login-subtitle">Ingresa tus credenciales para continuar.</p>

                    <x-validation-errors class="login-errors" />

                    @session('status')
                        <div class="mb-4 text-sm text-green-600">
                            {{ $value }}
                        </div>
                    @endsession

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="login-field">
                            <label class="login-label" for="email">Usuario</label>
                            <div class="login-input-wrap">
                                <i class="fas fa-user"></i>
                                <input
                                    class="login-input"
                                    id="email"
                                    type="text"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="Ingresa tu usuario"
                                    required
                                    autofocus
                                    autocomplete="username"
                                >
                            </div>
                        </div>

                        <div class="login-field">
                            <label class="login-label" for="password">Contrase&ntilde;a</label>
                            <div class="login-input-wrap">
                                <i class="fas fa-lock"></i>
                                <input
                                    class="login-input"
                                    id="password"
                                    type="password"
                                    name="password"
                                    placeholder="Ingresa tu contrase&ntilde;a"
                                    required
                                    autocomplete="current-password"
                                >
                            </div>
                        </div>

                        <div class="login-options">
                            <label class="login-remember" for="remember_me">
                                <input id="remember_me" type="checkbox" name="remember">
                                <span>Recordarme</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="login-link" href="{{ route('password.request') }}">
                                    &iquest;Olvidaste tu contrase&ntilde;a?
                                </a>
                            @endif
                        </div>

                        <button class="login-submit" type="submit">
                            Ingresar al portal
                            <i class="fas fa-arrow-right"></i>
                        </button>

                        <p class="login-security">
                            <i class="fas fa-shield-alt"></i>
                            Acceso protegido para usuarios autorizados.
                        </p>
                    </form>
                </div>
            </div>
        </section>
    </main>
</x-guest-layout>
