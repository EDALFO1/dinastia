<?php

namespace App\Domains\Accounting\Controllers;

use App\Domains\Accounting\Models\ChartOfAccounts;
use App\Domains\Accounting\Models\JournalEntry;
use App\Domains\Accounting\Models\JournalLine;
use App\Domains\Accounting\Requests\StoreJournalEntryRequest;
use App\Domains\Accounting\Requests\UpdateJournalEntryRequest;
use App\Domains\Accounting\Resources\JournalEntryResource;
use App\Domains\Accounting\Services\AccountingValidator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JournalEntryApiController
{
    protected AccountingValidator $validator;

    public function __construct(AccountingValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Listar asientos contables
     * GET /api/v1/accounting/journal-entries
     */
    public function index(Request $request): JsonResponse
    {
        $empresaId = session('empresa_id');

        $query = JournalEntry::where('empresa_id', $empresaId)
            ->with(['lines.account', 'usuarioCreacion', 'usuarioAprobacion']);

        // Filtros
        if ($request->has('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->has('desde') && $request->has('hasta')) {
            $desde = \Carbon\Carbon::createFromFormat('Y-m-d', $request->input('desde'));
            $hasta = \Carbon\Carbon::createFromFormat('Y-m-d', $request->input('hasta'));
            $query->whereBetween('fecha', [$desde, $hasta]);
        }

        if ($request->has('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function (Builder $q) use ($buscar) {
                $q->where('numero_asiento', 'like', "%{$buscar}%")
                    ->orWhere('descripcion', 'like', "%{$buscar}%")
                    ->orWhere('referencia_documento', 'like', "%{$buscar}%");
            });
        }

        $asientos = $query->orderBy('fecha', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => JournalEntryResource::collection($asientos),
            'links' => [
                'first' => $asientos->url(1),
                'last' => $asientos->url($asientos->lastPage()),
                'prev' => $asientos->previousPageUrl(),
                'next' => $asientos->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $asientos->currentPage(),
                'from' => $asientos->firstItem(),
                'last_page' => $asientos->lastPage(),
                'per_page' => $asientos->perPage(),
                'to' => $asientos->lastItem(),
                'total' => $asientos->total(),
            ],
        ]);
    }

    /**
     * Crear asiento contable
     * POST /api/v1/accounting/journal-entries
     */
    public function store(StoreJournalEntryRequest $request): JsonResponse
    {
        $empresaId = session('empresa_id');

        return DB::transaction(function () use ($request, $empresaId) {
            // Generar número de asiento
            $numeroAsiento = JournalEntry::generateNumber($empresaId);

            // Validar que no existe
            if (!$this->validator->validateNoDuplicate($empresaId, $numeroAsiento)) {
                return response()->json([
                    'message' => 'Error al generar número de asiento',
                    'errors' => $this->validator->getErrors(),
                ], 422);
            }

            // Crear asiento en estado borrador
            $asiento = JournalEntry::create([
                'empresa_id' => $empresaId,
                'numero_asiento' => $numeroAsiento,
                'fecha' => $request->input('fecha'),
                'descripcion' => $request->input('descripcion'),
                'referencia_documento' => $request->input('referencia_documento'),
                'tipo_documento' => $request->input('tipo_documento'),
                'usuario_creacion_id' => auth()->id(),
                'estado' => 'borrador',
            ]);

            // Crear líneas
            foreach ($request->input('lines', []) as $lineData) {
                $cuenta = ChartOfAccounts::find($lineData['account_id']);

                if (!$this->validator->validateAccountCanMove($cuenta)) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Cuenta no válida para movimientos',
                        'errors' => $this->validator->getErrors(),
                    ], 422);
                }

                JournalLine::create([
                    'empresa_id' => $empresaId,
                    'journal_entry_id' => $asiento->id,
                    'account_id' => $lineData['account_id'],
                    'descripcion' => $lineData['descripcion'] ?? null,
                    'tipo_movimiento' => $lineData['tipo_movimiento'],
                    'valor' => $lineData['valor'],
                    'centro_costo_id' => $lineData['centro_costo_id'] ?? null,
                    'referencia_documento' => $lineData['referencia_documento'] ?? null,
                ]);
            }

            // Recargar con relaciones
            $asiento->load(['lines.account', 'usuarioCreacion']);

            Log::info('Asiento contable creado', [
                'asiento_id' => $asiento->id,
                'numero_asiento' => $numeroAsiento,
                'empresa_id' => $empresaId,
                'usuario_id' => auth()->id(),
            ]);

            return response()->json(
                new JournalEntryResource($asiento),
                201
            );
        });
    }

    /**
     * Obtener asiento específico
     * GET /api/v1/accounting/journal-entries/{id}
     */
    public function show(int $id): JsonResponse
    {
        $asiento = JournalEntry::where('empresa_id', session('empresa_id'))
            ->with(['lines.account', 'usuarioCreacion', 'usuarioAprobacion'])
            ->findOrFail($id);

        return response()->json(new JournalEntryResource($asiento));
    }

    /**
     * Actualizar asiento
     * PUT /api/v1/accounting/journal-entries/{id}
     */
    public function update(int $id, UpdateJournalEntryRequest $request): JsonResponse
    {
        $asiento = JournalEntry::where('empresa_id', session('empresa_id'))->findOrFail($id);

        if ($asiento->estado !== 'borrador') {
            return response()->json([
                'message' => 'Solo se pueden editar asientos en estado borrador',
            ], 422);
        }

        return DB::transaction(function () use ($asiento, $request) {
            $asiento->update([
                'fecha' => $request->input('fecha', $asiento->fecha),
                'descripcion' => $request->input('descripcion', $asiento->descripcion),
                'referencia_documento' => $request->input('referencia_documento', $asiento->referencia_documento),
                'tipo_documento' => $request->input('tipo_documento', $asiento->tipo_documento),
            ]);

            // Actualizar líneas si se proporcionan
            if ($request->has('lines')) {
                $asiento->lines()->delete();

                foreach ($request->input('lines', []) as $lineData) {
                    JournalLine::create([
                        'empresa_id' => session('empresa_id'),
                        'journal_entry_id' => $asiento->id,
                        'account_id' => $lineData['account_id'],
                        'descripcion' => $lineData['descripcion'] ?? null,
                        'tipo_movimiento' => $lineData['tipo_movimiento'],
                        'valor' => $lineData['valor'],
                        'centro_costo_id' => $lineData['centro_costo_id'] ?? null,
                    ]);
                }
            }

            $asiento->load(['lines.account', 'usuarioCreacion', 'usuarioAprobacion']);

            Log::info('Asiento contable actualizado', [
                'asiento_id' => $asiento->id,
                'empresa_id' => session('empresa_id'),
            ]);

            return response()->json(new JournalEntryResource($asiento), 200);
        });
    }

    /**
     * Eliminar asiento
     * DELETE /api/v1/accounting/journal-entries/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $asiento = JournalEntry::where('empresa_id', session('empresa_id'))->findOrFail($id);

        if ($asiento->estado !== 'borrador') {
            return response()->json([
                'message' => 'Solo se pueden eliminar asientos en estado borrador',
            ], 422);
        }

        $numero = $asiento->numero_asiento;
        $asiento->delete();

        Log::info('Asiento contable eliminado', [
            'numero_asiento' => $numero,
            'empresa_id' => session('empresa_id'),
        ]);

        return response()->json(null, 204);
    }

    /**
     * Aprobar asiento (cambiar a posteado)
     * POST /api/v1/accounting/journal-entries/{id}/approve
     */
    public function approve(int $id): JsonResponse
    {
        $asiento = JournalEntry::where('empresa_id', session('empresa_id'))
            ->with(['lines.account'])
            ->findOrFail($id);

        if ($asiento->estado !== 'borrador') {
            return response()->json([
                'message' => 'El asiento no está en estado borrador',
            ], 422);
        }

        try {
            $asiento->approve(auth()->id());

            Log::info('Asiento contable aprobado', [
                'asiento_id' => $asiento->id,
                'numero_asiento' => $asiento->numero_asiento,
                'usuario_id' => auth()->id(),
            ]);

            return response()->json(new JournalEntryResource($asiento), 200);
        } catch (\Exception $e) {
            Log::error('Error al aprobar asiento', [
                'asiento_id' => $asiento->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al aprobar asiento: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Rechazar asiento
     * POST /api/v1/accounting/journal-entries/{id}/reject
     */
    public function reject(int $id): JsonResponse
    {
        $asiento = JournalEntry::where('empresa_id', session('empresa_id'))->findOrFail($id);

        if ($asiento->estado === 'posteado') {
            return response()->json([
                'message' => 'No se puede rechazar un asiento ya posteado',
            ], 422);
        }

        $asiento->reject();

        Log::info('Asiento contable rechazado', [
            'asiento_id' => $asiento->id,
            'numero_asiento' => $asiento->numero_asiento,
        ]);

        return response()->json(new JournalEntryResource($asiento), 200);
    }

    /**
     * Obtener resumen de saldos (por cuenta)
     * GET /api/v1/accounting/journal-entries/summary/balances
     */
    public function balanceSummary(Request $request): JsonResponse
    {
        $empresaId = session('empresa_id');

        $cuentas = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('permite_movimiento', true)
            ->get()
            ->map(function (ChartOfAccounts $cuenta) use ($request) {
                if ($request->has('desde') && $request->has('hasta')) {
                    $desde = \Carbon\Carbon::createFromFormat('Y-m-d', $request->input('desde'));
                    $hasta = \Carbon\Carbon::createFromFormat('Y-m-d', $request->input('hasta'));
                    $saldo = $cuenta->getBalanceByPeriod($desde, $hasta);
                } else {
                    $saldo = $cuenta->getCurrentBalance();
                }

                return [
                    'codigo' => $cuenta->codigo,
                    'nombre' => $cuenta->nombre,
                    'tipo' => $cuenta->tipo_cuenta->value,
                    'saldo' => (float) $saldo,
                ];
            });

        return response()->json([
            'data' => $cuentas,
            'total_records' => $cuentas->count(),
        ]);
    }
}
