<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Login</title>
</head>
<body style="text-align:center;margin-top:100px;font-family:sans-serif;">

    <h2>⚠️ Akun sedang digunakan</h2>
    <p>Akun ini sudah login di perangkat lain.</p>
    <p>Lanjutkan login dan keluarkan perangkat lama?</p>

    <form method="POST" action="{{ route('session.confirm.post') }}">
        @csrf
        <button type="submit">Ya, Lanjutkan</button>
    </form>

    <br>

    <a href="{{ route('login') }}">
        Batal
    </a>

</body>
</html>
