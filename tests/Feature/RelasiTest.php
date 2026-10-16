<?php

namespace Tests\Feature;

use App\Models\Kambing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_relasi_page_empty(): void
    {
        $response = $this->get(route('relasi.index'));

        $response->assertStatus(200);
        $response->assertSee('Relasi Silsilah');
        $response->assertSee('Belum ada data kambing tersimpan');
    }

    public function test_can_view_relasi_page_with_data(): void
    {
        $jantan = Kambing::create(['nama' => 'Bimo', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);
        $betina = Kambing::create(['nama' => 'Srikandi', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $anak = Kambing::create(['nama' => 'Cempe 1', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);

        $response = $this->get(route('relasi.index'));

        $response->assertStatus(200);
        $response->assertSee('Bimo');
        $response->assertSee('Srikandi');
        $response->assertSee('Cempe 1');
        $response->assertSee('Hubungkan Relasi Silsilah');
    }

    public function test_can_connect_parents_to_multiple_children(): void
    {
        $ayah = Kambing::create(['nama' => 'Pejantan Tangguh', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);
        $ibu = Kambing::create(['nama' => 'Induk Super', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);

        $anak1 = Kambing::create(['nama' => 'Anak Pertama', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);
        $anak2 = Kambing::create(['nama' => 'Anak Kedua', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);

        $response = $this->post(route('relasi.store'), [
            'pejantan_id' => $ayah->id,
            'induk_id' => $ibu->id,
            'anak_ids' => [$anak1->id, $anak2->id],
        ]);

        $response->assertRedirect(route('relasi.index'));
        $response->assertSessionHas('success');

        $anak1->refresh();
        $anak2->refresh();

        $this->assertEquals($ayah->id, $anak1->pejantan_id);
        $this->assertEquals($ibu->id, $anak1->induk_id);

        $this->assertEquals($ayah->id, $anak2->pejantan_id);
        $this->assertEquals($ibu->id, $anak2->induk_id);
    }

    public function test_can_connect_only_father_or_only_mother(): void
    {
        $ayah = Kambing::create(['nama' => 'Ayah Saja', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);
        $anak = Kambing::create(['nama' => 'Anak Tanpa Ibu', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);

        $response = $this->post(route('relasi.store'), [
            'pejantan_id' => $ayah->id,
            'induk_id' => null,
            'anak_ids' => [$anak->id],
        ]);

        $response->assertRedirect(route('relasi.index'));
        $anak->refresh();

        $this->assertEquals($ayah->id, $anak->pejantan_id);
        $this->assertNull($anak->induk_id);
    }

    public function test_validation_fails_if_no_parent_selected(): void
    {
        $anak = Kambing::create(['nama' => 'Anak Bingung', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $response = $this->post(route('relasi.store'), [
            'pejantan_id' => null,
            'induk_id' => null,
            'anak_ids' => [$anak->id],
        ]);

        $response->assertSessionHasErrors(['pejantan_id']);
    }

    public function test_validation_fails_if_no_children_selected(): void
    {
        $ayah = Kambing::create(['nama' => 'Ayah', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $response = $this->post(route('relasi.store'), [
            'pejantan_id' => $ayah->id,
            'induk_id' => null,
            'anak_ids' => [],
        ]);

        $response->assertSessionHasErrors(['anak_ids']);
    }

    public function test_cannot_set_goat_as_own_parent(): void
    {
        $kambing = Kambing::create(['nama' => 'Paradoks', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $response = $this->post(route('relasi.store'), [
            'pejantan_id' => $kambing->id,
            'induk_id' => null,
            'anak_ids' => [$kambing->id],
        ]);

        $kambing->refresh();
        $this->assertNull($kambing->pejantan_id);
    }

    public function test_can_disconnect_parent_relation(): void
    {
        $ayah = Kambing::create(['nama' => 'Ayah Lama', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);
        $ibu = Kambing::create(['nama' => 'Ibu Lama', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);

        $anak = Kambing::create([
            'nama' => 'Anak',
            'jenis_kelamin' => 'betina',
            'status' => 'hidup',
            'pejantan_id' => $ayah->id,
            'induk_id' => $ibu->id,
        ]);

        // Putus hanya pejantan
        $response = $this->delete(route('relasi.destroy', ['kambing' => $anak, 'tipe' => 'pejantan']));
        $response->assertSessionHas('success');

        $anak->refresh();
        $this->assertNull($anak->pejantan_id);
        $this->assertEquals($ibu->id, $anak->induk_id);

        // Putus semua
        $this->delete(route('relasi.destroy', $anak));
        $anak->refresh();
        $this->assertNull($anak->induk_id);
    }
}
