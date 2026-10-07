<?php

namespace Tests\Feature;

use App\Models\Kambing;
use App\Models\Perkawinan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerkawinanTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_perkawinan_index_page(): void
    {
        $betina = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $perkawinan = Perkawinan::create([
            'betina_id' => $betina->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kawin' => '2026-09-01',
            'status' => 'menunggu',
        ]);

        $response = $this->get(route('perkawinans.index'));

        $response->assertStatus(200);
        $response->assertSee('Mawar');
        $response->assertSee('Jalu');
        $response->assertSee('01 Sep 2026');
    }

    public function test_can_create_perkawinan_and_calculates_estimated_birth(): void
    {
        $betina = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $tanggalKawin = '2026-09-01';
        $expectedPerkiraanLahir = Carbon::parse($tanggalKawin)->addDays(150)->format('Y-m-d');

        $response = $this->post(route('perkawinans.store'), [
            'betina_id' => $betina->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kawin' => $tanggalKawin,
            'status' => 'berhasil',
            'catatan' => 'Kawin alami di kandang A',
        ]);

        $this->assertDatabaseHas('perkawinans', [
            'betina_id' => $betina->id,
            'pejantan_id' => $pejantan->id,
            'status' => 'berhasil',
            'catatan' => 'Kawin alami di kandang A',
        ]);

        $perkawinan = Perkawinan::where('betina_id', $betina->id)->first();
        $this->assertEquals($expectedPerkiraanLahir, $perkawinan->perkiraan_lahir->format('Y-m-d'));

        $response->assertRedirect(route('perkawinans.show', $perkawinan));
    }

    public function test_validation_rejects_invalid_genders(): void
    {
        $jantan1 = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);
        $jantan2 = Kambing::create(['nama' => 'Bima', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);
        $betina1 = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $betina2 = Kambing::create(['nama' => 'Melati', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);

        // Jantan as betina should fail
        $response1 = $this->post(route('perkawinans.store'), [
            'betina_id' => $jantan1->id,
            'pejantan_id' => $jantan2->id,
            'tanggal_kawin' => '2026-09-01',
            'status' => 'menunggu',
        ]);
        $response1->assertSessionHasErrors(['betina_id']);

        // Betina as pejantan should fail
        $response2 = $this->post(route('perkawinans.store'), [
            'betina_id' => $betina1->id,
            'pejantan_id' => $betina2->id,
            'tanggal_kawin' => '2026-09-01',
            'status' => 'menunggu',
        ]);
        $response2->assertSessionHasErrors(['pejantan_id']);
    }

    public function test_validation_rejects_same_goat_and_missing_fields(): void
    {
        $betina = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);

        $response = $this->post(route('perkawinans.store'), [
            'betina_id' => $betina->id,
            'pejantan_id' => $betina->id,
            'tanggal_kawin' => '',
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['pejantan_id', 'tanggal_kawin', 'status']);
    }

    public function test_can_view_perkawinan_detail(): void
    {
        $betina = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $perkawinan = Perkawinan::create([
            'betina_id' => $betina->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kawin' => '2026-09-01',
            'status' => 'berhasil',
            'catatan' => 'Perkawinan berhasil diamati',
        ]);

        $response = $this->get(route('perkawinans.show', $perkawinan));

        $response->assertStatus(200);
        $response->assertSee('Mawar');
        $response->assertSee('Jalu');
        $response->assertSee('29 January 2027');
        $response->assertSee('Perkawinan berhasil diamati');
    }

    public function test_can_update_perkawinan(): void
    {
        $betina = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $perkawinan = Perkawinan::create([
            'betina_id' => $betina->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kawin' => '2026-09-01',
            'status' => 'menunggu',
        ]);

        $response = $this->put(route('perkawinans.update', $perkawinan), [
            'betina_id' => $betina->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kawin' => '2026-09-01',
            'status' => 'berhasil',
            'catatan' => 'Tanda kebuntingan sudah terlihat',
        ]);

        $response->assertRedirect(route('perkawinans.show', $perkawinan));

        $this->assertDatabaseHas('perkawinans', [
            'id' => $perkawinan->id,
            'status' => 'berhasil',
            'catatan' => 'Tanda kebuntingan sudah terlihat',
        ]);
    }

    public function test_can_delete_perkawinan(): void
    {
        $betina = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $perkawinan = Perkawinan::create([
            'betina_id' => $betina->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kawin' => '2026-09-01',
            'status' => 'menunggu',
        ]);

        $response = $this->delete(route('perkawinans.destroy', $perkawinan));

        $response->assertRedirect(route('perkawinans.index'));
        $this->assertDatabaseMissing('perkawinans', [
            'id' => $perkawinan->id,
        ]);
    }

    public function test_kambing_show_page_displays_perkawinan_history_for_both_partners(): void
    {
        $betina = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        Perkawinan::create([
            'betina_id' => $betina->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kawin' => '2026-09-01',
            'status' => 'berhasil',
        ]);

        // Check betina detail page
        $responseBetina = $this->get(route('kambings.show', $betina));
        $responseBetina->assertStatus(200);
        $responseBetina->assertSee('Riwayat Perkawinan');
        $responseBetina->assertSee('dengan');
        $responseBetina->assertSee('Jalu');

        // Check pejantan detail page
        $responsePejantan = $this->get(route('kambings.show', $pejantan));
        $responsePejantan->assertStatus(200);
        $responsePejantan->assertSee('Riwayat Perkawinan');
        $responsePejantan->assertSee('dengan');
        $responsePejantan->assertSee('Mawar');
    }
}
