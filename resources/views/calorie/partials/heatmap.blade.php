{{-- GitHub風の達成度ヒートマップ（草カレンダー） --}}
{{-- 引数: $year (対象年), $data (日付 => 確定摂取熱量) --}}
@php
    $hmToday = date('Y-m-d');

    // 確定摂取熱量から色クラスを決定（マイナスが大きいほど濃い緑、プラスは赤）
    $hmCellClass = function ($calorie) {
        if ($calorie === null) {
            return 'hm-none';
        }
        if ($calorie <= -400) {
            return 'hm-g3';
        }
        if ($calorie <= -150) {
            return 'hm-g2';
        }
        if ($calorie <= 0) {
            return 'hm-g1';
        }
        if ($calorie <= 200) {
            return 'hm-r1';
        }
        return 'hm-r2';
    };

    $hmStart  = new DateTime($year . '-01-01');
    $hmEnd    = new DateTime($year . '-12-31');
    $hmOffset = (int) $hmStart->format('w'); // 年初の曜日（日曜=0）

    $hmDates = [];
    for ($d = clone $hmStart; $d <= $hmEnd; $d->modify('+1 day')) {
        $hmDates[] = $d->format('Y-m-d');
    }

    // 月ラベルの表示位置（月初日が属する週列）
    $hmMonthCols = [];
    foreach ($hmDates as $i => $date) {
        if (substr($date, 8, 2) === '01') {
            $hmMonthCols[(int) substr($date, 5, 2)] = intdiv($hmOffset + $i, 7);
        }
    }
@endphp
<div class="heatmap-wrap mb-3">
    <div class="heatmap-scroll">
        <div class="heatmap-months">
            @foreach ($hmMonthCols as $m => $col)
                <span style="left: {{ $col * 15 }}px;">{{ $m }}月</span>
            @endforeach
        </div>
        <div class="heatmap-grid">
            @for ($i = 0; $i < $hmOffset; $i++)
                <div class="hm-cell hm-empty"></div>
            @endfor
            @foreach ($hmDates as $date)
                @if ($date > $hmToday)
                    <div class="hm-cell hm-empty"></div>
                @else
                    @php $c = $data[$date] ?? null; @endphp
                    <div class="hm-cell {{ $hmCellClass($c) }}"
                         title="{{ $date }}{{ $c !== null ? ' : ' . number_format($c) . ' kcal' : ' : 記録なし' }}"></div>
                @endif
            @endforeach
        </div>
        <div class="heatmap-legend text-muted">
            <span>超過</span>
            <div class="hm-cell hm-r2"></div>
            <div class="hm-cell hm-r1"></div>
            <div class="hm-cell hm-g1"></div>
            <div class="hm-cell hm-g2"></div>
            <div class="hm-cell hm-g3"></div>
            <span>達成</span>
            <span class="ml-2">（達成 = 確定摂取熱量が0以下）</span>
        </div>
    </div>
</div>
