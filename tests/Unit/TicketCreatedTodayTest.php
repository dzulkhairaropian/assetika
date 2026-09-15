<?php

namespace Tests\Unit;

use App\Exports\TicketsExport;
use Tests\TestCase;

class TicketCreatedTodayTest extends TestCase
{
    public function test_tickets_export_supports_created_today_tab_filter()
    {
        $export = new TicketsExport(['tab' => 'created_today']);
        $headings = $export->headings();

        $this->assertContains('Created Date', $headings);
        $this->assertContains('Request Date', $headings);
    }
}
