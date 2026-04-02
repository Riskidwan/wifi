@extends('layouts.layouts', ['menu' => 'interface', 'submenu' => ''])

@section('title', 'Monitoring Traffic Interface')

@section('content')

<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                    <div>
                        <h2 class="text-white pb-2 fw-bold">Monitoring Traffic Interface</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-inner">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Pilih Interface untuk Dimonitor</label>
                                <select class="form-control form-control-lg" id="interface">
                                    <option value="">-- Pilih Interface --</option>
                                    @foreach($interface as $iface)
                                        @if(isset($iface['name']) && ($iface['disabled'] ?? 'false') == 'false')
                                            <option value="{{ $iface['name'] }}">{{ $iface['name'] }} 
                                                @if(isset($iface['running']) && $iface['running'] == 'true')
                                                    <span class="badge badge-success">Up</span>
                                                @else
                                                    <span class="badge badge-danger">Down</span>
                                                @endif
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <!-- Grafik Traffic -->
                            <div id="trafficChart" style="height: 400px; margin-top: 20px;"></div>
                        </div>
                    </div>

                    <!-- Statistik Real-time -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card card-stats card-round bg-primary">
                                <div class="card-body text-white">
                                    <div class="row align-items-center">
                                        <div class="col col-stats ml-3 ml-sm-0">
                                            <h5 class="card-title mb-0">UPLOAD (TX)</h5>
                                            <h3 class="mb-0" id="txValue">0 bps</h3>
                                        </div>
                                        <div class="col-icon">
                                            <div class="icon-big text-center">
                                                <i class="fas fa-upload"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-stats card-round bg-success">
                                <div class="card-body text-white">
                                    <div class="row align-items-center">
                                        <div class="col col-stats ml-3 ml-sm-0">
                                            <h5 class="card-title mb-0">DOWNLOAD (RX)</h5>
                                            <h3 class="mb-0" id="rxValue">0 bps</h3>
                                        </div>
                                        <div class="col-icon">
                                            <div class="icon-big text-center">
                                                <i class="fas fa-download"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>
$(document).ready(function() {
    let chart;
    let interfaceInterval;

    function initChart() {
        chart = Highcharts.chart('trafficChart', {
            chart: {
                type: 'spline',
                animation: Highcharts.svg,
                marginRight: 10,
                events: {
                    load: function() {
                        // Data awal kosong
                    }
                }
            },
            title: { text: 'Traffic Real-time (bps)' },
            xAxis: {
                type: 'datetime',
                tickPixelInterval: 150,
                maxZoom: 20 * 1000
            },
            yAxis: {
                title: { text: 'Bits per Second' },
                plotLines: [{ value: 0, width: 1, color: '#808080' }]
            },
            tooltip: {
                formatter: function() {
                    return '<b>' + this.series.name + '</b><br/>' +
                        Highcharts.dateFormat('%H:%M:%S', this.x) + '<br/>' +
                        convertBits(this.y);
                }
            },
            legend: { enabled: true },
            series: [{
                name: 'TX (Upload)',
                data: []
            }, {
                name: 'RX (Download)',
                data: []
            }]
        });
    }

    function convertBits(bits) {
        if (bits === 0) return '0 bps';
        const sizes = ['bps', 'Kbps', 'Mbps', 'Gbps'];
        const i = Math.floor(Math.log(bits) / Math.log(1000)); // Gunakan 1000 untuk bits
        return (bits / Math.pow(1000, i)).toFixed(2) + ' ' + sizes[i];
    }

    function updateTraffic() {
        const interface = $('#interface').val();
        if (!interface) {
            $('#txValue').text('0 bps');
            $('#rxValue').text('0 bps');
            return;
        }

        $.getJSON("{{ url('/interface/traffic') }}/" + encodeURIComponent(interface))
            .done(function(data) {
                const now = new Date().getTime();
                const tx = data[0].data || 0;
                const rx = data[1].data || 0;

                // Update grafik
                const series = chart.series;
                const shift = series[0].data.length > 59; // Keep 60 points (1 min at 1s interval)

                series[0].addPoint([now, tx], true, shift);
                series[1].addPoint([now, rx], true, shift);

                // Update nilai real-time
                $('#txValue').text(convertBits(tx));
                $('#rxValue').text(convertBits(rx));
            })
            .fail(function() {
                $('#txValue').text('Error');
                $('#rxValue').text('Error');
            });
    }

    // Inisialisasi
    initChart();

    // Event saat pilih interface
    $('#interface').on('change', function() {
        // Reset data grafik
        chart.series[0].setData([]);
        chart.series[1].setData([]);
        
        // Hentikan interval lama
        if (interfaceInterval) clearInterval(interfaceInterval);
        
        // Mulai interval baru jika interface dipilih
        if ($(this).val()) {
            updateTraffic(); // Update langsung
            interfaceInterval = setInterval(updateTraffic, 1000);
        }
    });
});
</script>
@endpush

@endsection