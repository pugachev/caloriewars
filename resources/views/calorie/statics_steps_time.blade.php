@extends('layouts.app')

@section('content')
<div class="container">
    <h2>歩数と歩行時間</h2>

    <!-- タブナビゲーション -->
    <ul class="nav nav-tabs" id="yearTabs" role="tablist">
        @foreach($labels as $year)
            <li class="nav-item">
                <a class="nav-link {{ $year == date('Y') ? 'active' : '' }}"
                   id="y{{ $year }}-tab"
                   data-toggle="tab"
                   href="#y{{ $year }}"
                   role="tab"
                   aria-controls="y{{ $year }}"
                   aria-selected="{{ $year == date('Y') ? 'true' : 'false' }}"
                   data-year="{{ $year }}">
                    {{ $year }}年
                </a>
            </li>
        @endforeach
    </ul>

    <!-- タブコンテンツ -->
    <div class="tab-content" id="yearTabContent">
        @foreach($labels as $year)
            <div class="tab-pane fade {{ $year == date('Y') ? 'show active' : '' }}"
                 id="y{{ $year }}"
                 role="tabpanel"
                 aria-labelledby="y{{ $year }}-tab">
                <div class="mt-3">
                    <canvas id="chart{{ $year }}"></canvas>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
const charts = {};

function createChart(data, year) {
    const ctx = document.getElementById('chart' + year).getContext('2d');

    if (charts[year]) {
        charts[year].destroy();
    }

    charts[year] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels.map(week => `第${week}週`),
            datasets: [
                {
                    label: '歩数',
                    data: data.steps_data,
                    borderColor: 'rgb(75, 192, 192)',
                    yAxisID: 'y-steps',
                },
                {
                    label: '歩行時間',
                    data: data.time_data,
                    borderColor: 'rgb(255, 99, 132)',
                    yAxisID: 'y-time',
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                'y-steps': {
                    type: 'linear',
                    position: 'left',
                    title: {
                        display: true,
                        text: '歩数'
                    }
                },
                'y-time': {
                    type: 'linear',
                    position: 'right',
                    title: {
                        display: true,
                        text: '歩行時間(分)'
                    }
                }
            }
        }
    });
}

$(document).ready(function() {
    // 初期表示（現在の年）
    loadData(new Date().getFullYear());

    // タブクリック時の処理
    $('.nav-link').on('shown.bs.tab', function (e) {
        const year = $(e.target).data('year');
        loadData(year);
    });
});

function loadData(year) {
    $.ajax({
        url: '{{ route('makegraph3ajax') }}',
        method: 'GET',
        data: { tgtyear: year },
        success: function(data) {
            createChart(data, year);
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });
}
</script>
@endsection
