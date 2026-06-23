@extends('layouts.main')

@section('titulo','Seleccionar Empresa')

@push('styles')
<style>
.se-wrap {
    min-height: calc(100vh - 60px);
    display: flex; align-items: center; justify-content: center;
    padding: 2.5rem 1rem;
}
.se-card {
    width: 100%; max-width: 480px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.08);
    overflow: hidden;
}
.se-card-header {
    padding: 2rem 1.75rem 1.5rem;
    text-align: center;
}
.se-icon {
    width: 56px; height: 56px; margin: 0 auto 1rem;
    border-radius: 16px;
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.5rem;
    box-shadow: 0 8px 16px -4px rgba(59, 130, 246, 0.4);
}
.se-card-header h1 {
    font-size: 1.25rem; font-weight: 700;
    color: #0f172a; margin: 0 0 0.35rem;
}
.se-card-header p {
    font-size: 0.88rem; color: #64748b; margin: 0;
}
.se-list {
    padding: 0.5rem 1.25rem 1.75rem;
    display: flex; flex-direction: column; gap: 0.6rem;
}
.se-item {
    width: 100%;
    display: flex; align-items: center; gap: 0.85rem;
    padding: 0.85rem 1rem;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 12px;
    text-align: left;
    cursor: pointer;
    transition: all 0.15s ease;
}
.se-item:hover {
    background: #eff6ff;
    border-color: #93c5fd;
    transform: translateY(-1px);
    box-shadow: 0 6px 14px -6px rgba(59, 130, 246, 0.25);
}
.se-item:active { transform: translateY(0); }
.se-avatar {
    flex-shrink: 0;
    width: 40px; height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff; font-weight: 700; font-size: 0.85rem;
    display: flex; align-items: center; justify-content: center;
    letter-spacing: -0.3px;
}
.se-item-body { flex: 1; min-width: 0; }
.se-item-name {
    font-size: 0.92rem; font-weight: 600; color: #0f172a;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.se-item-sub {
    font-size: 0.74rem; color: #94a3b8; margin-top: 1px;
}
.se-item-arrow {
    flex-shrink: 0; color: #94a3b8; font-size: 1rem;
    transition: transform 0.15s ease, color 0.15s ease;
}
.se-item:hover .se-item-arrow { color: #3b82f6; transform: translateX(3px); }

.se-empty {
    text-align: center;
    padding: 1rem 1rem 0.5rem;
}
.se-empty i {
    font-size: 2.2rem; color: #cbd5e1; margin-bottom: 0.75rem; display: block;
}
.se-empty p {
    color: #64748b; font-size: 0.9rem; margin: 0;
}
</style>
@endpush

@section('contenido')

<div class="se-wrap">
  <div class="se-card">

    <div class="se-card-header">
      <div class="se-icon">
        <i class="bi bi-buildings-fill"></i>
      </div>
      <h1>Selecciona tu empresa</h1>
      <p>Elige con cuál empresa deseas trabajar en esta sesión</p>
    </div>

    @if($empresas->isEmpty())

      <div class="se-empty pb-4">
        <i class="bi bi-inboxes"></i>
        <p>No tienes empresas asignadas</p>
      </div>

    @else

      <div class="se-list">
        @foreach($empresas as $empresa)

          <form action="{{ route('cambiar.empresa') }}" method="POST">
            @csrf
            <input type="hidden" name="empresa_id" value="{{ $empresa->id }}">

            <button type="submit" class="se-item">
              <span class="se-avatar">{{ strtoupper(substr($empresa->nombre, 0, 2)) }}</span>
              <span class="se-item-body">
                <span class="se-item-name">{{ $empresa->nombre }}</span>
                <span class="se-item-sub">Haz clic para ingresar</span>
              </span>
              <i class="bi bi-chevron-right se-item-arrow"></i>
            </button>

          </form>

        @endforeach
      </div>

    @endif

  </div>
</div>

@endsection
