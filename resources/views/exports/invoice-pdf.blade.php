<!-- resources/views/pdf/invoice.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Invoice</title>
  @php
    $logo = base64_encode(file_get_contents(public_path('images/logo.JPG')));
  @endphp
  <style>
    body {
      font-family: sans-serif;
      font-size: 12px;
      line-height: 1.4;
    }

    .watermark {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -185%);
      width: 300px;
      height: 300px;
      background-image: url('data:image/jpg;base64,{{ $logo }}');
      background-size: contain;
      background-repeat: no-repeat;
      background-position: center;
      opacity: 0.1;
      z-index: -1;
      pointer-events: none;
    }

    .header {
      text-align: center;
      margin-bottom: 20px;
    }

    .title {
      font-size: 20px;
      font-weight: bold;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    table, th, td {
      border: 1px solid #000;
    }

    th, td {
      padding: 8px;
      text-align: left;
    }

    .text-right {
      text-align: right;
    }

    .text-center {
      text-align: center;
    }

    .mt-1 { margin-top: 10px; }
    .mt-2 { margin-top: 20px; }
    .mt-4 { margin-top: 40px; }
  </style>
</head>
<body>
  <div class="watermark"></div>

  <div class="header">
    <div class="title">Invoice</div>
    <div>
      ID Order: {{ optional($invoice->order)->id_order ?? '-' }} |
      ID Invoice: {{ $invoice->id_invoice }}
    </div>
  </div>

  <div>
    <p><strong>Pelanggan:</strong> {{ optional($invoice->order)->nama_pelanggan ?? '-' }}</p>
    <p><strong>Tgl Bayar:</strong> {{ optional($invoice->order)->created_at ?? '-' }}</p>
    <p>
        <strong>Jenis Invoice:</strong>
        <span style="color:red; font-weight:bold;">
            {{ optional($invoice->kategori)->nama ?? '-' }}
        </span>
    </p>
    <p><strong>Status:</strong> {{ optional($invoice->status)->nama ?? '-' }}</p>
  </div>

  <div class="mt-2">
    <table>
      <thead>
        <tr>
          <th>Produk</th>
          <th>Qty</th>
          <th>Harga</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($invoice->order->orderDetail ?? [] as $item)
          <tr>
            <td>{{ $item->nama }}</td>
            <td>{{ $item->qty }}</td>
            <td class="text-right">Rp. {{ number_format($item->harga, 0, ',', '.') }}</td>
            <td class="text-right">Rp. {{ number_format($item->harga * $item->qty, 0, ',', '.') }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="text-center">Tidak ada item produk.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @php
    $orderDetails = collect($invoice->order->orderDetail ?? []);
  @endphp

  <div class="mt-2">
    <strong>Tambahan: </strong>
    <ul>
        @forelse (collect($invoice->order->orderDetail ?? [])->pluck('orderTambahan')->flatten() as $tambahan)
            @if ($tambahan)
                <li>{{ $tambahan->nama }} - {{ $tambahan->qty }} qty - Rp. {{ number_format($tambahan->total, 0, ',', '.') }}</li>
            @endif
        @empty
            <li>Tidak ada tambahan.</li>
        @endforelse
    </ul>
  </div>

  <div class="mt-2">
    <strong>Keterangan:</strong> <br>{{ optional($invoice->order)->keterangan ?? '-' }}
  </div>

    <div class="mt-2">
        <table>
            <tr>
                <td><strong>Sub Total</strong></td>
                <td class="text-right">Rp. {{ number_format($invoice->sub_total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Lainnya</strong></td>
                <td class="text-right">Rp. {{ number_format($invoice->lainnya, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Diskon</strong></td>
                <td class="text-right">- Rp. {{ number_format($invoice->diskon, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Total</strong></td>
                <td class="text-right">Rp. {{ number_format($invoice->total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="mt-2">
        <table>
            <tr>
                <td><strong>Sisa Tagihan Sebelumnya</strong></td>
                <td class="text-right">Rp. {{ number_format($invoice->sisa_bayar_sblmnya, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Total Terbayar s.d. Saat Ini</strong></td>
                <td class="text-right">Rp. {{ number_format($invoice->total_pembayaran, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Sisa Tagihan Akhir</strong></td>
                <td class="text-right">Rp. {{ number_format($invoice->sisa_bayar, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="mt-2">
        <p><strong>Kasir:</strong> {{ optional($invoice->order)->user_nama ?? '-' }}</p>

        <p>
            <strong>Waktu:</strong>
            {{ \Carbon\Carbon::now()->translatedFormat('d-m-Y - H:i:s') }}
        </p>
    </div>
</body>
</html>
