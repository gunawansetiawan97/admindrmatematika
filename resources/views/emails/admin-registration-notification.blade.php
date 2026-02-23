<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isExtension ? 'Perpanjangan Kelas Murid' : 'Notifikasi Murid Baru' }}</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #1f2937; color: white; padding: 30px 40px; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 30px 40px; color: #333333; }
        .body p { line-height: 1.6; margin: 0 0 16px; }
        .info-box { background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
        .info-row { display: flex; margin-bottom: 10px; font-size: 14px; }
        .info-label { font-weight: bold; color: #555; min-width: 150px; }
        .info-value { color: #111; }
        .footer { background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 40px; text-align: center; color: #888; font-size: 13px; }
        .badge { display: inline-block; background-color: #dcfce7; color: #166534; padding: 2px 10px; border-radius: 9999px; font-size: 12px; font-weight: bold; }
        .badge-blue { background-color: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $isExtension ? 'Perpanjangan Kelas Murid' : 'Murid Baru Terdaftar' }}</h1>
        </div>
        <div class="body">
            @if($isExtension)
                <p>Murid berikut telah memperpanjang kelas <strong>{{ $subscription->name }}</strong>.</p>
            @else
                <p>Ada murid baru yang berhasil mendaftar ke kelas <strong>{{ $subscription->name }}</strong>.</p>
            @endif

            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Nama Murid</span>
                    <span class="info-value">{{ $order->user->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email Murid</span>
                    <span class="info-value">{{ $order->user->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nomor Pesanan</span>
                    <span class="info-value">{{ $order->order_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kelas</span>
                    <span class="info-value">{{ $subscription->name }}</span>
                </div>
                @if($isExtension && $expiresAt)
                <div class="info-row">
                    <span class="info-label">Aktif Hingga</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($expiresAt)->translatedFormat('d F Y') }}</span>
                </div>
                @else
                <div class="info-row">
                    <span class="info-label">Tanggal Mulai</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($startsAt)->translatedFormat('d F Y') }}</span>
                </div>
                @endif
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
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <span class="badge {{ $isExtension ? 'badge-blue' : '' }}">
                            {{ $isExtension ? 'Diperpanjang' : 'Terverifikasi' }}
                        </span>
                    </span>
                </div>
            </div>

            @if(!$isExtension)
                <p>Jangan lupa untuk mendaftarkan murid ini ke kelola kelas agar bisa melihat aktivitas kelas.</p>
            @endif
        </div>
        <div class="footer">
            <p>Kelas Olimpiade Matematika &bull; Notifikasi Admin</p>
        </div>
    </div>
</body>
</html>
