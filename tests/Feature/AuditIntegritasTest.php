<?php

namespace Tests\Feature;

use App\Models\Kambing;
use App\Models\Kelahiran;
use App\Models\Perkawinan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditIntegritasTest extends TestCase
{
    use RefreshDatabase;

    public function test_penghapusan_induk_dan_pejantan_aman_dan_tidak_menghapus_anak(): void
    {
        $induk = Kambing::create(['nama' => 'Mawar Induk', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu Pejantan', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $kelahiran = Kelahiran::create([
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => '2027-02-20',
        ]);

        $anak = Kambing::create([
            'nama' => 'Bimo Anak',
            'jenis_kelamin' => 'jantan',
            'status' => 'hidup',
            'kelahiran_id' => $kelahiran->id,
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_lahir' => '2027-02-20',
        ]);

        // Hapus Induk
        $this->delete(route('kambings.destroy', $induk));

        // Anak harus tetap ada di database, induk_id menjadi null
        $this->assertDatabaseHas('kambings', ['id' => $anak->id]);
        $this->assertNull($anak->fresh()->induk_id);

        // Hapus Pejantan
        $this->delete(route('kambings.destroy', $pejantan));

        // Anak harus tetap ada di database, pejantan_id menjadi null
        $this->assertDatabaseHas('kambings', ['id' => $anak->id]);
        $this->assertNull($anak->fresh()->pejantan_id);
    }

    public function test_penghapusan_kelahiran_tidak_menghapus_anak(): void
    {
        $induk = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $kelahiran = Kelahiran::create([
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => '2027-02-20',
        ]);

        $anak = Kambing::create([
            'nama' => 'Bimo',
            'jenis_kelamin' => 'jantan',
            'status' => 'hidup',
            'kelahiran_id' => $kelahiran->id,
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_lahir' => '2027-02-20',
        ]);

        $this->delete(route('kelahirans.destroy', $kelahiran));

        $this->assertDatabaseMissing('kelahirans', ['id' => $kelahiran->id]);
        $this->assertDatabaseHas('kambings', ['id' => $anak->id]);
        $this->assertNull($anak->fresh()->kelahiran_id);
    }

    public function test_pengeditan_kelahiran_memperbarui_orang_tua_dan_tanggal_lahir_anak(): void
    {
        $induk1 = Kambing::create(['nama' => 'Mawar 1', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan1 = Kambing::create(['nama' => 'Jalu 1', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);
        $pejantan2 = Kambing::create(['nama' => 'Rambo 2', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $kelahiran = Kelahiran::create([
            'induk_id' => $induk1->id,
            'pejantan_id' => $pejantan1->id,
            'tanggal_kelahiran' => '2027-02-20',
        ]);

        $anak = Kambing::create([
            'nama' => 'Bimo',
            'jenis_kelamin' => 'jantan',
            'status' => 'hidup',
            'kelahiran_id' => $kelahiran->id,
            'induk_id' => $induk1->id,
            'pejantan_id' => $pejantan1->id,
            'tanggal_lahir' => '2027-02-20',
        ]);

        $this->put(route('kelahirans.update', $kelahiran), [
            'induk_id' => $induk1->id,
            'pejantan_id' => $pejantan2->id,
            'tanggal_kelahiran' => '2027-02-25',
            'catatan' => 'Pejantan dikoreksi ke Rambo 2',
        ]);

        $anakFresh = $anak->fresh();
        $this->assertEquals($pejantan2->id, $anakFresh->pejantan_id);
        $this->assertEquals('2027-02-25', $anakFresh->tanggal_lahir->format('Y-m-d'));
    }

    public function test_pengeditan_perkawinan_yang_memiliki_kelahiran_dibatasi(): void
    {
        $induk = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan1 = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);
        $pejantan2 = Kambing::create(['nama' => 'Rambo', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $perkawinan = Perkawinan::create([
            'betina_id' => $induk->id,
            'pejantan_id' => $pejantan1->id,
            'tanggal_kawin' => '2026-09-01',
            'status' => 'berhasil',
        ]);

        Kelahiran::create([
            'perkawinan_id' => $perkawinan->id,
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan1->id,
            'tanggal_kelahiran' => '2027-01-29',
        ]);

        // Mengubah pejantan pada perkawinan yang sudah memiliki kelahiran harus ditolak
        $response = $this->put(route('perkawinans.update', $perkawinan), [
            'betina_id' => $induk->id,
            'pejantan_id' => $pejantan2->id,
            'tanggal_kawin' => '2026-09-01',
            'status' => 'berhasil',
        ]);

        $response->assertSessionHasErrors(['betina_id']);
    }

    public function test_validasi_biologis_dan_pencegahan_perubahan_jenis_kelamin_induk(): void
    {
        $induk = Kambing::create(['nama' => 'Mawar Induk', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu Pejantan', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        Perkawinan::create([
            'betina_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kawin' => '2026-09-01',
            'status' => 'menunggu',
        ]);

        // Mencoba merubah jenis kelamin induk menjadi jantan harus gagal
        $response = $this->put(route('kambings.update', $induk), [
            'nama' => 'Mawar Induk',
            'jenis_kelamin' => 'jantan',
            'status' => 'hidup',
        ]);

        $response->assertSessionHasErrors(['jenis_kelamin']);
    }

    public function test_silsilah_tetap_konsisten_setelah_penghapusan_orang_tua(): void
    {
        $induk = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $anak = Kambing::create([
            'nama' => 'Bimo',
            'jenis_kelamin' => 'jantan',
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'status' => 'hidup',
        ]);

        // Hapus pejantan
        $pejantan->delete();

        $response = $this->get(route('silsilah.data', $anak));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals('Bimo', $data['pusat']['nama']);
        $this->assertNull($data['orang_tua']['ayah']);
        $this->assertEquals('Mawar', $data['orang_tua']['ibu']['nama']);
    }

    public function test_pemeriksaan_tidak_ada_data_orphan(): void
    {
        $induk = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $kelahiran = Kelahiran::create([
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => '2027-02-20',
        ]);

        Kambing::create([
            'nama' => 'Bimo',
            'jenis_kelamin' => 'jantan',
            'status' => 'hidup',
            'kelahiran_id' => $kelahiran->id,
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_lahir' => '2027-02-20',
        ]);

        // Hapus kelahiran
        $kelahiran->delete();

        // Pastikan tidak ada kambing dengan kelahiran_id yang menunjuk ke id yang tidak ada
        $orphanedKambingKelahiran = DB::table('kambings')
            ->whereNotNull('kelahiran_id')
            ->whereNotIn('kelahiran_id', DB::table('kelahirans')->pluck('id'))
            ->count();

        $this->assertEquals(0, $orphanedKambingKelahiran);

        // Pastikan tidak ada kambing dengan induk_id yang tidak ada
        $orphanedKambingInduk = DB::table('kambings')
            ->whereNotNull('induk_id')
            ->whereNotIn('induk_id', DB::table('kambings')->pluck('id'))
            ->count();

        $this->assertEquals(0, $orphanedKambingInduk);
    }
}
