<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('arbitrary executable upload is rejected for ticket attachment', function () {
    $company = Company::create(['name' => 'Sec Corp', 'code' => 'SC']);
    $user = User::factory()->create(['company_id' => $company->id]);
    Role::firstOrCreate(['name' => 'user']);
    $user->assignRole('user');
    $this->actingAs($user);

    $maliciousFile = UploadedFile::fake()->create('backdoor.php', 100, 'application/x-php');

    $response = $this->post(route('tickets.store'), [
        'problem_description' => 'Suspicious test payload',
        'attachment' => $maliciousFile,
    ]);

    $response->assertSessionHasErrors('attachment');
});

test('unprivileged user cannot execute administrative bulk delete routes', function () {
    $company = Company::create(['name' => 'Sec Corp', 'code' => 'SC']);
    $user = User::factory()->create(['company_id' => $company->id]);
    Role::firstOrCreate(['name' => 'user']);
    $user->assignRole('user');
    $this->actingAs($user);

    $assetDeleteResponse = $this->post(route('assets.bulk-delete'), [
        'ids' => [1, 2],
    ]);
    $assetDeleteResponse->assertForbidden();

    $companyDeleteResponse = $this->post(route('companies.bulk-delete'), [
        'ids' => [1, 2],
    ]);
    $companyDeleteResponse->assertForbidden();
});

test('security headers middleware applies protective headers', function () {
    $response = $this->get('/signin');

    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $this->assertTrue($response->headers->has('Content-Security-Policy'));
});
