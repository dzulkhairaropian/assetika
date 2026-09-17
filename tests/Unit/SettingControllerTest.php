<?php

namespace Tests\Unit;

use App\Helpers\AppSetting;
use Tests\TestCase;

class SettingControllerTest extends TestCase
{
    public function test_app_setting_helper_returns_default_values()
    {
        $all = AppSetting::all();
        $this->assertArrayHasKey('app_name', $all);
        $this->assertArrayHasKey('favicon', $all);
        $this->assertArrayHasKey('logo_light', $all);
        $this->assertArrayHasKey('logo_dark', $all);
    }
}
