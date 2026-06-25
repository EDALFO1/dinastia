<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Domains\Payroll\Controllers\{
    AfiliacionController,
    AfiliadoController,
    AfiliadoServicioController,
    ArlAfiliadoController,
    ExportBatchController,
    IncapacidadController,
    PeriodoAfiliadoController,
    ReciboAfiliacionController,
    ReciboController,
    ReciboDetalleController,
    RemisionController,
    RemisionDetalleController,
};

use App\Domains\Shared\Controllers\{
    ArlController,
    AsesorController,
    AuthController,
    CajaController,
    DocumentoController,
    EmpresaClaveController,
    EmpresaController,
    EmpresaExternaController,
    EmpresaLaboralController,
    EpsController,
    ModuloEmpresaController,
    ModuloRolController,
    NotaController,
    ParametroAnualController,
    PensionController,
    PlanController,
    RolController,
    ServicioController,
    ServicioExternoController,
    SubtipoCotizanteController,
    UserController,
};

/*
|--------------------------------------------------------------------------
| RUTAS PUBLICAS
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect()->route('dashboard'));

Route::view('/login', 'modules.auth.login')->name('login');

Route::post('/logear', [AuthController::class, 'logear'])->name('logear');

Route::get('/force-logout', function (Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->middleware('web');


/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |-----------------------------------
    | GENERALES (todos los roles)
    |-----------------------------------
    */

    Route::view('/dashboard', 'modules.dashboard.home')->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('seleccionar-empresa', [AuthController::class, 'seleccionarEmpresa'])->name('seleccionar.empresa');
    Route::post('cambiar-empresa', [AuthController::class, 'cambiarEmpresa'])->name('cambiar.empresa');


    /*
    |-----------------------------------
    | TODOS LOS USUARIOS AUTENTICADOS
    | admin(1), operador(2), asesor(3)
    |-----------------------------------
    */

    // Afiliados ARL exclusivo
    Route::middleware('empresa')->group(function () {
        Route::get('/arl-afiliados/buscar', [ArlAfiliadoController::class, 'buscar'])->name('arl-afiliados.buscar');
        Route::resource('arl-afiliados', ArlAfiliadoController::class)
            ->parameters(['arl-afiliados' => 'arl_afiliado']);
    });

    // Afiliados, afiliaciones, servicios por afiliado y empresas laborales
    Route::middleware('empresa')->group(function () {
        Route::post('/afiliados/importar', [AfiliadoController::class, 'importar'])->name('afiliados.importar');
        Route::get('/afiliados/plantilla', [AfiliadoController::class, 'descargarPlantilla'])->name('afiliados.plantilla');
        Route::get('/afiliados/buscar', [AfiliadoController::class, 'buscar'])->name('afiliados.buscar');
        Route::get('afiliado-servicio/{id}', [RemisionController::class, 'valorServicio']);
        Route::resource('afiliados', AfiliadoController::class)->parameters(['afiliados' => 'afiliado']);
        Route::get('/afiliaciones/plantilla', [AfiliacionController::class, 'descargarPlantilla'])->name('afiliaciones.plantilla');
        Route::post('/afiliaciones/importar', [AfiliacionController::class, 'importar'])->name('afiliaciones.importar');
        Route::resource('afiliaciones', AfiliacionController::class)->parameters(['afiliaciones' => 'afiliacion']);
        Route::resource('afiliado_servicios', AfiliadoServicioController::class)->parameters(['afiliado_servicios' => 'afiliado_servicio']);

        // Empresa laboral
        Route::resource('empresas_laborales', EmpresaLaboralController::class)->parameters(['empresas_laborales' => 'empresa_laboral']);
    });

    // Incapacidades
    Route::middleware('empresa')->group(function () {
        Route::resource('incapacidades', IncapacidadController::class);
        Route::post('incapacidades/{incapacidad}/observacion', [IncapacidadController::class, 'agregarObservacion'])->name('incapacidades.observacion');
        Route::delete('incapacidad-observaciones/{incapacidad_observacion}', function (\App\Models\IncapacidadObservacion $incapacidad_observacion) {
            $incapacidad = $incapacidad_observacion->incapacidad_id;
            $incapacidad_observacion->delete();
            return redirect()->route('incapacidades.show', $incapacidad)->with('success', 'Observación eliminada.');
        })->name('incapacidad_observaciones.destroy');
    });

    // Remisiones
    Route::middleware('empresa')->group(function () {
        Route::resource('remisiones', RemisionController::class);
        Route::get('/buscar-afiliados',              [RemisionController::class, 'buscarAfiliados'])->name('buscar.afiliados');
        Route::get('calcular-dias/{afiliado}/{fecha}',[RemisionController::class, 'calcularDias']);
        Route::post('remisiones/preview',            [RemisionController::class, 'preview'])->name('remisiones.preview');
        Route::get('remisiones/{id}/imprimir',       [RemisionController::class, 'imprimir'])->name('remisiones.imprimir');
        Route::resource('remision_detalles', RemisionDetalleController::class);
    });

    // Notas, Planes y Claves por empresa
    Route::resource('planes', PlanController::class)->parameters(['planes' => 'plan']);
    Route::resource('notas', NotaController::class);
    Route::post('notas/{nota}/resolver', [NotaController::class, 'resolver'])->name('notas.resolver');
    Route::post('notas/{nota}/reabrir',  [NotaController::class, 'reabrir'])->name('notas.reabrir');
    Route::prefix('empresa-claves')->as('empresa-claves.')->middleware('empresa')->group(function () {
        Route::get('/',                   [EmpresaClaveController::class, 'index'])->name('index');
        Route::get('/create',             [EmpresaClaveController::class, 'create'])->name('create');
        Route::post('/',                  [EmpresaClaveController::class, 'store'])->name('store');
        Route::get('/{empresaClave}/edit',[EmpresaClaveController::class, 'edit'])->name('edit');
        Route::put('/{empresaClave}',     [EmpresaClaveController::class, 'update'])->name('update');
        Route::delete('/{empresaClave}',  [EmpresaClaveController::class, 'destroy'])->name('destroy');
    });


    /*
    |-----------------------------------
    | RECIBOS (modulo:recibos)
    |-----------------------------------
    */

    Route::middleware(['empresa', 'modulo:recibos,recibos_afiliacion'])->group(function () {
        Route::prefix('recibos')->name('recibos.')->group(function () {
            Route::post('preview',              [ReciboController::class, 'preview'])->name('preview');
            Route::post('generar',              [ReciboController::class, 'generar'])->name('generar');
            Route::get('activos-siguiente',     [ReciboController::class, 'activosSiguientePeriodo'])->name('activos');
            Route::get('sin-recibo',            [ReciboController::class, 'usuariosSinRecibo'])->name('sin_recibo');
            Route::post('cerrar-periodo',       [ReciboController::class, 'cerrarPeriodo'])->name('cerrar_periodo');
            Route::post('generar/{afiliado}',   [ReciboController::class, 'generarUno'])->name('generar.uno');
            Route::post('generar-todos',        [ReciboController::class, 'generarTodos'])->name('generar.todos');
            Route::get('exportar-vigentes',     [ReciboController::class, 'exportarVigentes'])->name('exportar.vigentes');
        });
        Route::resource('recibos', ReciboController::class);
        Route::resource('recibo_detalles', ReciboDetalleController::class);

        // Recibos de Afiliación
        Route::prefix('recibos-afiliacion')->name('recibos-afiliacion.')->group(function () {
            Route::get('/{recibos_afiliacion}/imprimir', [ReciboAfiliacionController::class, 'imprimir'])->name('imprimir');
            Route::post('/{recibos_afiliacion}/pagar',   [ReciboAfiliacionController::class, 'pagar'])->name('pagar');
        });
        Route::resource('recibos-afiliacion', ReciboAfiliacionController::class)->parameters(['recibos-afiliacion' => 'recibos_afiliacion']);
    });

    /*
    |-----------------------------------
    | ASESORES Y SERVICIOS (modulo:asesores)
    |-----------------------------------
    */

    Route::middleware(['empresa', 'modulo:asesores'])->group(function () {
        Route::resources([
            'asesores'  => AsesorController::class,
            'servicios' => ServicioController::class,
        ]);
    });

    /*
    |-----------------------------------
    | SERVICIOS EXTERNOS (modulo:servicios_externos)
    |-----------------------------------
    */

    Route::middleware('modulo:servicios_externos')->group(function () {
        Route::prefix('servicios-externos')->as('servicios-externos.')->group(function () {
            Route::get('/',                       [ServicioExternoController::class, 'index'])->name('index');
            Route::get('/create',                 [ServicioExternoController::class, 'create'])->name('create');
            Route::post('/',                      [ServicioExternoController::class, 'store'])->name('store');
            Route::get('/{serviciosExterno}/edit',[ServicioExternoController::class, 'edit'])->name('edit');
            Route::put('/{serviciosExterno}',     [ServicioExternoController::class, 'update'])->name('update');
            Route::delete('/{serviciosExterno}',  [ServicioExternoController::class, 'destroy'])->name('destroy');
        });
    });

    /*
    |-----------------------------------
    | EMPRESAS EXTERNAS (modulo:empresas_externas)
    |-----------------------------------
    */

    Route::middleware('modulo:empresas_externas')->group(function () {
        Route::resource('empresas_externas', EmpresaExternaController::class);
    });

    /*
    |-----------------------------------
    | EXPORTACIONES (modulo:exportaciones)
    |-----------------------------------
    */

    Route::middleware('modulo:exportaciones')->group(function () {
        Route::prefix('exportaciones')->name('export.')->group(function () {
            Route::get('/',                 [ExportBatchController::class, 'index'])->name('index');
            Route::post('/crear',           [ExportBatchController::class, 'crearLote'])->name('crear');
            Route::get('pila-excel',        [ReciboController::class, 'exportarPilaExcel'])->name('pila.excel');
            Route::get('afiliados/exportar',    [AfiliadoController::class, 'exportar'])->name('afiliados.exportar');
            Route::get('arl-afiliados/exportar',[ArlAfiliadoController::class, 'exportar'])->name('arl-afiliados.exportar');
            Route::get('{id}',              [ExportBatchController::class, 'show'])->name('show');
            Route::post('{id}/reversar',    [ExportBatchController::class, 'reversar'])->name('reversar');
            Route::get('{id}/descargar',    [ExportBatchController::class, 'descargar'])->name('descargar');
        });
    });

    /*
    |-----------------------------------
    | LIBRERÍA (modulo:arls)
    |-----------------------------------
    */

    Route::middleware('modulo:arls')->group(function () {
        Route::resources([
            'eps'               => EpsController::class,
            'arls'              => ArlController::class,
            'pensions'          => PensionController::class,
            'cajas'             => CajaController::class,
            'documentos'        => DocumentoController::class,
            'subtipo_cotizantes'=> SubtipoCotizanteController::class,
        ]);
        Route::resource('parametros_anuales', ParametroAnualController::class)
            ->parameters(['parametros_anuales' => 'parametro_anual']);
    });

    /*
    |-----------------------------------
    | SISTEMA (modulo:usuarios / modulo:empresas / modulo:roles)
    |-----------------------------------
    */

    Route::middleware('modulo:empresas')->group(function () {
        Route::resource('empresas', EmpresaController::class)->parameters(['empresas' => 'empresa']);
    });

    Route::middleware('modulo:usuarios')->group(function () {
        Route::resource('usuarios', UserController::class);
    });

    Route::middleware('modulo:roles')->group(function () {
        Route::resource('roles', RolController::class);
    });

    /*
    |-----------------------------------
    | GESTIÓN DE MÓDULOS (modulo:modulos_empresa / modulo:modulos_rol)
    |-----------------------------------
    */

    Route::middleware('modulo:modulos_empresa')->group(function () {
        Route::get('modulos-empresa',               [ModuloEmpresaController::class, 'index'])->name('modulos-empresa.index');
        Route::get('modulos-empresa/{empresa}/edit',[ModuloEmpresaController::class, 'edit'])->name('modulos-empresa.edit');
        Route::put('modulos-empresa/{empresa}',     [ModuloEmpresaController::class, 'update'])->name('modulos-empresa.update');
    });

    Route::middleware('modulo:modulos_rol')->group(function () {
        Route::get('modulos-rol',               [ModuloRolController::class, 'index'])->name('modulos-rol.index');
        Route::get('modulos-rol/{rol}/edit',    [ModuloRolController::class, 'edit'])->name('modulos-rol.edit');
        Route::put('modulos-rol/{rol}',         [ModuloRolController::class, 'update'])->name('modulos-rol.update');
    });

});