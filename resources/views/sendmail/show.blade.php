<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;">

    <div style="max-width: 650px; margin: auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); padding: 25px;">
        <h2 style="color: #0d6efd; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; margin-bottom: 20px;">Informasi Transaksi</h2>

        <table width="100%" cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
            <tr>
                <td width="35%" style="font-weight: bold;">ID Transaksi:</td>
                <td>TRX-0{{ $transaksi->id }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Nama Kasir:</td>
                <td>{{ $transaksi->nama_kasir }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Email Pembeli:</td>
                <td><a href="mailto:{{ $transaksi->email_pembeli }}" style="color: #0d6efd;">{{ $transaksi->email_pembeli }}</a></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Tanggal Transaksi:</td>
                <td>{{ \Carbon\Carbon::parse($transaksi->created_at)->format('d M Y, H:i:s') }}</td>
            </tr>
        </table>

        <h3 style="color: #0d6efd; margin-top: 30px;">Rincian Produk</h3>
        <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse: collapse; border: 1px solid #dee2e6;">
            <thead>
                <tr style="background-color: #0d6efd; color: #ffffff; text-align: left;">
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach($transaksi->details as $detail)
                    @php 
                        $hargaSatuan = $detail->product->price;
                        $subtotal = $detail->jumlah_pembelian * $hargaSatuan;
                        $grandTotal += $subtotal;
                    @endphp
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td>{{ $detail->product->title }}</td>
                        <td>{{ $detail->jumlah_pembelian }}</td>
                        <td>Rp{{ number_format($hargaSatuan, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #fff3cd; font-weight: bold;">
                    <td colspan="3" style="text-align: right;">Grand Total</td>
                    <td>Rp{{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div style="text-align: center; margin-top: 30px; font-size: 13px; color: #6c757d;">
            <p>Terima kasih telah bertransaksi dengan kami<br>
            Email ini dikirim otomatis oleh sistem.</p>
        </div>
    </div>

</body>
</html>