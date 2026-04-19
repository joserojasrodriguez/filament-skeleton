<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class SkeletonSetup extends Command
{
    // El comando que ejecutaremos
    protected $signature = 'db:skeleton-setup';
    protected $description = 'Configura el nombre de la DB en el .env y la crea en MySQL';

    public function handle()
    {
        $this->newLine();
        $dbName = $this->info('Base de datos. Asegúrate de que las credenciales de conexión en .env son correctas');
        $dbName = $this->ask('¿Cuál es el nombre de la base de datos? Deja en blanco para omitir este paso.');

        if (empty($dbName)) {
            $this->info('Parando el proceso de base de datos');
            return 0;
        }

        $this->updateDotEnv('DB_DATABASE', $dbName);

        config(['database.connections.mysql.database' => null]);

        DB::purge('mysql');
        DB::reconnect('mysql');

        try {
            $this->info("Creando base de datos '$dbName'...");

            $charset = config('database.connections.mysql.charset', 'utf8mb4');
            $collation = config('database.connections.mysql.collation', 'utf8mb4_unicode_ci');


            DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET $charset COLLATE $collation;");

            $this->info("✅ Base de datos '$dbName' creada con éxito.");
            config(['database.connections.mysql.database' => $dbName]);
            DB::purge('mysql');
            DB::reconnect('mysql');

            $this->info("Ejecutando migraciones...");
            $this->call('migrate', [
                '--force' => true
            ]);

            $this->info("Poblando base de datos (seeding)...");
            $this->call('db:seed', [
                '--force' => true
            ]);

            config(['database.connections.mysql.database' => $dbName]);
            DB::purge('mysql');

        } catch (\Exception $e) {
            $this->error("Error al crear la base de datos: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    protected function updateDotEnv($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $oldValue = env($key);
            // Reemplaza la línea exacta de DB_DATABASE
            file_put_contents($path, str_replace(
                "$key=" . $oldValue,
                "$key=" . $value,
                file_get_contents($path)
            ));
        }
    }
}