<!-- resources/views/realtime/load.blade.php -->

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Username</th>
                <th>IP Address</th>
                <th>Uptime</th>
                <th>Bytes In/Out</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($active) && count($active) > 0)
                @foreach($active as $user)
                    <tr>
                        <td><code>{{ $user['name'] }}</code></td>
                        <td>{{ $user['address'] }}</td>
                        <td>{{ $user['uptime'] }}</td>
                        <td>{{ formatBytes($user['bytes-in']) }} / {{ formatBytes($user['bytes-out']) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" class="text-center">Tidak ada pelanggan aktif.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>