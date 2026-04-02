
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi 2FA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-4">
            <div class="card shadow-sm p-4">
                <h4 class="text-center mb-3">Masukkan kode 2FA</h4>

                @if($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('2fa.verify.post') }}">
                    @csrf
                    <div class="mb-3">
                        <input type="text" name="otp" class="form-control" placeholder="6-digit kode OTP" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Verifikasi</button>
                </form>

            </div>
        </div>
    </div>
</div>
</body>
</html>
