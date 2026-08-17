<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app-url" content="{{ url('/') }}">
  <title>Inicio | Portal</title>

  <link rel="icon" href="{{ asset('assets/images/brand-logos/dc-logo_cirsulo_white.png') }}" type="image/png">

  @include('layouts.lte_head')

  <style>
    .home-page {
      --home-primary: #0e4366;
      --home-primary-dark: #082f4d;
      --home-accent: #1da4d9;
      --home-soft: #edf7fb;
      min-height: calc(100vh - 112px);
      padding: 28px;
      background:
        radial-gradient(circle at 10% 10%, rgba(29, 164, 217, .10), transparent 30%),
        linear-gradient(135deg, #f8fbfd 0%, #eef4f8 100%);
    }

    .home-hero {
      position: relative;
      display: flex;
      max-width: 1440px;
      min-height: calc(100vh - 168px);
      margin: 0 auto;
      overflow: hidden;
      border: 1px solid rgba(14, 67, 102, .08);
      border-radius: 28px;
      background: rgba(255, 255, 255, .94);
      box-shadow: 0 22px 60px rgba(14, 67, 102, .12);
    }

    .home-layout {
      display: flex;
      width: 100%;
      min-height: inherit;
      align-items: stretch;
    }

    .home-layout > [class*="col-"] {
      display: flex;
      flex-direction: column;
    }

    .home-copy {
      display: flex;
      flex: 1;
      flex-direction: column;
      justify-content: center;
      padding: 68px 62px 42px;
    }

    .home-eyebrow {
      display: inline-flex;
      align-items: center;
      padding: 8px 14px;
      border-radius: 999px;
      color: var(--home-primary);
      background: var(--home-soft);
      font-size: .76rem;
      font-weight: 700;
      letter-spacing: .12em;
      text-transform: uppercase;
    }

    .home-title {
      max-width: 680px;
      margin: 24px 0 18px;
      color: var(--home-primary-dark);
      font-size: clamp(2.65rem, 5vw, 5.25rem);
      font-weight: 700;
      letter-spacing: -.055em;
      line-height: .98;
    }

    .home-title span {
      display: block;
      color: var(--home-accent);
    }

    .home-description {
      max-width: 610px;
      margin-bottom: 28px;
      color: #6b7e8d;
      font-size: 1.05rem;
      line-height: 1.75;
    }

    .home-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 40px;
    }

    .home-action {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      padding: 11px 18px;
      border: 1px solid rgba(14, 67, 102, .12);
      border-radius: 12px;
      color: var(--home-primary);
      background: #fff;
      font-size: .9rem;
      font-weight: 700;
    }

    .home-features {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 12px;
      max-width: 720px;
    }

    .home-feature {
      padding: 16px;
      border: 1px solid rgba(14, 67, 102, .08);
      border-radius: 16px;
      background: #fbfdfe;
    }

    .home-feature i {
      color: var(--home-accent);
      font-size: 1.35rem;
    }

    .home-feature strong {
      display: block;
      margin-top: 10px;
      color: var(--home-primary-dark);
      font-size: .9rem;
    }

    .home-feature span {
      display: block;
      margin-top: 3px;
      color: #8595a2;
      font-size: .76rem;
      line-height: 1.4;
    }

    .home-brand {
      position: relative;
      display: flex;
      min-height: inherit;
      flex: 1;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      padding: 48px;
      text-align: center;
      background:
        linear-gradient(145deg, rgba(8, 47, 77, .98), rgba(14, 67, 102, .93)),
        url("{{ asset('assets/images/brand-logos/Mapa-home-768x875.avif') }}") center / cover;
    }

    .home-brand::before,
    .home-brand::after {
      position: absolute;
      content: "";
      border: 1px solid rgba(255, 255, 255, .10);
      border-radius: 50%;
    }

    .home-brand::before {
      width: 520px;
      height: 520px;
      top: -180px;
      right: -230px;
    }

    .home-brand::after {
      width: 340px;
      height: 340px;
      bottom: -150px;
      left: -130px;
    }

    .home-brand-content {
      position: relative;
      z-index: 1;
    }

    .home-brand-mark {
      display: inline-flex;
      width: 86px;
      height: 86px;
      align-items: center;
      justify-content: center;
      border: 1px solid rgba(255, 255, 255, .22);
      border-radius: 26px;
      background: rgba(255, 255, 255, .10);
      box-shadow: 0 18px 32px rgba(0, 0, 0, .15);
    }

    .home-brand-mark img {
      width: 62px;
      height: 62px;
      object-fit: contain;
    }

    .home-brand h2 {
      margin: 28px 0 8px;
      color: #fff;
      font-size: 1.8rem;
      font-weight: 700;
    }

    .home-brand p {
      max-width: 320px;
      margin: 0 auto;
      color: rgba(255, 255, 255, .70);
      font-size: .95rem;
      line-height: 1.65;
    }

    .home-brand-logo {
      width: min(100%, 360px);
      margin-top: 36px;
      padding: 14px 18px;
      border-radius: 14px;
      background: rgba(255, 255, 255, .92);
    }

    .home-powered {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      margin-top: 28px;
      color: rgba(255, 255, 255, .60);
      font-size: .72rem;
      letter-spacing: .08em;
      text-transform: uppercase;
    }

    .home-powered img {
      max-height: 28px;
      filter: brightness(0) invert(1);
      opacity: .92;
    }

    @media (max-width: 991.98px) {
      .home-page { padding: 18px; }
      .home-hero { min-height: auto; }
      .home-layout { min-height: auto; }
      .home-copy { padding: 48px 34px 30px; }
      .home-brand { min-height: 360px; padding: 42px 28px; }
    }

    @media (max-width: 575.98px) {
      .home-page { padding: 12px; }
      .home-hero { border-radius: 20px; }
      .home-copy { padding: 34px 22px 22px; }
      .home-title { font-size: 3rem; }
      .home-features { grid-template-columns: 1fr; }
      .home-feature { padding: 13px 15px; }
      .home-feature i { float: left; margin: 3px 12px 10px 0; }
      .home-feature strong { margin-top: 0; }
      .home-brand { min-height: 330px; }
    }
  </style>
</head>
<body class="hold-transition sidebar-mini sidebar-collapse layout-fixed">
  <div class="wrapper">
    @include('layouts.lte_preloader')
    @include('layouts.lte_nav')
    @include('layouts.lte_aside')

    <div class="content-wrapper">
      <section class="home-page">
        <div class="home-hero">
          <div class="row no-gutters home-layout">
            <div class="col-lg-7">
              <div class="home-copy">
                <span class="home-eyebrow"><i class="fas fa-shield-alt mr-2"></i> Portal corporativo</span>

                <h1 class="home-title">
                  Bienvenido al Portal
                  <span>de Homologaci&oacute;n</span>
                </h1>

                <p class="home-description">
                  Centralizamos la informaci&oacute;n de clientes, proveedores y documentaci&oacute;n
                  para que cada proceso sea m&aacute;s simple, ordenado y transparente.
                </p>

                <div class="home-features">
                  <div class="home-feature">
                    <i class="fas fa-file-signature"></i>
                    <strong>Documentaci&oacute;n</strong>
                    <span>Informaci&oacute;n organizada y disponible en un solo lugar.</span>
                  </div>
                  <div class="home-feature">
                    <i class="fas fa-route"></i>
                    <strong>Seguimiento</strong>
                    <span>Consulta el avance de cada proceso de homologaci&oacute;n.</span>
                  </div>
                  <div class="home-feature">
                    <i class="fas fa-lock"></i>
                    <strong>Acceso seguro</strong>
                    <span>Gesti&oacute;n confiable para clientes y proveedores.</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-5">
              <div class="home-brand">
                <div class="home-brand-content">
                  <div class="home-brand-mark">
                    <img src="{{ asset('assets/images/brand-logos/dc-logo_cirsulo_white.png') }}" alt="D&C">
                  </div>
                  <h2>Grupo Inmobiliario D&C</h2>
                  <p>Un espacio digital pensado para acompa&ntilde;ar y simplificar tus gestiones.</p>
                  <img
                    src="{{ asset('assets/images/brand-logos/logo-grpo-inmobiliario-dc_dark.svg') }}"
                    alt="D&C Grupo Inmobiliario"
                    class="home-brand-logo"
                  >
                  <a class="home-powered" href="https://optimiza360.pe/" target="_blank" rel="noopener noreferrer">
                    Desarrollado por
                    <img src="{{ asset('assets/images/brand-logos/logo-principal-optimiza.png') }}" alt="Optimiza 360">
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    @include('layouts.lte_footer')

    <aside class="control-sidebar control-sidebar-dark">
    </aside>
  </div>

  @include('layouts.lte_script')
</body>
</html>
