<?php

namespace Tests\Feature;

use App\Enums\DocumentType;
use App\Models\PPDBRegistration;
use App\Models\Program;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_response_has_hardened_browser_headers(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin')
            ->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
    }

    public function test_portal_login_locks_the_target_account_across_different_ips(): void
    {
        RateLimiter::clear('portal-login-account|'.hash('sha256', 'target@example.com'));

        foreach (range(1, 6) as $attempt) {
            $this->withServerVariables(['REMOTE_ADDR' => "198.51.100.{$attempt}"])
                ->post(route('portal.login.store'), [
                    'email' => 'target@example.com',
                    'password' => 'wrong-password',
                ]);
        }

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post(route('portal.login.store'), [
                'email' => 'target@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('email');
    }

    public function test_private_document_preview_is_not_cacheable_and_is_sandboxed(): void
    {
        Storage::fake('ppdb_private');
        $user = User::factory()->create(['is_admin' => false, 'is_applicant' => true]);
        $program = Program::factory()->create();
        $application = PPDBRegistration::factory()->create([
            'program_id' => $program->id,
            'applicant_account_id' => $user->id,
        ]);
        $document = DocumentService::store(
            $application,
            DocumentType::Akta,
            UploadedFile::fake()->create('akta siswa.pdf', 10, 'application/pdf'),
            $user->id
        );

        $response = $this->actingAs($user)
            ->get(route('portal.documents.preview', $document))
            ->assertOk()
            ->assertHeader('Content-Security-Policy', "sandbox; default-src 'none'")
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);
    }
}
