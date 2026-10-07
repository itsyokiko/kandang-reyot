<?php

namespace Tests\Feature;

use App\Models\Kambing;
use App\Models\PengingatPerawatan;
use App\Models\RiwayatSakit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatSakitDanPengingatTest extends TestCase
{
    use RefreshDatabase;

    // 1. Membuat catatan sakit & 2. Catatan awal berstatus sedang sakit
    public function test_can_create_riwayat_sakit_with_status_sedang_sakit(): void
    {
        $kambing = Kambing::create(['nama' => 'Deni', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $response = $this->post(route('riwayat-sakit.store'), [
            'kambing_id' => $kambing->id,
            'tanggal_mulai' => '2026-10-12',
            'catatan' => 'Nafsu makan menurun',
        ]);

        $response->assertRedirect(route('riwayat-sakit.index'));

        $this->assertDatabaseHas('riwayat_sakits', [
            'kambing_id' => $kambing->id,
            'tanggal_mulai' => '2026-10-12',
            'status' => 'sedang_sakit',
            'catatan' => 'Nafsu makan menurun',
        ]);
    }

    // 3. Mengonfirmasi sembuh, 5. Durasi dihitung benar, 7. Kambing tetap tersimpan
    public function test_can_confirm_sembuh_and_calculates_duration(): void
    {
        $kambing = Kambing::create(['nama' => 'Deni', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $riwayat = RiwayatSakit::create([
            'kambing_id' => $kambing->id,
            'tanggal_mulai' => '2026-10-12',
            'status' => 'sedang_sakit',
            'catatan' => 'Nafsu makan menurun',
        ]);

        $response = $this->post(route('riwayat-sakit.konfirmasi', $riwayat), [
            'status' => 'sembuh',
            'tanggal_selesai' => '2026-10-20',
        ]);

        $response->assertSessionHasNoErrors();

        $riwayatFresh = $riwayat->fresh();
        $this->assertEquals('sembuh', $riwayatFresh->status);
        $this->assertEquals('2026-10-20', $riwayatFresh->tanggal_selesai->format('Y-m-d'));
        $this->assertEquals(8, $riwayatFresh->durasi_hari);

        // Kambing tetap tersimpan dan tetap hidup
        $this->assertDatabaseHas('kambings', [
            'id' => $kambing->id,
            'status' => 'hidup',
        ]);
    }

    // 4. Mengonfirmasi mati & 8. Status kambing berubah menjadi mati jika hasil sakit = mati
    public function test_can_confirm_mati_and_updates_kambing_status(): void
    {
        $kambing = Kambing::create(['nama' => 'Deni', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $riwayat = RiwayatSakit::create([
            'kambing_id' => $kambing->id,
            'tanggal_mulai' => '2026-10-12',
            'status' => 'sedang_sakit',
        ]);

        $response = $this->post(route('riwayat-sakit.konfirmasi', $riwayat), [
            'status' => 'mati',
            'tanggal_selesai' => '2026-10-15',
        ]);

        $response->assertSessionHasNoErrors();

        // Status riwayat sakit = mati
        $this->assertEquals('mati', $riwayat->fresh()->status);

        // Status kambing berubah menjadi mati dan tidak dihapus dari DB
        $this->assertDatabaseHas('kambings', [
            'id' => $kambing->id,
            'status' => 'mati',
        ]);
    }

    // 6. Validasi tanggal (tanggal_selesai tidak boleh sebelum tanggal_mulai)
    public function test_validation_rejects_end_date_before_start_date(): void
    {
        $kambing = Kambing::create(['nama' => 'Deni', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $riwayat = RiwayatSakit::create([
            'kambing_id' => $kambing->id,
            'tanggal_mulai' => '2026-10-12',
            'status' => 'sedang_sakit',
        ]);

        $response = $this->post(route('riwayat-sakit.konfirmasi', $riwayat), [
            'status' => 'sembuh',
            'tanggal_selesai' => '2026-10-10', // 2 hari sebelum tanggal mulai
        ]);

        $response->assertSessionHasErrors(['tanggal_selesai']);
    }

    // 9. Membuat pengingat
    public function test_can_create_pengingat_perawatan(): void
    {
        $kambing = Kambing::create(['nama' => 'Deni', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $response = $this->post(route('pengingat-perawatan.store'), [
            'kambing_id' => $kambing->id,
            'jenis_perawatan' => 'Vitamin B Complex',
            'tanggal' => '2026-10-20',
            'catatan' => 'Injeksi 2ml',
        ]);

        $response->assertRedirect(route('pengingat-perawatan.index'));

        $this->assertDatabaseHas('pengingat_perawatans', [
            'kambing_id' => $kambing->id,
            'jenis_perawatan' => 'Vitamin B Complex',
            'tanggal' => '2026-10-20',
            'status' => 'belum_diberikan',
        ]);
    }

    // 10. Pengingat muncul di dashboard & 13. Pengingat selesai tidak muncul sebagai pengingat aktif
    public function test_pengingat_appears_on_dashboard_when_active_and_disappears_when_completed(): void
    {
        $kambing = Kambing::create(['nama' => 'Deni', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);
        $todayStr = now()->toDateString();

        $pengingat = PengingatPerawatan::create([
            'kambing_id' => $kambing->id,
            'jenis_perawatan' => 'Obat Cacing',
            'tanggal' => $todayStr,
            'status' => 'belum_diberikan',
        ]);

        // Belum diberikan -> Muncul di Dashboard
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Obat Cacing');
        $response->assertSee('Deni');

        // 11. Menandai pengingat sudah diberikan & 12. Riwayat tetap tersimpan
        $this->post(route('pengingat-perawatan.selesai', $pengingat), [
            'tanggal_pemberian' => $todayStr,
        ]);

        $this->assertDatabaseHas('pengingat_perawatans', [
            'id' => $pengingat->id,
            'status' => 'sudah_diberikan',
            'tanggal_pemberian' => $todayStr,
        ]);

        // Sudah diberikan -> Tidak muncul sebagai pengingat aktif di Dashboard
        $response2 = $this->get(route('dashboard'));
        $response2->assertStatus(200);
        $response2->assertDontSee('Deni — Obat Cacing');
    }
}
