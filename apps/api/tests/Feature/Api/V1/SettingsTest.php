<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_get_settings(): void
    {
        Setting::set('garage_name', 'Garage Mékano');
        Setting::set('whatsapp_number', '+237699000000');

        $response = $this->getJson('/api/v1/settings');

        $response->assertOk()
            ->assertJsonPath('data.garage_name', 'Garage Mékano')
            ->assertJsonPath('data.whatsapp_number', '+237699000000');
    }

    public function test_admin_settings_requires_auth(): void
    {
        $this->getJson('/api/v1/admin/settings')->assertStatus(401);
        $this->patchJson('/api/v1/admin/settings', ['settings' => []])->assertStatus(401);
    }

    public function test_admin_can_update_settings_in_bulk(): void
    {
        $admin = User::factory()->create();
        $token = $admin->createToken('admin')->plainTextToken;

        $response = $this->withToken($token)->patchJson('/api/v1/admin/settings', [
            'settings' => [
                'garage_name' => 'Garage Mékano Yaoundé',
                'whatsapp_number' => '+237677000000',
                'hero_title' => 'Votre spécialiste automobile au Cameroun',
                'address' => 'Bastos, Yaoundé',
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.garage_name', 'Garage Mékano Yaoundé')
            ->assertJsonPath('data.whatsapp_number', '+237677000000')
            ->assertJsonPath('data.hero_title', 'Votre spécialiste automobile au Cameroun');

        $this->assertEquals('Garage Mékano Yaoundé', Setting::get('garage_name'));
        $this->assertEquals('+237677000000', Setting::get('whatsapp_number'));
    }
}
