<?php

namespace App\Mail;

use App\Models\Asset;
use App\Models\AssetCheckout;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssetCheckoutMail extends Mailable
{
    use Queueable, SerializesModels;

    public Asset $asset;

    public AssetCheckout $checkout;

    public function __construct(Asset $asset, AssetCheckout $checkout)
    {
        $this->asset = $asset;
        $this->checkout = $checkout;
    }

    public function build(): self
    {
        return $this->subject('Asset Handover Notice: '.$this->asset->asset_tag)
            ->html($this->renderHtml());
    }

    private function renderHtml(): string
    {
        $appName = config('app.name', 'Assetika');
        $assetTag = e($this->asset->asset_tag);
        $productName = e(optional($this->asset->product)->name ?? $this->asset->name ?? 'IT Asset');
        $userName = e(optional($this->checkout->checkedOutToUser)->name ?? 'User');
        $checkoutDate = $this->checkout->checked_out_at ? $this->checkout->checked_out_at->format('d M Y, H:i') : date('d M Y');
        $notes = e($this->checkout->checkout_notes ?? '-');

        return "
        <div style=\"font-family: Arial, sans-serif; background-color: #f8fafc; padding: 24px;\">
            <div style=\"max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;\">
                <div style=\"background-color: #465fff; padding: 20px; text-align: center;\">
                    <h2 style=\"color: #ffffff; margin: 0;\">{$appName} - Asset Handover Notice</h2>
                </div>
                <div style=\"padding: 24px; color: #1e293b;\">
                    <p>Hello <strong>{$userName}</strong>,</p>
                    <p>An IT asset has been successfully assigned to you. Below are the handover details:</p>

                    <table style=\"width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 14px;\">
                        <tr><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold;\">Asset Tag</td><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9;\">{$assetTag}</td></tr>
                        <tr><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold;\">Product / Item</td><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9;\">{$productName}</td></tr>
                        <tr><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold;\">Checkout Date</td><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9;\">{$checkoutDate}</td></tr>
                        <tr><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold;\">Notes</td><td style=\"padding: 8px; border-bottom: 1px solid #f1f5f9;\">{$notes}</td></tr>
                    </table>

                    <p style=\"font-size: 13px; color: #64748b;\">Please take good care of the assigned hardware. Contact the IT Service Desk if you notice any discrepancies.</p>
                </div>
                <div style=\"background-color: #f1f5f9; padding: 12px; text-align: center; font-size: 12px; color: #94a3b8;\">
                    &copy; ".date('Y')." {$appName}. All rights reserved.
                </div>
            </div>
        </div>";
    }
}
