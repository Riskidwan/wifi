<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card">
                <div class="card-header">
                    <h4>Aktifkan Two-Factor Authentication (2FA)</h4>
                </div>

                <div class="card-body text-center">

                    <p>
                        Scan QR Code berikut menggunakan
                        <strong>Microsoft Authenticator</strong>
                        atau Google Authenticator.
                    </p>

                    <div class="mb-3">
                        {!! $qrCode !!}
                    </div>

                    <p><strong>Secret Key:</strong></p>
                    <div class="alert alert-secondary">
                        {{ $secret }}
                    </div>

                    <hr>

                    <form method="POST" action="{{ route('2fa.store') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label>Masukkan Kode 6 Digit</label>
                            <input type="text"
                                   name="otp"
                                   class="form-control @error('otp') is-invalid @enderror"
                                   placeholder="Contoh: 123456"
                                   required>
                            @error('otp')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Aktifkan 2FA
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
