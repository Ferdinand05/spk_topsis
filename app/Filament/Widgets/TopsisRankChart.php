<?php

namespace App\Filament\Widgets;

use App\Models\Calculation;
use App\Models\Result;
use Filament\Widgets\ChartWidget;

class TopsisRankChart extends ChartWidget
{
    protected ?string $heading = 'Topsis Rank Chart';
    protected ?string $pollingInterval = '4s';
    protected  ?string $maxHeight = '200px';

    public $stats = [];

    protected function getData(): array
    {
        if (empty($this->stats['calculation_id'])) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $results = Result::with('alternative')
            ->where('calculation_id', $this->stats['calculation_id'])
            ->orderBy('rank')
            ->get();

        $calculation = Calculation::find($this->stats['calculation_id']);

        return [
            'datasets' => [
                [
                    'label' => "Preferensi (v)",
                    'data' => $results->pluck('score'), // ✅ ini yang benar
                ],
            ],
            'labels' => $results->pluck('alternative.name'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
