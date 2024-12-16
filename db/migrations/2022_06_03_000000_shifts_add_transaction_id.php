<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class ShiftsAddTransactionId extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        if (!$this->schema->hasTable('Shifts')) {
            return;
        }

        $this->schema->table('Shifts', function (Blueprint $table): void {
            $table->uuid('transaction_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        if (!$this->schema->hasTable('Shifts')) {
            return;
        }

        $this->schema->table('Shifts', function (Blueprint $table): void {
            $table->dropColumn('transaction_id');
        });
    }
}
