<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla Mensual - {{ $mes }}/{{ $anio }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .subtitle { font-size: 14px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f2f2f2; border: 1px solid #ddd; padding: 8px; text-align: left; text-transform: uppercase; font-size: 10px; }
        td { border: 1px solid #ddd; padding: 8px; vertical-align: middle; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .footer { margin-top: 30px; font-size: 10px; text-align: right; color: #777; }
        .totals { background-color: #eee; font-weight: bold; }
        .signature-row { margin-top: 50px; }
        .signature-box { width: 200px; border-top: 1px solid #000; text-align: center; float: right; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Planilla de Sueldos y Salarios</div>
        <div class="subtitle">Periodo: {{ \Carbon\Carbon::create()->month((int)$mes)->translatedFormat('F') }} {{ $anio }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 40%">Nombre del Personal</th>
                <th style="width: 20%">Cargo</th>
                <th class="text-right">Haber Básico (Bs.)</th>
                <th class="text-right">Abonado</th>
                <th class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $sumPactado = 0; $sumPagado = 0; $sumSaldo = 0; 
            @endphp
            @foreach($datosProcesados as $index => $r)
                @php
                    $sumPactado += $r->sueldo;
                    $sumPagado += $r->pagado;
                    $sumSaldo += $r->saldo;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $r->nombre }}</td>
                    <td>{{ $r->cargo }}</td>
                    <td class="text-right">{{ number_format($r->sueldo, 2) }}</td>
                    <td class="text-right">{{ number_format($r->pagado, 2) }}</td>
                    <td class="text-right">{{ number_format($r->saldo, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals">
                <td colspan="3" class="text-right">TOTALES GENERALES:</td>
                <td class="text-right">{{ number_format($sumPactado, 2) }}</td>
                <td class="text-right">{{ number_format($sumPagado, 2) }}</td>
                <td class="text-right">{{ number_format($sumSaldo, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Documento generado por Asistencia GAM el {{ now()->format('d/m/Y H:i') }}
    </div>

    <div class="signature-row">
        <div class="signature-box">
            Firma Responsable
        </div>
    </div>
</body>
</html>