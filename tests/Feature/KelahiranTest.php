<?php

namespace Tests\Feature;

use App\Models\Kambing;
use App\Models\Kelahiran;
use App\Models\Perkawinan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelahiranTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_kelahiran_index_page(): void
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

        $response = $this->get(route('kelahirans.index'));

        $response->assertStatus(200);
        $response->assertSee('Mawar');
        $response->assertSee('Jalu');
        $response->assertSee('Bimo');
    }

    public function test_can_create_kelahiran_with_multiple_children_and_links_to_parents(): void
    {
        $induk = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'ras' => 'Etawa', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'ras' => 'Boer', 'status' => 'hidup']);

        $response = $this->post(route('kelahirans.store'), [
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => '2027-02-20',
            'catatan' => 'Lahir sehat dan normal',
            'anak' => [
                ['nama' => 'Bimo', 'jenis_kelamin' => 'jantan', 'ras' => 'Peranakan Etawa', 'catatan' => 'Bobot 3.2 kg'],
                ['nama' => 'Bima', 'jenis_kelamin' => 'betina', 'ras' => 'Peranakan Etawa', 'catatan' => 'Bobot 2.9 kg'],
            ],
        ]);

        $this->assertDatabaseHas('kelahirans', [
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => '2027-02-20',
        ]);

        $kelahiran = Kelahiran::first();
        $response->assertRedirect(route('kelahirans.show', $kelahiran));

        // Check child goats created in kambings table
        $this->assertDatabaseHas('kambings', [
            'nama' => 'Bimo',
            'jenis_kelamin' => 'jantan',
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'kelahiran_id' => $kelahiran->id,
            'tanggal_lahir' => '2027-02-20',
        ]);

        $this->assertDatabaseHas('kambings', [
            'nama' => 'Bima',
            'jenis_kelamin' => 'betina',
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'kelahiran_id' => $kelahiran->id,
            'tanggal_lahir' => '2027-02-20',
        ]);

        $this->assertEquals(2, $kelahiran->anaks()->count());
    }

    public function test_can_create_kelahiran_from_perkawinan_and_updates_perkawinan_status(): void
    {
        $induk = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $perkawinan = Perkawinan::create([
            'betina_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kawin' => '2026-09-01',
            'status' => 'menunggu',
        ]);

        $response = $this->post(route('kelahirans.store'), [
            'perkawinan_id' => $perkawinan->id,
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => '2027-01-29',
            'anak' => [
                ['nama' => 'Cempluk', 'jenis_kelamin' => 'betina'],
            ],
        ]);

        $this->assertDatabaseHas('kelahirans', [
            'perkawinan_id' => $perkawinan->id,
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
        ]);

        $this->assertEquals('berhasil', $perkawinan->fresh()->status);
    }

    public function test_validation_rejects_invalid_parents_and_mismatched_perkawinan(): void
    {
        $jantan = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);
        $betina1 = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $betina2 = Kambing::create(['nama' => 'Melati', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);

        $perkawinan = Perkawinan::create([
            'betina_id' => $betina1->id,
            'pejantan_id' => $jantan->id,
            'tanggal_kawin' => '2026-09-01',
            'status' => 'berhasil',
        ]);

        // Jantan as induk should fail
        $response1 = $this->post(route('kelahirans.store'), [
            'induk_id' => $jantan->id,
            'pejantan_id' => $jantan->id,
            'tanggal_kelahiran' => '2027-02-20',
            'anak' => [['nama' => 'Anak 1', 'jenis_kelamin' => 'jantan']],
        ]);
        $response1->assertSessionHasErrors(['induk_id', 'pejantan_id']);

        // Mismatched perkawinan (using betina2 instead of betina1)
        $response2 = $this->post(route('kelahirans.store'), [
            'perkawinan_id' => $perkawinan->id,
            'induk_id' => $betina2->id,
            'pejantan_id' => $jantan->id,
            'tanggal_kelahiran' => '2027-02-20',
            'anak' => [['nama' => 'Anak 1', 'jenis_kelamin' => 'jantan']],
        ]);
        $response2->assertSessionHasErrors(['perkawinan_id']);
    }

    public function test_validation_rejects_duplicate_children_names_in_same_birth(): void
    {
        $induk = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $response = $this->post(route('kelahirans.store'), [
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => '2027-02-20',
            'anak' => [
                ['nama' => 'Kembar', 'jenis_kelamin' => 'jantan'],
                ['nama' => 'kembar', 'jenis_kelamin' => 'betina'],
            ],
        ]);

        $response->assertSessionHasErrors(['anak.1.nama']);
    }

    public function test_can_view_kelahiran_detail(): void
    {
        $induk = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $kelahiran = Kelahiran::create([
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => '2027-02-20',
            'catatan' => 'Kelahiran kembar dua',
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

        $response = $this->get(route('kelahirans.show', $kelahiran));

        $response->assertStatus(200);
        $response->assertSee('Mawar');
        $response->assertSee('Jalu');
        $response->assertSee('Bimo');
        $response->assertSee('Kelahiran kembar dua');
    }

    public function test_can_update_kelahiran_and_syncs_children_birth_date(): void
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

        $response = $this->put(route('kelahirans.update', $kelahiran), [
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => '2027-02-21',
            'catatan' => 'Tanggal dikoreksi',
        ]);

        $response->assertRedirect(route('kelahirans.show', $kelahiran));
        $this->assertDatabaseHas('kelahirans', [
            'id' => $kelahiran->id,
            'tanggal_kelahiran' => '2027-02-21',
            'catatan' => 'Tanggal dikoreksi',
        ]);

        $this->assertEquals('2027-02-21', $anak->fresh()->tanggal_lahir->format('Y-m-d'));
    }

    public function test_can_delete_kelahiran_safely_unlinks_children(): void
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

        $response = $this->delete(route('kelahirans.destroy', $kelahiran));

        $response->assertRedirect(route('kelahirans.index'));
        $this->assertDatabaseMissing('kelahirans', ['id' => $kelahiran->id]);

        // Child goat is still alive in the database, with kelahiran_id unlinked
        $this->assertDatabaseHas('kambings', ['id' => $anak->id]);
        $this->assertNull($anak->fresh()->kelahiran_id);
    }

    public function test_profil_induk_displays_riwayat_kelahiran_and_profil_anak_displays_orang_tua(): void
    {
        $induk = Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        $pejantan = Kambing::create(['nama' => 'Jalu', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $kelahiran = Kelahiran::create([
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_kelahiran' => '2027-02-20',
        ]);

        $anak1 = Kambing::create([
            'nama' => 'Bimo',
            'jenis_kelamin' => 'jantan',
            'status' => 'hidup',
            'kelahiran_id' => $kelahiran->id,
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_lahir' => '2027-02-20',
        ]);

        $anak2 = Kambing::create([
            'nama' => 'Bima',
            'jenis_kelamin' => 'betina',
            'status' => 'hidup',
            'kelahiran_id' => $kelahiran->id,
            'induk_id' => $induk->id,
            'pejantan_id' => $pejantan->id,
            'tanggal_lahir' => '2027-02-20',
        ]);

        // Check Mother's Profile
        $responseInduk = $this->get(route('kambings.show', $induk));
        $responseInduk->assertStatus(200);
        $responseInduk->assertSee('Riwayat Kelahiran');
        $responseInduk->assertSee('Bimo');
        $responseInduk->assertSee('Bima');

        // Check Child's Profile
        $responseAnak = $this->get(route('kambings.show', $anak1));
        $responseAnak->assertStatus(200);
        $responseAnak->assertSee('Orang Tua');
        $responseAnak->assertSee('Mawar');
        $responseAnak->assertSee('Jalu');
    }
}
