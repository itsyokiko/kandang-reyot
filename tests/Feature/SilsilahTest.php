<?php

namespace Tests\Feature;

use App\Models\Kambing;
use App\Models\Kelahiran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SilsilahTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_silsilah_page_empty(): void
    {
        $response = $this->get(route('silsilah.index'));

        $response->assertStatus(200);
        $response->assertSee('Belum ada data kambing');
    }

    public function test_can_view_silsilah_page_with_goat_as_center(): void
    {
        $kambing = Kambing::create([
            'nama' => 'Bimo',
            'jenis_kelamin' => 'jantan',
            'status' => 'hidup',
        ]);

        $response = $this->get(route('silsilah.index', $kambing));

        $response->assertStatus(200);
        $response->assertSee('Pohon Silsilah Visual:');
        $response->assertSee('Bimo');
        $response->assertSee('canvas-viewport');
    }

    public function test_silsilah_data_endpoint_returns_multi_generation_structure(): void
    {
        // 1. Kakek & Nenek (Generasi -2)
        $kakekAyah = Kambing::create(['nama' => 'Jalu Tua', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);
        $nenekAyah = Kambing::create(['nama' => 'Mawar Tua', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);

        // 2. Orang Tua (Generasi -1)
        $ayah = Kambing::create([
            'nama' => 'Jalu Muda',
            'jenis_kelamin' => 'jantan',
            'pejantan_id' => $kakekAyah->id,
            'induk_id' => $nenekAyah->id,
            'status' => 'hidup',
        ]);
        $ibu = Kambing::create([
            'nama' => 'Melati',
            'jenis_kelamin' => 'betina',
            'status' => 'hidup',
        ]);

        // 3. Kelahiran Pusat & Saudara (Generasi 0)
        $kelahiran = Kelahiran::create([
            'induk_id' => $ibu->id,
            'pejantan_id' => $ayah->id,
            'tanggal_kelahiran' => '2025-05-10',
        ]);

        $pusat = Kambing::create([
            'nama' => 'Bimo',
            'jenis_kelamin' => 'jantan',
            'pejantan_id' => $ayah->id,
            'induk_id' => $ibu->id,
            'kelahiran_id' => $kelahiran->id,
            'status' => 'hidup',
        ]);

        $saudara = Kambing::create([
            'nama' => 'Bima',
            'jenis_kelamin' => 'betina',
            'pejantan_id' => $ayah->id,
            'induk_id' => $ibu->id,
            'kelahiran_id' => $kelahiran->id,
            'status' => 'hidup',
        ]);

        // 4. Anak (Generasi +1)
        $anak = Kambing::create([
            'nama' => 'Cempluk',
            'jenis_kelamin' => 'betina',
            'pejantan_id' => $pusat->id,
            'induk_id' => $ibu->id,
            'status' => 'hidup',
        ]);

        // 5. Cucu (Generasi +2)
        $cucu = Kambing::create([
            'nama' => 'Gogon',
            'jenis_kelamin' => 'jantan',
            'induk_id' => $anak->id,
            'pejantan_id' => $ayah->id,
            'status' => 'hidup',
        ]);

        $response = $this->get(route('silsilah.data', $pusat));

        $response->assertStatus(200);
        $data = $response->json();

        // Verifikasi Pusat
        $this->assertEquals('Bimo', $data['pusat']['nama']);
        $this->assertEquals('pusat', $data['pusat']['role']);

        // Verifikasi Orang Tua
        $this->assertEquals('Jalu Muda', $data['orang_tua']['ayah']['nama']);
        $this->assertEquals('Melati', $data['orang_tua']['ibu']['nama']);

        // Verifikasi Kakek & Nenek
        $this->assertEquals('Jalu Tua', $data['kakek_nenek']['kakek_dari_ayah']['nama']);
        $this->assertEquals('Mawar Tua', $data['kakek_nenek']['nenek_dari_ayah']['nama']);
        $this->assertNull($data['kakek_nenek']['kakek_dari_ibu']);

        // Verifikasi Saudara Sekelahiran
        $this->assertCount(1, $data['saudara']);
        $this->assertEquals('Bima', $data['saudara'][0]['nama']);

        // Verifikasi Anak (Gen +1)
        $this->assertCount(1, $data['keturunan']);
        $this->assertEquals('Cempluk', $data['keturunan'][0]['anak']['nama']);

        // Verifikasi Cucu (Gen +2)
        $this->assertCount(1, $data['keturunan'][0]['cucu']);
        $this->assertEquals('Gogon', $data['keturunan'][0]['cucu'][0]['nama']);
    }

    public function test_goat_detail_page_has_link_to_silsilah(): void
    {
        $kambing = Kambing::create([
            'nama' => 'Rambo',
            'jenis_kelamin' => 'jantan',
            'status' => 'hidup',
        ]);

        $response = $this->get(route('kambings.show', $kambing));

        $response->assertStatus(200);
        $response->assertSee(route('silsilah.index', $kambing));
        $response->assertSee('Lihat Silsilah');
    }
}
