<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverview;
use Filament\Widgets\AccountWidget;

class CustomDashboard extends \Filament\Pages\Dashboard
{
    // ...


    public function getWidgets(): array
    {
        return [
            AccountWidget::class,
            StatsOverview::class,

        ];
    }
}
