<?php

namespace App\Filament\Widgets;

use App\Models\Alternative;
use App\Models\Calculation;
use App\Models\Criteria;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{

    protected ?string $heading = 'Statistik';

    protected ?string $description = 'Gambaran umum mengenai beberapa statistik.';

    protected function getStats(): array
    {
        return [
            Stat::make('Alternatif', Alternative::count())
                ->description("Data Alternatif yang tersedia")
                ->icon(Heroicon::DocumentText)
                ->color('success'),
            Stat::make('Kriteria', Criteria::count())
                ->description("Data Kriteria yang tersedia")
                ->icon(Heroicon::DocumentDuplicate)
                ->color('info'),
            Stat::make('Jenis Perhitungan', Calculation::count())
                ->description("Data Jenis Perhitungan yang tersedia")
                ->icon(Heroicon::ClipboardDocumentList)
                ->color('warning'),

        ];
    }
}
