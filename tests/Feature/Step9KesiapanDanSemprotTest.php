<?php

namespace Tests\Feature;

use App\Models\Kambing;
use App\Models\Kelahiran;
use App\Models\PengingatPerawatan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Step9KesiapanDanSemprotTest extends TestCase
{
    use RefreshDatabase;

    // 1. Betina masa pemulihan
    public function test_betina_dalam_masa_pemulihan(): void
    {
        $betina = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Bimo', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        // Kelahiran 10 hari yang lalu
        Kelahiran::create([
            'induk_id' => $betina->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => now()->subDays(10)->toDateString(),
        ]);

        $info = $betina->kesiapan_kawin_info;

        $this->assertNotNull($info);
        $this->assertEquals('pemulihan', $info['status']);
        $this->assertEquals(50, $info['sisa_hari']);
    }

    // 2. Betina siap dikawinkan
    public function test_betina_siap_dikawinkan(): void
    {
        $betina = Kambing::create(['nama' => 'Sari', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Bimo', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        // Kelahiran 70 hari yang lalu (melewati default 60 hari recovery)
        Kelahiran::create([
            'induk_id' => $betina->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => now()->subDays(70)->toDateString(),
        ]);

        $info = $betina->kesiapan_kawin_info;

        $this->assertNotNull($info);
        $this->assertEquals('siap', $info['status']);
        $this->assertEquals(70, $info['lama_sejak_melahirkan']);
    }

    // 3. Betina tanpa kelahiran
    public function test_betina_tanpa_riwayat_kelahiran(): void
    {
        $betina = Kambing::create(['nama' => 'Dara', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);

        $info = $betina->kesiapan_kawin_info;

        $this->assertNotNull($info);
        $this->assertEquals('belum_ada_riwayat', $info['status']);
    }

    // 4. Beberapa kelahiran, gunakan yang terakhir
    public function test_beberapa_kelahiran_menggunakan_yang_terakhir(): void
    {
        $betina = Kambing::create(['nama' => 'Induk 2x', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Bimo', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        // Kelahiran pertama 200 hari lalu
        Kelahiran::create([
            'induk_id' => $betina->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => now()->subDays(200)->toDateString(),
        ]);

        // Kelahiran kedua 20 hari lalu
        Kelahiran::create([
            'induk_id' => $betina->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => now()->subDays(20)->toDateString(),
        ]);

        $info = $betina->kesiapan_kawin_info;

        $this->assertNotNull($info);
        $this->assertEquals('pemulihan', $info['status']); // Menggunakan kelahiran 20 hari lalu (pemulihan)
        $this->assertEquals(40, $info['sisa_hari']);
    }

    // 5. Perubahan tanggal kelahiran mengubah status
    public function test_perubahan_tanggal_kelahiran_mengubah_status(): void
    {
        $betina = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Bimo', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $kelahiran = Kelahiran::create([
            'induk_id' => $betina->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => now()->subDays(10)->toDateString(),
        ]);

        $this->assertEquals('pemulihan', $betina->fresh()->kesiapan_kawin_info['status']);

        // Update tanggal kelahiran menjadi 80 hari lalu
        $kelahiran->update(['tanggal_kelahiran' => now()->subDays(80)->toDateString()]);

        $this->assertEquals('siap', $betina->fresh()->kesiapan_kawin_info['status']);
    }

    // 6. Dashboard kesiapan & 7. Detail kambing
    public function test_dashboard_dan_detail_menampilkan_kesiapan(): void
    {
        $betina = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Bimo', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        Kelahiran::create([
            'induk_id' => $betina->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => now()->subDays(70)->toDateString(),
        ]);

        // Dashboard
        $responseDash = $this->get(route('dashboard'));
        $responseDash->assertStatus(200);
        $responseDash->assertSee('Betina Siap Dikawinkan');

        // Halaman kesiapan-kawin
        $responseKesiapan = $this->get(route('kesiapan-kawin.index'));
        $responseKesiapan->assertStatus(200);
        $responseKesiapan->assertSee('Mawar');
        $responseKesiapan->assertSee('Siap Dikawinkan');

        // Detail Kambing
        $responseDetail = $this->get(route('kambings.show', $betina));
        $responseDetail->assertStatus(200);
        $responseDetail->assertSee('Siap Dikawinkan');
    }

    // 8. Jantan tidak masuk & 9. Kambing mati/dijual tidak menjadi kandidat aktif
    public function test_jantan_dan_kambing_non_aktif_tidak_masuk_kandidat(): void
    {
        $jantan = Kambing::create(['nama' => 'Pejantan', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);
        $betinaMati = Kambing::create(['nama' => 'Almarhumah', 'jenis_kelamin' => 'betina', 'status' => 'mati']);
        $betinaDijual = Kambing::create(['nama' => 'Terjual', 'jenis_kelamin' => 'betina', 'status' => 'dijual']);

        $this->assertNull($jantan->kesiapan_kawin_info);
        $this->assertNull($betinaMati->kesiapan_kawin_info);
        $this->assertNull($betinaDijual->kesiapan_kawin_info);
    }

    // 10. Membuat pengingat Semprot Kandang, 11. Tanggal pertama digunakan
    public function test_can_create_pengingat_semprot_kandang(): void
    {
        $kambing = Kambing::create(['nama' => 'Deni', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $response = $this->post(route('pengingat-perawatan.store'), [
            'kambing_id' => $kambing->id,
            'jenis_perawatan' => 'Semprot Kandang',
            'tanggal' => '2026-10-05',
            'catatan' => 'Disinfeksi rutin',
        ]);

        $response->assertRedirect(route('pengingat-perawatan.index'));

        $this->assertDatabaseHas('pengingat_perawatans', [
            'kambing_id' => $kambing->id,
            'jenis_perawatan' => 'Semprot Kandang',
            'tanggal' => '2026-10-05',
            'status' => 'belum_diberikan',
        ]);
    }

    // 12. Interval menghasilkan jadwal berikutnya, 13. Histori tetap tersimpan, 14. Dashboard
    public function test_konfirmasi_semprot_kandang_membuat_jadwal_berikutnya(): void
    {
        $kambing = Kambing::create(['nama' => 'Deni', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $pengingat = PengingatPerawatan::create([
            'kambing_id' => $kambing->id,
            'jenis_perawatan' => 'Semprot Kandang',
            'tanggal' => '2026-10-05',
            'status' => 'belum_diberikan',
        ]);

        // Konfirmasi selesai pada 2026-10-05
        $response = $this->post(route('pengingat-perawatan.selesai', $pengingat), [
            'tanggal_pemberian' => '2026-10-05',
        ]);

        $response->assertSessionHasNoErrors();

        // 1. Record awal tetap tersimpan sebagai sudah_diberikan
        $this->assertDatabaseHas('pengingat_perawatans', [
            'id' => $pengingat->id,
            'status' => 'sudah_diberikan',
            'tanggal_pemberian' => '2026-10-05',
        ]);

        // 2. Record baru dibuat untuk jadwal berikutnya: 2026-10-05 + 14 hari = 2026-10-19
        $this->assertDatabaseHas('pengingat_perawatans', [
            'kambing_id' => $kambing->id,
            'jenis_perawatan' => 'Semprot Kandang',
            'tanggal' => '2026-10-19',
            'status' => 'belum_diberikan',
        ]);
    }

    public function test_can_view_and_set_jadwal_semprot_kandang_tanpa_input_kambing(): void
    {
        $response = $this->get(route('semprot-kandang.index'));
        $response->assertStatus(200);
        $response->assertSee('Semprot Kandang');

        $storeResponse = $this->post(route('semprot-kandang.store'), [
            'tanggal' => '2026-10-10',
            'interval_hari' => 14,
            'catatan' => 'Disinfeksi sekat dan lantai kandang',
        ]);

        $storeResponse->assertRedirect(route('semprot-kandang.index'));
        $this->assertDatabaseHas('pengingat_perawatans', [
            'jenis_perawatan' => 'Semprot Kandang',
            'tanggal' => '2026-10-10',
            'status' => 'belum_diberikan',
        ]);
    }
}
