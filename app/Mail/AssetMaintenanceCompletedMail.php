<?php

namespace App\Mail;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssetMaintenanceCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Asset $asset;

    public AssetMaintenance $maintenance;

    public function __construct(Asset $asset, AssetMaintenance $maintenance)
    {
        $this->asset = $asset;
        $this->maintenance = $maintenance;
    }

    public function build(): self
    {
        return $this->subject('Asset Maintenance Completed: '.$this->asset->asset_tag)
            ->html($this->renderHtml());
    }

    private function renderHtml(): string
    {
        $appName = config('app.name', 'Assetika');
        $assetTag = e($this->asset->asset_tag);
        $productName = e(optional($this->asset->product)->name ?? $this->asset->name ?? 'IT Asset');
        $provider = e($this->maintenance->service_provider ?? 'Internal IT Service');
        $completionDate = $this->maintenance->completion_date ? $this->maintenance->completion_date->format('d M Y') : date('d M Y');
        $cost = number_format((float) ($this->maintenance->cost ?? 0), 2);
        $resolutionNotes = e($this->maintenance->notes ?? '-');

        return "
        <div style=\"font-family: Arial, sans-serif; background-color: #f8fafc; padding: 24px;\">
            <div style=\"max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;\">
                <div style=\"background-color: #12b76a; padding: 20px; text-align: center;\">
                    <h2 style=\"color: #ffffff; margin: 0;\">{$appName} - Maintenance Completed</h2>
                </div>
                <div style=\"padding: 24px; color: #1e293b;\">
                    <p>Hello,</p>
                    <p>Maintenance / service for the IT asset has been successfully completed and the unit status is updated to <strong>Ready to Deploy</strong>.</p>

                    <table style=\"width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 14px;\">
                        <tr><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold;\">Asset Tag</td><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9;\">{$assetTag}</td></tr>
                        <tr><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold;\">Product / Item</td><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9;\">{$productName}</td></tr>
                        <tr><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold;\">Service Provider</td><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9;\">{$provider}</td></tr>
                        <tr><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold;\">Completion Date</td><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9;\">{$completionDate}</td></tr>
                        <tr><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold;\">Total Cost</td><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9;\">{$cost}</td></tr>
                        <tr><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold;\">Resolution Notes</td><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9;\">{$resolutionNotes}</td></tr>
                    </table>

                    <p style=\"font-size: 13px; color: #64748b;\">The hardware is now available for deployment in IT inventory.</p>
                </div>
                <div style=\"background-color: #f1f5f9; padding: 12px; text-align: center; font-size: 12px; color: #94a3b8;\">
                    &copy; ".date('Y')." {$appName}. All rights reserved.
                </div>
            </div>
        </div>";
    }
}
