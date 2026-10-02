<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_bisa_login_dan_mendapatkan_token_sanctum(): void
    {
        $user = User::factory()->create([
            'email' => 'api-user@viwb.test',
            'password' => 'secret123',
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => 'api-user@viwb.test',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);
    }

    public function test_login_gagal_dengan_password_salah(): void
    {
        User::factory()->create([
            'email' => 'api-user@viwb.test',
            'password' => 'secret123',
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => 'api-user@viwb.test',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_bisa_mengakses_endpoint_me_dengan_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.me'));

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_user_bisa_logout_dan_mencabut_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.logout'));

        $response->assertOk();
        $this->assertDatabaseEmpty('personal_access_tokens');
    }

    public function test_api_v1_version_prefix_berfungsi_dengan_baik(): void
    {
        $user = User::factory()->create([
            'email' => 'v1-user@viwb.test',
            'password' => 'secret123',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'v1-user@viwb.test',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);

        $token = $response->json('token');

        $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me');

        $meResponse->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }
}
