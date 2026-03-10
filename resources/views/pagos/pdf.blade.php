<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comprobante de Pago #{{ $pago->id }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
        .title { font-size: 20px; font-weight: bold; color: #28a745; text-transform: uppercase; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 8px; border: 1px solid #ddd; }
        .bg-light { background-color: #f8f9fa; font-weight: bold; width: 30%; }
        .amount-box { border: 2px solid #333; padding: 15px; text-align: center; font-size: 18px; font-weight: bold; margin: 20px 0; }
        .footer-signatures { margin-top: 60px; width: 100%; }
        .signature-line { border-top: 1px solid #333; width: 200px; margin: 0 auto; margin-top: 40px; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Comprobante de Pago</div>
        <div>Sistema de Gestión Asistencia GAM</div>
        <div>Fecha de emisión: {{ date('d/m/Y H:i') }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="bg-light">Nro. Transacción:</td>
            <td>#{{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td class="bg-light">Beneficiario:</td>
            <td>{{ $pago->persona->nombre }}</td>
        </tr>
        <tr>
            <td class="bg-light">Concepto:</td>
            <td>{{ $pago->tipo_pago }} - {{ $pago->mes_correspondiente ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="bg-light">Fecha de Operación:</td>
            <td>{{ $pago->fecha_operacion->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="bg-light">Método de Pago:</td>
            <td>
                @if($pago->cuentaFinanciera)
                    Transferencia: {{ $pago->cuentaFinanciera->banco->nombre }} (***{{ substr($pago->cuentaFinanciera->identificador_cuenta, -4) }})
                @else
                    Efectivo / Caja
                @endif
            </td>
        </tr>
        @if($pago->comprobante_fise)
        <tr>
            <td class="bg-light">Referencia/FISE:</td>
            <td>{{ $pago->comprobante_fise }}</td>
        </tr>
        @endif
    </table>

    <div class="amount-box">
        MONTO PAGADO: {{ $pago->moneda_pago }} {{ number_format($pago->monto_pagado, 2) }}
        <br>
        <small style="font-size: 10px; font-weight: normal; color: #666;">
            Equivalente en Contrato: {{ $pago->contrato->moneda }} {{ number_format($pago->debe_equivalente, 2) }}
        </small>
    </div>

    <p><strong>Observaciones:</strong> {{ $pago->observacion ?? 'Sin observaciones particulares.' }}</p>

    <table class="footer-signatures">
        <tr>
            <td class="text-center">
                <div class="signature-line"></div>
                <strong>ENTREGADO POR</strong><br>
                Administración
            </td>
            <td class="text-center">
                <div class="signature-line"></div>
                <strong>RECIBIDO POR</strong><br>
                {{ $pago->persona->nombre }}
            </td>
        </tr>
    </table>
</body>
</html>