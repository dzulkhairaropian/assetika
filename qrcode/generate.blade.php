@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">QR Code Generator</div>
                
                <div class="card-body text-center">
                    <h3>Example QR Code</h3>
                    <div class="qr-code-container">
                        {!! $qrCode !!}
                    </div>
                    
                    <p class="mt-3">Scan this QR code with your phone to visit the example website.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection