<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Asap rute: tidak ada 500/404 tak terduga di rute penting.
 * Guest boleh redirect (302 ke login); yang dilarang: 500.
 */
class RouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('guestRoutesProvider')]
    public function test_guest_routes_do_not_error(string $method, string $uri, array $okStatuses): void
    {
        $response = $this->call($method, $uri);
        $this->assertContains(
            $response->getStatusCode(), $okStatuses,
            "Rute {$method} {$uri} meledak: ".$response->getStatusCode()
        );
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    public static function guestRoutesProvider(): array
    {
        return [
            'home' => ['GET', '/', [200]],
            'programs' => ['GET', '/program-keahlian', [200]],
            'news' => ['GET', '/berita', [200]],
            'gallery' => ['GET', '/galeri', [200]],
            'announcements' => ['GET', '/pengumuman', [200]],
            'contact' => ['GET', '/kontak', [200]],
            'ppdb landing' => ['GET', '/ppdb', [200]],
            'removed public form redirect' => ['GET', '/ppdb/siswa', [301]],
            'removed status checker redirect' => ['GET', '/ppdb/status', [301]],
            'portal register' => ['GET', '/portal/daftar', [200]],
            'portal login' => ['GET', '/portal/masuk', [200]],
            'portal dashboard guest' => ['GET', '/portal', [302]],
            'admin login' => ['GET', '/admin/login', [200]],
            'admin dashboard guest' => ['GET', '/admin', [302]],
            'health' => ['GET', '/health', [200]],
            'sitemap' => ['GET', '/sitemap.xml', [200]],
        ];
    }

    public function test_admin_key_routes_render_without_500(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_superadmin' => true, 'is_active' => true]);
        Program::factory()->create(['status' => 'active']);
        $this->actingAs($admin);

        foreach ([
            '/', '/registrations', '/registrations/create', '/interview-slots',
            '/periods', '/periods/create', '/programs', '/news', '/galleries',
            '/announcements', '/pages', '/contact-messages', '/settings',
            '/trash', '/audit-logs', '/users', '/login-logs',
        ] as $uri) {
            $response = $this->get('/admin'.$uri);
            $this->assertContains($response->getStatusCode(), [200, 302], "Admin {$uri}: ".$response->getStatusCode());
            $this->assertNotEquals(500, $response->getStatusCode(), "Admin {$uri} 500!");
        }
    }

    public function test_portal_key_routes_render_without_500(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true, 'email_verified_at' => now()]);
        $this->actingAs($user);
        $this->get('/portal')->assertOk();
        $this->get('/portal/aplikasi/baru')->assertOk();
        $this->get('/portal/notifikasi')->assertOk();
    }
}
