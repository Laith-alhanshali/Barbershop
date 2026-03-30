<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use BackedEnum;

class BackupCenter extends Page
{
    protected static  string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';
    // protected static string|BackedEnum|null $navigationGroup = 'Settings';
    protected string $view = 'filament.pages.backup-center';

    public function getHeaderActions(): array
    {
        return [
           Action::make('backup')
    ->label('Create Backup')
    ->icon('heroicon-o-cloud-arrow-up')
    ->requiresConfirmation()
    ->action(function () {
        $exitCode = Artisan::call('backup:run');
        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            Notification::make()
                ->title('Backup failed')
                ->body('Cannot create a backup contact the Administrator')
                ->danger()
                ->send();

            return;
        }

        // حاول تجيب آخر ملف zip تم إنشاؤه
        $disk = Storage::disk('backups'); // أو local حسب إعدادك
        $appName = config('backup.backup.name'); // نفس الموجود في config/backup.php

        $latest = collect($disk->allFiles($appName))
            ->filter(fn ($f) => str_ends_with($f, '.zip'))
            ->sortDesc()
            ->first();

        Notification::make()
            ->title('Backup created successfully')
            ->body($latest ? "File: " . basename($latest) : "No zip found in disk path: {$appName}")
            ->success()
            ->send();
    }),
        ];
    }

    public function backups(): array
    {
        $disk = Storage::disk('backups');
        $appName = config('backup.backup.name');

        if (! $disk->exists($appName)) {
            return [];
        }

        return collect($disk->files($appName))
            ->filter(fn ($file) => str_ends_with($file, '.zip'))
            ->sortDesc()
            ->map(fn ($file) => [
                'path' => $file,
                'name' => basename($file),
                'size' => $disk->size($file),
                'date' => date('Y-m-d H:i:s', $disk->lastModified($file)),
            ])
            ->values()
            ->toArray();
    }

    public function download(string $path)
{
    $disk = Storage::disk('backups');

    abort_unless($disk->exists($path), 404);

    return response()->download(
        $disk->path($path),
        basename($path),
        ['Content-Type' => 'application/zip']
    );
}

}
