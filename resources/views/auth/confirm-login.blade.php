<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center vh-100">

    <div class="card shadow p-4 text-center" style="width:400px">

        <h4 class="mb-3 text-danger">⚠️ Akun Sedang Digunakan</h4>

        <p>
            Akun ini sedang login di perangkat lain.<br>
            Lanjutkan login?
        </p>

        <form method="POST" action="{{ route('session.process') }}">
            @csrf

            <button type="submit" name="confirm" value="yes" class="btn btn-success w-100 mb-2">
                Ya, lanjutkan (logout sesi lama)
            </button>

            <button type="submit" name="confirm" value="no" class="btn btn-secondary w-100">
                Tidak
            </button>
        </form>

    </div>

</div>

</body>
</html>
