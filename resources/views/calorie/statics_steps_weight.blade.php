@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">歩数と確定体重</div>
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
<script>
$(document).ready(function() {
    // CSRFトークンの設定
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let myChart = null;

    function loadGraphData(year) {
        console.log('Loading data for year:', year);
        $('#myChart').css('opacity', '0.5');

        $.ajax({
            url: '{{ route("makegraph4ajax") }}',
            data: { year: year },
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                $('#myChart').css('opacity', '1');
                console.log('Received data:', data);
                if (data && data.length > 0) {
                    updateChart(data);
                } else {
                    console.log('No data received for year:', year);
                    // 空のグラフを表示
                    updateChart([]);
                }
            },
            error: function(xhr, status, error) {
                $('#myChart').css('opacity', '1');
                console.error('Ajax error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);

                if (xhr.status === 401) {
                    alert('セッションが切れました。ページをリロードします。');
                    window.location.reload();
                }
            }
        });
    }

    function updateChart(data) {
        const dates = data.map(item => item.target_date);
        const steps = data.map(item => item.steps);
        const weights = data.map(item => item.weight);

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
                        label: '確定体重',
                        data: weights,
                        borderColor: 'rgb(255, 99, 132)',
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: '歩数'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: '体重(kg)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }

    // 初期データ読み込み
    loadGraphData(new Date().getFullYear());

    // タブクリックイベント
    $('.nav-link').on('click', function(e) {
        e.preventDefault();
        $(this).tab('show');
        const year = $(this).data('year');
        loadGraphData(year);
    });
});
</script>
@endsection
