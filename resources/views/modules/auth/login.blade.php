@extends('layouts.login')
@section('titulo', 'Iniciar Sesión — Segura Tech')
@section('contenido')

<style>
  *, *::before, *::after { box-sizing: border-box; }

  body {
    font-family: 'Poppins', sans-serif;
    background: #0f172a;
  }

  /* ── Wrapper principal ── */
  .st-login-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: stretch;
  }

  /* ── Panel izquierdo (marca) ── */
  .st-brand {
    flex: 1;
    background: linear-gradient(145deg, #0f172a 0%, #1e3a5f 50%, #0c4a6e 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    position: relative;
    overflow: hidden;
  }

  .st-brand::before {
    content: '';
    position: absolute;
    width: 700px; height: 700px;
    background: radial-gradient(circle, rgba(14,165,233,.15) 0%, transparent 70%);
    top: -200px; left: -200px;
    border-radius: 50%;
  }

  .st-brand::after {
    content: '';
    position: absolute;
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(56,189,248,.1) 0%, transparent 70%);
    bottom: -150px; right: -150px;
    border-radius: 50%;
  }

  .st-brand-content { position: relative; z-index: 1; text-align: center; }

  .st-brand-logo {
    width: 100px;
    margin-bottom: 1.75rem;
    filter: drop-shadow(0 0 30px rgba(14,165,233,.6));
    animation: float 4s ease-in-out infinite;
  }

  @keyframes float {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-8px); }
  }

  .st-brand h1 {
    color: #fff;
    font-size: 2.5rem;
    font-weight: 700;
    letter-spacing: -0.5px;
    margin-bottom: .5rem;
  }

  .st-brand p {
    color: #94a3b8;
    font-size: 1rem;
    margin-bottom: 2.5rem;
  }

  .st-brand-features {
    display: flex;
    gap: 2.5rem;
    justify-content: center;
  }

  .st-feature {
    text-align: center;
    color: rgba(255,255,255,.55);
  }

  .st-feature i {
    font-size: 1.6rem;
    display: block;
    margin-bottom: .4rem;
    color: #38bdf8;
  }

  .st-feature span { font-size: .78rem; }

  /* Divisor decorativo */
  .st-divider {
    width: 60px; height: 3px;
    background: linear-gradient(90deg, #0ea5e9, #38bdf8);
    border-radius: 2px;
    margin: 1.5rem auto;
  }

  /* ── Panel derecho (formulario) ── */
  .st-form-panel {
    width: 460px;
    flex-shrink: 0;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 3.5rem;
  }

  .st-form-inner { width: 100%; max-width: 360px; }

  /* Logo mobile */
  .st-mobile-logo {
    display: none;
    align-items: center;
    gap: .75rem;
    margin-bottom: 2rem;
  }

  .st-mobile-logo img { height: 38px; }
  .st-mobile-logo span { font-size: 1.3rem; font-weight: 700; color: #0f172a; }

  /* Cabecera del form */
  .st-form-header h2 {
    font-size: 1.7rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: .35rem;
  }

  .st-form-header p {
    font-size: .875rem;
    color: #64748b;
    margin-bottom: 0;
  }

  /* Labels */
  .st-label {
    font-size: .8rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: .4rem;
    display: block;
  }

  /* Inputs con icono */
  .st-input-wrap {
    position: relative;
  }

  .st-input-wrap .st-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: .95rem;
    pointer-events: none;
    transition: color .25s;
  }

  .st-input-wrap .form-control {
    padding: .75rem 1rem .75rem 2.75rem;
    border-radius: 10px;
    border: 1.5px solid #e2e8f0;
    font-size: .9rem;
    color: #1e293b;
    background: #f8fafc;
    transition: all .25s;
    font-family: 'Poppins', sans-serif;
  }

  .st-input-wrap .form-control:focus {
    border-color: #0ea5e9;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(14,165,233,.12);
    outline: none;
  }

  .st-input-wrap .form-control:focus + .st-icon,
  .st-input-wrap:focus-within .st-icon { color: #0ea5e9; }

  /* Ícono de mostrar/ocultar contraseña */
  .st-toggle-pwd {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 0;
    font-size: .95rem;
    transition: color .2s;
  }

  .st-toggle-pwd:hover { color: #0ea5e9; }

  /* Botón principal */
  .st-btn-login {
    width: 100%;
    padding: .85rem;
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    border: none;
    border-radius: 10px;
    color: #fff;
    font-family: 'Poppins', sans-serif;
    font-size: .95rem;
    font-weight: 600;
    letter-spacing: .3px;
    cursor: pointer;
    transition: all .3s;
    box-shadow: 0 4px 15px rgba(14,165,233,.35);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
  }

  .st-btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(14,165,233,.5);
  }

  .st-btn-login:active { transform: translateY(0); }

  /* Bloque de errores */
  .st-errors {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-left: 4px solid #ef4444;
    border-radius: 10px;
    padding: .8rem 1rem;
    font-size: .83rem;
    color: #dc2626;
  }

  .st-errors i { margin-right: .4rem; }

  /* Footer */
  .st-footer {
    margin-top: 2.5rem;
    text-align: center;
    font-size: .75rem;
    color: #94a3b8;
  }

  .st-footer a { color: #0ea5e9; text-decoration: none; }
  .st-footer a:hover { text-decoration: underline; }

  /* ── Responsive ── */
  @media (max-width: 768px) {
    .st-brand { display: none !important; }
    .st-form-panel { width: 100%; padding: 2.5rem 1.75rem; }
    .st-mobile-logo { display: flex; }
  }
</style>

<div class="st-login-wrapper">

  {{-- ═══════════════ Panel Izquierdo — Marca ═══════════════ --}}
  <div class="st-brand d-none d-md-flex flex-column align-items-center justify-content-center">
    <div class="st-brand-content">
      
      <h1>Segura Tech</h1>
      <div class="st-divider"></div>
      <p>Sistema de Gestión Integral</p>
      <div class="st-brand-features">
        <div class="st-feature">
          <i class="bi bi-shield-check"></i>
          <span>Seguro</span>
        </div>
        <div class="st-feature">
          <i class="bi bi-lightning-charge"></i>
          <span>Eficiente</span>
        </div>
        <div class="st-feature">
          <i class="bi bi-people"></i>
          <span>Confiable</span>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════ Panel Derecho — Formulario ═══════════════ --}}
  <div class="st-form-panel">
    <div class="st-form-inner">

      {{-- Logo solo en móvil --}}
      <div class="st-mobile-logo">
        <img src="{{ asset('assets/img/logo.png') }}" alt="Logo">
        <span>Segura Tech</span>
      </div>

      {{-- Cabecera --}}
      <div class="st-form-header mb-4">
        <h2>Bienvenido</h2>
        <p>Ingresa tus credenciales para acceder al sistema</p>
      </div>

      {{-- Errores de autenticación --}}
      @if ($errors->any())
        <div class="st-errors mb-4">
          <i class="bi bi-exclamation-circle-fill"></i>
          @foreach ($errors->all() as $error)
            <span class="d-block">{{ $error }}</span>
          @endforeach
        </div>
      @endif

      {{-- Formulario --}}
      <form method="POST" action="{{ route('logear') }}" novalidate>
        @csrf

        {{-- Email --}}
        <div class="mb-3">
          <label for="email" class="st-label">Correo electrónico</label>
          <div class="st-input-wrap">
            <input type="email" name="email" id="email"
                   class="form-control"
                   placeholder="usuario@empresa.com"
                   value="{{ old('email') }}"
                   autocomplete="email"
                   required autofocus>
            <i class="bi bi-envelope st-icon"></i>
          </div>
        </div>

        {{-- Contraseña --}}
        <div class="mb-4">
          <label for="password" class="st-label">Contraseña</label>
          <div class="st-input-wrap">
            <input type="password" name="password" id="password"
                   class="form-control"
                   placeholder="••••••••"
                   autocomplete="current-password"
                   required>
            <i class="bi bi-lock st-icon"></i>
            <button type="button" class="st-toggle-pwd" onclick="stTogglePwd()" tabindex="-1">
              <i class="bi bi-eye" id="st-eye-icon"></i>
            </button>
          </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="st-btn-login">
          <i class="bi bi-box-arrow-in-right"></i>
          Iniciar Sesión
        </button>

      </form>

      <div class="st-footer">
        &copy; {{ date('Y') }} Segura Tech &mdash; Desarrollado por
        <a href="#">Edalfo</a>
      </div>

    </div>
  </div>

</div>

<script>
  function stTogglePwd() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('st-eye-icon');
    const isHidden = input.type === 'password';
    input.type      = isHidden ? 'text'     : 'password';
    icon.className  = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
  }
</script>

@endsection
