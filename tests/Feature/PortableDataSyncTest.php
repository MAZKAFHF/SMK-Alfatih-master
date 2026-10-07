<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortableDataSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_round_trip_restores_persistent_data_and_excludes_sessions(): void
    {
        $user = User::factory()->create(['email' => 'snapshot@example.test']);
        $path = storage_path('framework/testing/portable-data.json.gz');
        @unlink($path);

        $this->artisan('app:data-export', ['path' => $path])->assertExitCode(0);

        $user->delete();
        $this->assertDatabaseMissing('users', ['email' => 'snapshot@example.test']);

        $this->artisan('app:data-import', ['path' => $path, '--force' => true])->assertExitCode(0);

        $this->assertDatabaseHas('users', ['email' => 'snapshot@example.test']);
        $payload = json_decode(gzdecode((string) file_get_contents($path)), true, flags: JSON_THROW_ON_ERROR);
        $this->assertArrayNotHasKey('sessions', $payload['tables']);
        $this->assertArrayNotHasKey('password_reset_tokens', $payload['tables']);

        @unlink($path);
    }

    public function test_import_requires_force(): void
    {
        $this->artisan('app:data-import', ['path' => 'missing.json.gz'])->assertExitCode(1);
    }
}
