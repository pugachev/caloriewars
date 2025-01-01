@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">歩数と歩行時間</div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="yearTabs" role="tablist">
                        @foreach(['2025', '2024', '2023'] as $year)
                        <li class="nav-item">
                            <a class="nav-link {{ $year == date('Y') ? 'active' : '' }}"
                               id="y{{ $year }}-tab"
                               data-toggle="tab"
                               href="#y{{ $year }}"
                               role="tab"
                               data-year="{{ $year }}">{{ $year }}年</a>
                        </li>
                        @endforeach
                    </ul>
                    <div class="tab-content" id="yearTabContent">
                        <canvas id="myChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let myChart = null;

    function loadGraphData(year) {
        console.log('Loading data for year:', year);
        $.ajax({
            url: '{{ route("makegraph3ajax") }}',
            data: { year: year },
            method: 'GET',
            success: function(data) {
                console.log('Received data:', data);
                updateChart(data);
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
            }
        });
    }

    function updateChart(data) {
        const dates = data.map(item => item.target_date);
        const steps = data.map(item => item.steps);
        const times = data.map(item => item.time);

        if (myChart) {
            myChart.destroy();
        }

        const ctx = document.getElementById('myChart').getContext('2d');
        myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: '歩数',
                        data: steps,
                        borderColor: 'rgb(75, 192, 192)',
                        yAxisID: 'y',
                    },
                    {
                        label: '歩行時間',
                        data: times,
                        borderColor: 'rgb(255, 99, 132)',
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                    }
                }
            }
        });
    }

    loadGraphData(new Date().getFullYear());

    $('.nav-link').on('click', function(e) {
        e.preventDefault();
        const year = $(this).data('year');
        loadGraphData(year);
    });
});
</script>
@endsection
