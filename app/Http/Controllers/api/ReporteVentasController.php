<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ReporteVentasController extends Controller
{
    /**
     * Ventas agrupadas por dia o por mes, incluyendo los periodos sin ventas (utiles para graficas).
     * Parametros: ?agrupar=dia|mes, ?desde=YYYY-MM-DD, ?hasta=YYYY-MM-DD
     * Por defecto: ultimos 30 dias o ultimos 12 meses.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'agrupar' => ['nullable', 'in:dia,mes'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $porMes = $request->query('agrupar') === 'mes';
        $hasta = Carbon::parse($request->query('hasta', today()));
        $desde = $request->filled('desde')
            ? Carbon::parse($request->query('desde'))
            : ($porMes ? $hasta->copy()->subMonths(11)->startOfMonth() : $hasta->copy()->subDays(29));

        if ($porMes) {
            $desde->startOfMonth();
        }

        if (! $porMes && $desde->diffInDays($hasta) > 366) {
            throw ValidationException::withMessages([
                'desde' => 'Para agrupar por dia el rango no puede superar un ano.',
            ]);
        }

        $formato = $porMes ? 'Y-m' : 'Y-m-d';

        $ventas = Venta::query()
            ->whereDate('fecha_venta', '>=', $desde->toDateString())
            ->whereDate('fecha_venta', '<=', $hasta->toDateString())
            ->get(['fecha_venta', 'cantidad', 'total'])
            ->groupBy(fn (Venta $venta) => $venta->fecha_venta->format($formato));

        $periodos = collect(CarbonPeriod::create($desde, $porMes ? '1 month' : '1 day', $hasta))
            ->map(function (Carbon $fecha) use ($ventas, $formato) {
                $grupo = $ventas->get($fecha->format($formato), collect());

                return [
                    'periodo' => $fecha->format($formato),
                    'ventas' => $grupo->count(),
                    'unidades' => (int) $grupo->sum('cantidad'),
                    'ingresos' => round((float) $grupo->sum('total'), 2),
                ];
            });

        return response()->json([
            'agrupar' => $porMes ? 'mes' : 'dia',
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
            'totales' => [
                'ventas' => $periodos->sum('ventas'),
                'unidades' => $periodos->sum('unidades'),
                'ingresos' => round($periodos->sum('ingresos'), 2),
            ],
            'periodos' => $periodos->values(),
        ]);
    }
}
