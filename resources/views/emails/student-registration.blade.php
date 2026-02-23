<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Kelas Berhasil</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #2563eb; color: white; padding: 30px 40px; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 30px 40px; color: #333333; }
        .body p { line-height: 1.6; margin: 0 0 16px; }
        .info-box { background-color: #eff6ff; border-left: 4px solid #2563eb; border-radius: 4px; padding: 16px 20px; margin: 20px 0; }
        .info-row { display: flex; margin-bottom: 8px; }
        .info-label { font-weight: bold; color: #555; min-width: 140px; }
        .info-value { color: #111; }
        .footer { background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 40px; text-align: center; color: #888; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pendaftaran Kelas Berhasil!</h1>
        </div>
        <div class="body">
            <p>Halo, <strong>{{ $order->user->name }}</strong>,</p>
            <p>Selamat! Pembayaran Anda telah dikonfirmasi dan pendaftaran kelas berhasil diproses.</p>

            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Nomor Pesanan</span>
                    <span class="info-value">{{ $order->order_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kelas</span>
                    <span class="info-value">{{ $subscription->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Mulai</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($startsAt)->translatedFormat('d F Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Durasi</span>
                    <span class="info-value">{{ $subscription->duration_days }} hari</span>
                </div>
                @if($subscription->days && count($subscription->days) > 0)
                <div class="info-row">
                    <span class="info-label">Hari Pertemuan</span>
                    <span class="info-value">{{ implode(', ', $subscription->days) }}</span>
                </div>
                @endif
                @if($subscription->meetings_count)
                <div class="info-row">
                    <span class="info-label">Jumlah Pertemuan</span>
                    <span class="info-value">{{ $subscription->meetings_count }} pertemuan</span>
                </div>
                @endif
            </div>

            <p>Anda sudah bisa masuk ke kelas melalui aplikasi mulai tanggal <strong>{{ \Carbon\Carbon::parse($startsAt)->translatedFormat('d F Y') }}</strong>.</p>
            <p>Terima kasih telah mendaftar. Semangat belajar!</p>
        </div>
        <div class="footer">
            <p>Kelas Olimpiade Matematika &bull; Email ini dikirim otomatis, harap tidak membalas.</p>
        </div>
    </div>
</body>
</html>
