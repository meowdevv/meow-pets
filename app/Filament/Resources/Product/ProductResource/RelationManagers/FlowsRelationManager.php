<?php

namespace App\Filament\Resources\Product\ProductResource\RelationManagers;

use App\Models\ProductFlow;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FlowsRelationManager extends RelationManager
{
    protected static string $relationship = 'flows';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                ->badge()
                    ->getStateUsing(fn (ProductFlow $record): string => $record->type)
                    ->colors([
                        'success' => 'IN',
                        'danger' => 'OUT',
                    ]),
                Tables\Columns\TextColumn::make('amount')->numeric(),
                Tables\Columns\TextColumn::make('desc'),
                Tables\Columns\TextColumn::make('mutate_at')->datetime(),

            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Mutation type')
                    ->options([
                        'IN' => 'IN',
                        'OUT' => 'OUT',
                    ]),
                Filter::make('mutate_at')
                    ->form([DatePicker::make('mutate_at')])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['mutate_at'], fn (Builder $query, $date): Builder => $query->whereDate('mutate_at', '>=', $date));
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
