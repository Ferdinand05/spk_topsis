<?php

namespace App\Filament\Resources\Alternatives;

use App\Filament\Resources\Alternatives\Pages\ManageAlternatives;
use App\Models\Alternative;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class AlternativeResource extends Resource
{
    protected static ?string $model = Alternative::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;
    protected static ?string $navigationLabel = 'Alternatif';
    protected static string | UnitEnum | null $navigationGroup = 'Data';
    protected static ?int $navigationSort = 2;
    protected static ?string $label = 'Alternatif';


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make("code")
                    ->label("Kode")
                    ->required()
                    ->default(function () {
                        $last = Alternative::query()->latest()->first();

                        $number = $last ? ((int) substr($last->code, 1)) + 1 : 1;

                        $code = 'A' . str_pad($number, 2, '0', STR_PAD_LEFT);

                        return $code;
                    }),
                TextInput::make("name")
                    ->label("Name")
                    ->required(),
                Select::make("calculation_id")
                    ->label("Nama Perhitungan")
                    ->required()
                    ->relationship("calculation", "name")
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("code")
                    ->label("Kode")
                    ->sortable(),
                TextColumn::make("name")
                    ->label("Nama"),
                TextColumn::make("calculation.name")
                    ->label("Nama Perhitungan")


            ])
            ->filters([
                SelectFilter::make("filterCalculation")
                    ->label("Filter Perhitungan")
                    ->relationship('calculation', "name")
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAlternatives::route('/'),
        ];
    }
}
