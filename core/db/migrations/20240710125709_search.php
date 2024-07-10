<?php

declare(strict_types=1);

use Vesp\Services\Migration;

final class Search extends Migration
{
    private int $try = 0;

    public function up(): void
    {
        try {
            $client = (new DI\Container())->get(App\Services\Manticore::class);
            $client->createIndex();
            $client->index();
        } catch (\Throwable $e) {
            if ($this->try < 3) {
                $this->try++;
                sleep(5); // Wait for Manticore container
                $this->up();
            } else {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        $client = (new DI\Container())->get(App\Services\Manticore::class);
        $client->getIndex()->drop(true);
    }
}
