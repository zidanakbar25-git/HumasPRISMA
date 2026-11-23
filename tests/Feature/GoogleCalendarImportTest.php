<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\JadwalKegiatan\GoogleCalendarServiceInterface;
use App\Enums\Role;
use App\Enums\SumberKegiatan;
use App\Livewire\Shared\JadwalKegiatan\JadwalKegiatanIndex;
use App\Models\JadwalKegiatan\Kegiatan;
use App\Models\ManajemenUser\User;
use App\Services\JadwalKegiatan\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class GoogleCalendarImportTest extends TestCase
{
    use RefreshDatabase;

    private User $kabag;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kabag = User::factory()->create([
            'name' => 'Kepala Bagian Humas',
            'username' => 'kabag',
            'role' => Role::Kabag,
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'name' => 'Staff Humas',
            'username' => 'staff',
            'role' => Role::Staff,
            'is_active' => true,
        ]);
    }

    public function test_only_kabag_can_access_google_calendar_redirect(): void
    {
        // Guest redirects to login
        $this->get(route('kabag.google-calendar.redirect'))
            ->assertRedirect(route('login'));

        // Staff gets 403
        $this->actingAs($this->staff)
            ->get(route('kabag.google-calendar.redirect'))
            ->assertStatus(403);

        // Kabag redirects to Google OAuth
        $response = $this->actingAs($this->kabag)
            ->get(route('kabag.google-calendar.redirect'));

        $response->assertRedirect();
        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('accounts.google.com/o/oauth2/auth', $targetUrl);
        $this->assertStringContainsString('prompt=select_account', $targetUrl);
        $this->assertStringContainsString('calendar.readonly', $targetUrl);
    }

    public function test_google_calendar_service_parses_events_correctly(): void
    {
        $fakeEvents = [
            'items' => [
                // 1. Regular event with specific time
                [
                    'id' => 'event-1-regular',
                    'status' => 'confirmed',
                    'summary' => 'Rapat Koordinasi Media',
                    'location' => 'Ruang Rapat Utama',
                    'description' => 'Membahas agenda mingguan biro humas',
                    'start' => ['dateTime' => '2026-11-20T09:00:00+07:00'],
                    'end' => ['dateTime' => '2026-11-20T11:30:00+07:00'],
                ],
                // 2. All-day event (seharian)
                [
                    'id' => 'event-2-allday',
                    'status' => 'confirmed',
                    'summary' => 'Pameran Pembangunan Daerah',
                    'location' => 'Alun-alun Kota',
                    'description' => 'Liputan stan biro humas',
                    'start' => ['date' => '2026-11-22'],
                    'end' => ['date' => '2026-11-23'],
                ],
                // 3. Multi-day event spanning several days
                [
                    'id' => 'event-3-multiday',
                    'status' => 'confirmed',
                    'summary' => 'Kunjungan Kerja Luar Kota',
                    'location' => 'Kabupaten Sebelah',
                    'description' => 'Pendampingan pimpinan selama 3 hari',
                    'start' => ['dateTime' => '2026-11-25T08:00:00+07:00'],
                    'end' => ['dateTime' => '2026-11-28T17:00:00+07:00'],
                ],
                // 4. Cancelled event (should be skipped)
                [
                    'id' => 'event-4-cancelled',
                    'status' => 'cancelled',
                    'summary' => 'Acara Dibatalkan',
                ],
            ],
        ];

        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events*' => Http::response($fakeEvents, 200),
        ]);

        $service = new GoogleCalendarService();
        $result = $service->fetchEvents('fake-access-token');

        $this->assertNull($result['error']);
        $this->assertCount(3, $result['events']);

        // Check regular event
        $this->assertSame('event-1-regular', $result['events'][0]['id']);
        $this->assertSame('Rapat Koordinasi Media', $result['events'][0]['judul']);
        $this->assertSame('2026-11-20', $result['events'][0]['tanggal']);
        $this->assertSame('09:00', $result['events'][0]['waktu_mulai']);
        $this->assertSame('11:30', $result['events'][0]['waktu_selesai']);
        $this->assertSame('Ruang Rapat Utama', $result['events'][0]['tempat']);

        // Check all-day event (tanpa jam)
        $this->assertSame('event-2-allday', $result['events'][1]['id']);
        $this->assertSame('2026-11-22', $result['events'][1]['tanggal']);
        $this->assertNull($result['events'][1]['waktu_mulai']);
        $this->assertNull($result['events'][1]['waktu_selesai']);

        // Check multi-day event (hanya tanggal mulai)
        $this->assertSame('event-3-multiday', $result['events'][2]['id']);
        $this->assertSame('2026-11-25', $result['events'][2]['tanggal']);
        $this->assertSame('08:00', $result['events'][2]['waktu_mulai']);
        $this->assertNull($result['events'][2]['waktu_selesai']); // waktu selesai dikosongkan karena beda hari
    }

    public function test_callback_handles_user_cancellation(): void
    {
        $this->actingAs($this->kabag);

        $response = $this->get(route('kabag.google-calendar.callback', ['error' => 'access_denied']));

        $response->assertRedirect(route('kabag.jadwal-kegiatan.index'));
        $response->assertSessionHas('gcal_error', 'Akses ke Google Calendar dibatalkan.');
    }

    public function test_callback_handles_empty_events(): void
    {
        $this->actingAs($this->kabag);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->token = 'mock-access-token';

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events*' => Http::response(['items' => []], 200),
        ]);

        $response = $this->get(route('kabag.google-calendar.callback'));

        $response->assertRedirect(route('kabag.jadwal-kegiatan.index'));
        $response->assertSessionHas('gcal_error', 'Tidak ada event mendatang yang ditemukan di Google Calendar Anda (6 bulan ke depan).');

        // Verify token was NOT stored anywhere
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('users', 'google_token'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('users', 'access_token'));
        $this->assertFalse(Session::has('google_token'));
        $this->assertFalse(Session::has('access_token'));
    }

    public function test_callback_fetches_events_and_flashes_to_session(): void
    {
        $this->actingAs($this->kabag);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->token = 'mock-access-token';

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $fakeEvents = [
            'items' => [
                [
                    'id' => 'gcal-12345',
                    'status' => 'confirmed',
                    'summary' => 'Konferensi Pers Tahunan',
                    'location' => 'Aula Gedung B',
                    'description' => 'Pemaparan capaian tahunan',
                    'start' => ['dateTime' => '2026-11-20T10:00:00+07:00'],
                    'end' => ['dateTime' => '2026-11-20T12:00:00+07:00'],
                ],
            ],
        ];

        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events*' => Http::response($fakeEvents, 200),
        ]);

        $response = $this->get(route('kabag.google-calendar.callback'));

        $response->assertRedirect(route('kabag.jadwal-kegiatan.index'));
        $response->assertSessionHas('gcal_events');

        $events = session('gcal_events');
        $this->assertCount(1, $events);
        $this->assertSame('gcal-12345', $events[0]['id']);
        $this->assertSame('Konferensi Pers Tahunan', $events[0]['judul']);

        // Token must NOT be stored
        $this->assertFalse(Session::has('access_token'));
    }

    public function test_livewire_event_picker_and_auto_fill_form(): void
    {
        $this->actingAs($this->kabag);

        // Pre-create an existing kegiatan already imported from Google
        Kegiatan::create([
            'judul' => 'Kegiatan Lama',
            'tanggal' => '2026-11-18',
            'tempat' => 'Ruang Sidang',
            'sumber' => SumberKegiatan::GoogleCalendar->value,
            'google_event_id' => 'already-imported-event-id',
            'created_by' => $this->kabag->id,
        ]);

        $events = [
            [
                'id' => 'already-imported-event-id',
                'judul' => 'Kegiatan Lama di Google',
                'tanggal' => '2026-11-18',
                'waktu_mulai' => '08:00',
                'waktu_selesai' => '10:00',
                'tempat' => 'Ruang Sidang',
                'deskripsi' => 'Deskripsi lama',
            ],
            [
                'id' => 'fresh-new-event-id',
                'judul' => 'Sosialisasi Kebijakan Baru',
                'tanggal' => '2026-11-21',
                'waktu_mulai' => '13:00',
                'waktu_selesai' => '15:30',
                'tempat' => 'Auditorium Pemda',
                'deskripsi' => 'Sosialisasi kepada seluruh OPD',
            ],
        ];

        // Simulate page load with flashed events
        session()->flash('gcal_events', $events);

        $test = Livewire::test(JadwalKegiatanIndex::class)
            ->assertSet('show_gcal_picker', true)
            ->assertSee('Pilih Event Google Calendar')
            ->assertSee('Kegiatan Lama di Google')
            ->assertSee('Sudah ditambahkan')
            ->assertSee('Sosialisasi Kebijakan Baru');

        // Trying to select already added event
        $test->call('selectGcalEvent', 0)
            ->assertDispatched('toast', message: 'Event ini sudah pernah ditambahkan.', type: 'error')
            ->assertSet('show_gcal_picker', true);

        // Selecting a fresh event
        $test->call('selectGcalEvent', 1)
            ->assertSet('show_gcal_picker', false)
            ->assertSet('show_form_modal', true)
            ->assertSet('form_judul', 'Sosialisasi Kebijakan Baru')
            ->assertSet('form_tanggal', '2026-11-21')
            ->assertSet('form_waktu_mulai', '13:00')
            ->assertSet('form_waktu_selesai', '15:30')
            ->assertSet('form_tempat', 'Auditorium Pemda')
            ->assertSet('form_deskripsi', 'Sosialisasi kepada seluruh OPD')
            ->assertSet('form_google_event_id', 'fresh-new-event-id')
            ->assertSet('form_from_gcal', true);

        // Kabag chooses PIC and saves
        $test->set('form_pic_ids', [$this->staff->id])
            ->call('simpanKegiatan')
            ->assertSet('show_form_modal', false)
            ->assertDispatched('toast', message: 'Kegiatan baru berhasil ditambahkan.', type: 'success');

        // Verify in database: saved with sumber = google_calendar and google_event_id
        $this->assertDatabaseHas('kegiatan', [
            'judul' => 'Sosialisasi Kebijakan Baru',
            'tanggal' => '2026-11-21',
            'tempat' => 'Auditorium Pemda',
            'sumber' => SumberKegiatan::GoogleCalendar->value,
            'google_event_id' => 'fresh-new-event-id',
        ]);

        $created = Kegiatan::where('google_event_id', 'fresh-new-event-id')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->pics->contains($this->staff->id));

        // After saved, updating or deleting works completely offline/locally without Google connection
        $created->update(['judul' => 'Sosialisasi Kebijakan Baru (Revisi Internal)']);
        $this->assertSame('Sosialisasi Kebijakan Baru (Revisi Internal)', $created->fresh()->judul);
    }
}
