<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mappings = [
            'lucide-check-circle' => 'check-circle',
            'lucide-user' => 'user',
            'lucide-x-circle' => 'x-circle',
            'lucide-wrench' => 'tool',
            'lucide-archive' => 'archive',
            'lucide-file-text' => 'file-text',
            'lucide-info' => 'info',
            'lucide-circle-check' => 'check-circle',
            'lucide-circle-off' => 'x-circle',
            'lucide-package-x' => 'package',
            'lucide-shield-plus' => 'shield',
            'lucide-hourglass' => 'clock',
            'lucide-file-down' => 'download-cloud',
            'lucide-laptop' => 'monitor',
        ];

        foreach ($mappings as $old => $new) {
            DB::table('statuses')->where('icon', $old)->update(['icon' => $new]);
        }

        DB::table('statuses')->where('icon', 'like', 'lucide-%')->update([
            'icon' => DB::raw("REPLACE(icon, 'lucide-', '')"),
        ]);
    }

    public function down(): void
    {
        DB::table('statuses')->whereNotNull('icon')->update([
            'icon' => DB::raw("CONCAT('lucide-', icon)"),
        ]);
    }
};