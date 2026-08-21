<?php

namespace App\Actions\Backups;

use App\Exceptions\BackupNotFoundException;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Config\Config;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class RestoreBackup
{
    public function __construct(protected Config $config) {}

    /**
     * Restore the MySQL database dump contained in a backup zip.
     *
     * spatie/laravel-backup does not provide restore functionality itself,
     * so this extracts the `db-dumps/*.sql` file from the archive and
     * pipes it into the `mysql` client using `--defaults-extra-file`
     * (credentials never touch argv or the environment) and a piped file
     * handle (never a shell-interpolated string), so nothing here is
     * built from string concatenation of user input.
     *
     * This only restores the database. File restoration from the zip's
     * source files is intentionally out of scope — restoring arbitrary
     * files from an uploaded/stored archive onto the live filesystem is
     * a much larger attack surface and should be a deliberate, separate
     * piece of work if it's needed.
     */
    public function handle(string $disk, string $filename): void
    {
        $filename = basename($filename);

        $destination = BackupDestinationFactory::createFromArray($this->config)
            ->first(fn ($destination) => $destination->diskName() === $disk);

        if ($destination === null) {
            throw new BackupNotFoundException("No backup destination configured for disk [{$disk}].");
        }

        $backup = $destination->backups()->first(
            fn ($backup) => basename($backup->path()) === $filename
        );

        if ($backup === null) {
            throw new BackupNotFoundException("Backup [{$filename}] was not found on disk [{$disk}].");
        }

        $temporaryDirectory = (new TemporaryDirectory)->create();

        try {
            $localZipPath = $temporaryDirectory->path('backup.zip');
            file_put_contents($localZipPath, Storage::disk($disk)->get($backup->path()));

            $extractPath = $temporaryDirectory->path('extracted');
            mkdir($extractPath, 0700, true);

            $zip = new ZipArchive;

            if ($zip->open($localZipPath) !== true) {
                throw new RuntimeException('Unable to open the backup archive.');
            }

            $zip->extractTo($extractPath);
            $zip->close();

            $sqlFiles = glob($extractPath.'/db-dumps/*.sql') ?: [];

            if (empty($sqlFiles)) {
                throw new RuntimeException('No database dump was found inside the backup archive.');
            }

            $this->importDump($sqlFiles[0], $temporaryDirectory);
        } finally {
            $temporaryDirectory->delete();
        }
    }

    /**
     * Import a plain-text SQL dump into the configured MySQL connection
     * via the `mysql` CLI client.
     */
    protected function importDump(string $sqlPath, TemporaryDirectory $temporaryDirectory): void
    {
        $connection = config('database.connections.mysql');

        $credentialsFile = $temporaryDirectory->path('mysql-credentials.cnf');

        file_put_contents($credentialsFile, sprintf(
            "[client]\nhost=%s\nport=%s\nuser=%s\npassword=%s\n",
            $connection['host'],
            $connection['port'],
            $connection['username'],
            $connection['password'],
        ));

        chmod($credentialsFile, 0600);

        $handle = fopen($sqlPath, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Unable to read the extracted SQL dump.');
        }

        try {
            $process = new Process([
                'mysql',
                '--defaults-extra-file='.$credentialsFile,
                $connection['database'],
            ]);

            $process->setInput($handle);
            $process->setTimeout(config('backup.backup.database_dump_timeout', 300));
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException('Database restore failed: '.$process->getErrorOutput());
            }
        } finally {
            fclose($handle);
        }
    }
}
