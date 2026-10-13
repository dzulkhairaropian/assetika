<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('tickets', 'subject')) {
                $table->string('subject')->nullable()->after('request_id');
            }
            if (! Schema::hasColumn('tickets', 'first_responded_at')) {
                $table->timestamp('first_responded_at')->nullable()->after('completed_time');
            }
        });

        // Safely backfill subject for existing tickets without re-inserting or deleting
        if (Schema::hasTable('tickets') && Schema::hasColumn('tickets', 'subject')) {
            DB::table('tickets')
                ->whereNull('subject')
                ->orWhere('subject', '')
                ->chunkById(100, function ($tickets) {
                    foreach ($tickets as $ticket) {
                        $desc = (string) ($ticket->problem_description ?? '');
                        $firstLine = trim(strtok($desc, "\r\n") ?: $desc);
                        $subject = Str::limit($firstLine ?: ('Ticket #'.$ticket->request_id), 120);

                        DB::table('tickets')
                            ->where('id', $ticket->id)
                            ->update(['subject' => $subject]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['subject', 'first_responded_at']);
        });
    }
};
