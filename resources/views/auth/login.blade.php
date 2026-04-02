<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .countdown-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 1.5rem;
        }
        .countdown-box {
            text-align: center;
            padding: 2rem;
        }
        .countdown-box .timer {
            font-size: 3rem;
            font-weight: 700;
            color: #dc3545;
        }
        .countdown-box p {
            color: #6c757d;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body style="background: linear-gradient(135deg,#1e3c72,#2a5298);">

<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-5">

            <div class="card shadow-lg border-0 rounded-4 position-relative">

                {{-- Countdown overlay --}}
                @php
                    $seconds = session('lockout_seconds', $lockoutSeconds ?? 0);
                @endphp

                @if($seconds > 0)
                <div class="countdown-overlay" id="lockout-overlay">
                    <div class="countdown-box">
                        <div class="timer" id="countdown-timer">{{ $seconds }}</div>
                        <p>Terlalu banyak percobaan login.<br>Silakan tunggu sebelum mencoba lagi.</p>
                    </div>
                </div>
                @endif

                <div class="card-body p-5">

                    <h3 class="text-center mb-4 fw-bold">
                        {{ config('app.name') }}
                    </h3>

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any() && $seconds <= 0)
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" id="login-form">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                {{ $seconds > 0 ? 'disabled' : '' }}>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                required
                                {{ $seconds > 0 ? 'disabled' : '' }}>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-dark" id="login-btn" {{ $seconds > 0 ? 'disabled' : '' }}>
                                Login
                            </button>
                        </div>

                    </form>

                    <hr class="my-4">

                    <div class="text-center text-muted small">
                        &copy; {{ date('Y') }} {{ config('app.name') }}
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

@if($seconds > 0)
<script>
    (function() {
        let remaining = {{ $seconds }};
        const timerEl = document.getElementById('countdown-timer');
        const overlay = document.getElementById('lockout-overlay');
        const form = document.getElementById('login-form');
        const btn = document.getElementById('login-btn');

        function formatTime(sec) {
            const m = Math.floor(sec / 60);
            const s = sec % 60;
            if (m > 0) {
                return m + ':' + (s < 10 ? '0' : '') + s;
            }
            return s + ' detik';
        }

        timerEl.textContent = formatTime(remaining);

        const interval = setInterval(function() {
            remaining--;
            if (remaining <= 0) {
                clearInterval(interval);
                overlay.remove();
                // Enable form inputs
                form.querySelectorAll('input').forEach(function(input) {
                    input.disabled = false;
                });
                btn.disabled = false;
            } else {
                timerEl.textContent = formatTime(remaining);
            }
        }, 1000);
    })();
</script>
@endif

</body>
</html>
