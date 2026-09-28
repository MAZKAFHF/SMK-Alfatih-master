<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\PPDBRegistration;
use App\Models\PpdbPeriod;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Matriks kontrak field: DRAFT longgar, FINAL SUBMISSION ketat (server-side).
 * Setiap field wajib final diuji satu per satu: submit DITOLAK + tetap DRAF
 * + pesan Indonesia manusiawi.
 */
class PpdbFinalSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function period(): PpdbPeriod
    {
        return PpdbPeriod::first() ?? PpdbPeriod::create(['academic_year' => '2026/2027', 'is_open' => true]);
    }

    private function applicant(): User
    {
        return User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true, 'email_verified_at' => now()]);
    }

    /** Data lengkap yang LOLOS gerbang final. */
    public static function completeData(int $programId): array
    {
        return [
            'name' => 'Calon Lengkap',
            'nik' => '3201010105100001',
            'nisn' => '1234567890',
            'birth_place' => 'Bogor',
            'birth_date' => '2010-05-12',
            'gender' => 'laki-laki',
            'address' => 'Jl. Merdeka No. 1',
            'province' => 'Jawa Barat',
            'city' => 'Kota Bogor',
            'district' => 'Bogor Tengah',
            'village' => 'Pabaton',
            'postal_code' => '16121',
            'school_origin' => 'SMPN 1 Bogor',
            'father_name' => 'Ayah Lengkap',
            'father_phone' => '081234567891',
            'mother_name' => 'Ibu Lengkap',
            'mother_phone' => '081234567892',
            'program_id' => $programId,
        ];
    }

    private function completeApp(User $user, int $programId, array $overrides = []): PPDBRegistration
    {
        Storage::fake('ppdb_private');
        $app = PPDBRegistration::create(array_merge(
            static::completeData($programId),
            ['applicant_account_id' => $user->id, 'period_id' => $this->period()->id, 'source' => 'applicant'],
            $overrides
        ));
        \App\Services\DocumentService::ensurePlaceholders($app);
        foreach (['kk', 'ktp_ortu', 'akta', 'rapor'] as $type) {
            $file = UploadedFile::fake()->create($type.'.pdf', 200, 'application/pdf');
            \App\Services\DocumentService::store($app, \App\Enums\DocumentType::from($type), $file, $user->id);
        }
        $foto = UploadedFile::fake()->create('foto.jpg', 200, 'image/jpeg');
        \App\Services\DocumentService::store($app, \App\Enums\DocumentType::Foto, $foto, $user->id);

        return $app->fresh();
    }

    public function test_draft_partial_save_is_allowed(): void
    {
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        $this->actingAs($user)
            ->post('/portal/aplikasi', ['name' => 'Setengah', 'gender' => 'laki-laki', 'program_id' => $program->id])
            ->assertRedirect();
        $this->assertEquals(ApplicationStatus::Draft, PPDBRegistration::first()->application_status);
    }

    #[DataProvider('mandatoryFieldProvider')]
    public function test_each_mandatory_final_field_blocks_submission(string $field, ?string $expectedMessage): void
    {
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        $app = $this->completeApp($user, $program->id);
        // String kosong untuk kolom teks (lolos NOT NULL DB, gerbang
        // menormalisasi '' -> null sehingga `required` tetap diuji);
        // null untuk kolom date/FK yang nullable.
        $app->update([$field => in_array($field, ['birth_date', 'program_id'], true) ? null : '']);

        $response = $this->actingAs($user)->post(route('portal.applications.submit', $app), ['confirm' => 1]);
        $response->assertSessionHasErrors();
        $this->assertEquals(ApplicationStatus::Draft, $app->fresh()->application_status, "Field {$field} kosong harus tetap DRAF.");

        $msgs = collect(session('errors')->all());
        foreach ($msgs as $msg) {
            $this->assertDoesNotMatchRegularExpression('/^(validation|auth|passwords)\./', $msg);
        }
        if ($expectedMessage) {
            $this->assertContains($expectedMessage, $msgs->all());
        }
    }

    public static function mandatoryFieldProvider(): array
    {
        return [
            'nama' => ['name', 'Nama wajib diisi.'],
            'nik' => ['nik', 'NIK wajib diisi.'],
            'nisn' => ['nisn', 'NISN wajib diisi.'],
            'tempat lahir' => ['birth_place', 'Tempat lahir wajib diisi.'],
            'tanggal lahir' => ['birth_date', 'Tanggal lahir wajib diisi.'],
            'alamat' => ['address', 'Alamat wajib diisi.'],
            'provinsi' => ['province', 'Provinsi wajib diisi.'],
            'kota' => ['city', 'Kabupaten/Kota wajib diisi.'],
            'kecamatan' => ['district', 'Kecamatan wajib diisi.'],
            'desa' => ['village', 'Kelurahan/Desa wajib diisi.'],
            'kode pos' => ['postal_code', 'Kode pos wajib diisi.'],
            'sekolah' => ['school_origin', 'Asal sekolah wajib diisi.'],
            'ayah' => ['father_name', 'Nama ayah wajib diisi.'],
            'ibu' => ['mother_name', 'Nama ibu wajib diisi.'],
            'program' => ['program_id', 'Silakan pilih satu program keahlian.'],
        ];
    }

    public function test_nik_nisn_digit_rules(): void
    {
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        $app = $this->completeApp($user, $program->id);
        $app->update(['nik' => '123', 'nisn' => '456']);

        $this->actingAs($user)->post(route('portal.applications.submit', $app), ['confirm' => 1])->assertSessionHasErrors(['nik', 'nisn']);
        $msgs = session('errors')->all();
        $this->assertContains('NIK harus terdiri dari 16 digit.', $msgs);
        $this->assertContains('NISN harus terdiri dari 10 digit.', $msgs);
        $this->assertEquals(ApplicationStatus::Draft, $app->fresh()->application_status);
    }

    public function test_parent_contact_minimum_one(): void
    {
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        $app = $this->completeApp($user, $program->id);
        $app->update(['father_phone' => null, 'mother_phone' => null]);

        $this->actingAs($user)->post(route('portal.applications.submit', $app), ['confirm' => 1])->assertSessionHasErrors(['father_phone']);
        $this->assertContains('Isi minimal satu nomor kontak orang tua atau wali.', session('errors')->all());
    }

    public function test_guardian_conditional(): void
    {
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);

        // Wali sebagian -> ditolak.
        $app = $this->completeApp($user, $program->id, ['guardian_name' => 'Pak Wali']);
        $this->actingAs($user)->post(route('portal.applications.submit', $app), ['confirm' => 1])->assertSessionHasErrors(['guardian_phone']);

        // Wali lengkap -> lolos gerbang.
        $app->update(['guardian_phone' => '0819', 'guardian_relation' => 'Paman']);
        $this->actingAs($user)->post(route('portal.applications.submit', $app), ['confirm' => 1])->assertSessionHasNoErrors();
        $this->assertEquals(ApplicationStatus::Submitted, $app->fresh()->application_status);
    }

    public function test_missing_document_blocks_with_section_feedback(): void
    {
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        Storage::fake('ppdb_private');
        $app = PPDBRegistration::create(array_merge(
            static::completeData($program->id),
            ['applicant_account_id' => $user->id, 'period_id' => $this->period()->id, 'source' => 'applicant']
        ));
        // Tanpa dokumen sama sekali.
        $response = $this->from(route('portal.applications.review', $app))->actingAs($user)->post(route('portal.applications.submit', $app), ['confirm' => 1]);
        $response->assertSessionHasErrors();
        $msgs = session('errors')->all();
        $this->assertContains('Unggah Kartu Keluarga (KK).', $msgs);
        $this->assertContains('Unggah Foto siswa.', $msgs);
        foreach ($msgs as $msg) {
            $this->assertDoesNotMatchRegularExpression('/^(validation|auth|passwords)\./', $msg);
        }
        $this->assertEquals(ApplicationStatus::Draft, $app->fresh()->application_status);
        $follow = $this->followRedirects($response);
        $follow->assertDontSee('validation.', false);
        $follow->assertSee('Dokumen', false);
    }

    public function test_one_program_only_enforced(): void
    {
        $user = $this->applicant();
        $p1 = Program::factory()->create(['status' => 'active']);
        $p2 = Program::factory()->create(['status' => 'active']);
        $this->actingAs($user)->post('/portal/aplikasi', [
            'name' => 'Multi', 'gender' => 'laki-laki', 'program_id' => [$p1->id, $p2->id],
        ])->assertSessionHasErrors(['program_id']);
        $this->assertEquals(0, $user->applications()->count());
    }

    public function test_inactive_program_rejected_at_final(): void
    {
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'inactive']);
        $app = $this->completeApp($user, $program->id);
        $this->actingAs($user)->post(route('portal.applications.submit', $app), ['confirm' => 1])->assertSessionHasErrors(['program_id']);
        $this->assertEquals(ApplicationStatus::Draft, $app->fresh()->application_status);
    }

    public function test_illegal_transition_draft_to_decision_fails(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_superadmin' => true, 'is_active' => true]);
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        $app = $this->completeApp($user, $program->id);

        $this->actingAs($admin)->post(route('admin.registrations.decide', $app), ['result' => 'passed', 'confirm' => 1]);
        $this->assertEquals(ApplicationStatus::Draft, $app->fresh()->application_status);
        $this->assertNull($app->fresh()->decision);
    }
}
