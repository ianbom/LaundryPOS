<?php

use App\Models\BusinessSetting;
use App\Models\Outlet;
use App\Models\User;
use App\Models\UserOutlet;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

function phase2OwnerUser(array $attributes = []): User
{
    return User::factory()->create([
        'global_role' => 'owner',
        'is_active' => true,
        ...$attributes,
    ]);
}

function phase2StaffUser(array $attributes = []): User
{
    return User::factory()->create([
        'global_role' => 'staff',
        'is_active' => true,
        ...$attributes,
    ]);
}

test('owner can view and update business settings with default row creation', function () {
    Storage::fake('public');

    $owner = phase2OwnerUser();

    $this->actingAs($owner)
        ->get(route('settings.business.edit'))
        ->assertOk();

    expect(BusinessSetting::query()->count())->toBe(1);

    $this->actingAs($owner)
        ->put(route('settings.business.update'), [
            'business_name' => 'Bersih Laundry',
            'business_slug' => 'bersih-laundry',
            'logo_path' => UploadedFile::fake()->image('logo.png'),
            'favicon_path' => UploadedFile::fake()->image('favicon.png'),
            'owner_name' => 'Owner Bersih',
            'owner_phone' => '08123456789',
            'owner_email' => 'owner@example.com',
            'default_phone' => '031123456',
            'default_whatsapp_number' => '628123456789',
            'default_email' => 'hello@example.com',
            'default_address' => 'Jl. Bersih No. 1',
            'default_google_maps_url' => 'https://maps.google.com/?q=bersih',
            'timezone' => 'Asia/Jakarta',
            'currency' => 'IDR',
            'receipt_footer_text' => 'Terima kasih',
            'terms_and_conditions' => 'Syarat berlaku',
            'qris_expiry_minutes' => 45,
        ])
        ->assertRedirect(route('settings.business.edit'));

    $setting = BusinessSetting::query()->firstOrFail();

    expect($setting->business_name)->toBe('Bersih Laundry')
        ->and($setting->timezone)->toBe('Asia/Jakarta')
        ->and($setting->currency)->toBe('IDR')
        ->and($setting->qris_expiry_minutes)->toBe(45)
        ->and($setting->logo_path)->not->toBeNull()
        ->and($setting->favicon_path)->not->toBeNull();
});

test('integration settings keep existing secrets when empty values are submitted', function () {
    $owner = phase2OwnerUser();

    $setting = BusinessSetting::query()->create([
        'business_name' => 'Bersih Laundry',
        'whatsapp_api_key' => 'old-whatsapp-key',
        'midtrans_server_key' => 'old-server-key',
        'midtrans_client_key' => 'old-client-key',
    ]);

    $this->actingAs($owner)
        ->put(route('settings.integrations.update'), [
            'whatsapp_provider' => 'fonnte',
            'whatsapp_api_key' => '',
            'whatsapp_sender_number' => '628123456789',
            'midtrans_server_key' => '',
            'midtrans_client_key' => '',
            'midtrans_is_production' => true,
            'qris_expiry_minutes' => 60,
        ])
        ->assertRedirect(route('settings.integrations.edit'));

    $setting->refresh();

    expect($setting->whatsapp_api_key)->toBe('old-whatsapp-key')
        ->and($setting->midtrans_server_key)->toBe('old-server-key')
        ->and($setting->midtrans_client_key)->toBe('old-client-key')
        ->and($setting->midtrans_is_production)->toBeTrue()
        ->and($setting->qris_expiry_minutes)->toBe(60);
});

test('owner can create main outlet and set another outlet as main', function () {
    $owner = phase2OwnerUser();

    $this->actingAs($owner)
        ->post(route('outlets.store'), [
            'name' => 'Central Surabaya',
            'code' => 'SBY',
            'slug' => 'central-surabaya',
            'phone' => '031123456',
            'whatsapp_number' => '628123456789',
            'email' => 'sby@example.com',
            'address' => 'Surabaya',
            'google_maps_url' => 'https://maps.google.com/?q=sby',
            'is_main' => true,
            'is_active' => true,
        ])
        ->assertRedirect(route('outlets.index'));

    $firstOutlet = Outlet::query()->where('slug', 'central-surabaya')->firstOrFail();
    $secondOutlet = Outlet::query()->create([
        'name' => 'Gresik',
        'code' => 'GRS',
        'slug' => 'gresik',
        'is_main' => false,
        'is_active' => true,
    ]);

    $this->actingAs($owner)
        ->patch(route('outlets.set-main', $secondOutlet))
        ->assertRedirect();

    expect($firstOutlet->refresh()->is_main)->toBeFalse()
        ->and($secondOutlet->refresh()->is_main)->toBeTrue();
});

test('owner cannot deactivate the last active owner', function () {
    $owner = phase2OwnerUser();

    $this->actingAs($owner)
        ->patch(route('users.toggle-active', $owner))
        ->assertSessionHasErrors('user');

    expect($owner->refresh()->is_active)->toBeTrue();
});

test('owner can create user reset password and assign outlets', function () {
    $owner = phase2OwnerUser();
    $outlet = Outlet::query()->create([
        'name' => 'Central',
        'slug' => 'central',
        'is_active' => true,
    ]);
    $target = phase2StaffUser();

    $this->actingAs($owner)
        ->put(route('users.outlets.update', $target), [
            'outlets' => [
                [
                    'outlet_id' => $outlet->id,
                    'role' => 'cashier',
                    'can_manage_orders' => true,
                    'can_manage_payments' => true,
                    'can_manage_services' => false,
                    'can_manage_reports' => false,
                    'can_manage_users' => false,
                    'can_manage_settings' => false,
                    'is_primary' => true,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertRedirect(route('users.show', $target));

    expect(UserOutlet::query()->whereBelongsTo($target)->count())->toBe(1);

    $this->actingAs($owner)
        ->patch(route('users.reset-password', $target), [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect(route('users.show', $target));

    expect(Hash::check('new-password', $target->refresh()->password))->toBeTrue();
});

test('current outlet can only be switched to accessible active outlet', function () {
    $user = phase2StaffUser();
    $allowed = Outlet::query()->create([
        'name' => 'Allowed',
        'slug' => 'allowed',
        'is_active' => true,
    ]);
    $denied = Outlet::query()->create([
        'name' => 'Denied',
        'slug' => 'denied',
        'is_active' => true,
    ]);

    UserOutlet::query()->create([
        'user_id' => $user->id,
        'outlet_id' => $allowed->id,
        'role' => 'cashier',
        'is_active' => true,
        'is_primary' => true,
    ]);

    $this->actingAs($user)
        ->post(route('current-outlet.update'), ['outlet_id' => $allowed->id])
        ->assertRedirect();

    expect(session('current_outlet_id'))->toBe($allowed->id);

    $this->actingAs($user)
        ->post(route('current-outlet.update'), ['outlet_id' => $denied->id])
        ->assertSessionHasErrors('outlet_id');
});
