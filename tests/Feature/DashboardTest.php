<?php

namespace Tests\Feature;

use App\Models\Kambing;
use App\Models\Kelahiran;
use App\Models\Perkawinan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_dashboard_empty_db()
    {
        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Kandang');
        $response->assertSee('Total Kambing');
        $response->assertSee('Belum ada data kambing tersimpan');
        $response->assertSee('Belum ada catatan perkawinan');
        $response->assertSee('Belum ada catatan kelahiran');
    }

    public function test_dashboard_displays_correct_goat_statistics()
    {
        // 2 Jantan (1 hidup, 1 mati), 3 Betina (2 hidup, 1 dijual)
        Kambing::create(['nama' => 'Jantan 1', 'jenis_kelamin' => 'jantan', 'status' => 'hidup', 'ras' => 'Etawa']);
        Kambing::create(['nama' => 'Jantan 2', 'jenis_kelamin' => 'jantan', 'status' => 'mati', 'ras' => 'Etawa']);
        Kambing::create(['nama' => 'Betina 1', 'jenis_kelamin' => 'betina', 'status' => 'hidup', 'ras' => 'Jawa Randu']);
        Kambing::create(['nama' => 'Betina 2', 'jenis_kelamin' => 'betina', 'status' => 'hidup', 'ras' => 'Jawa Randu']);
        Kambing::create(['nama' => 'Betina 3', 'jenis_kelamin' => 'betina', 'status' => 'dijual']);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('5'); // Total Kambing
        $response->assertSee('Etawa');
        $response->assertSee('Jawa Randu');
        $response->assertSee('Tanpa ras');
    }

    public function test_dashboard_displays_breeding_and_recent_activities()
    {
        $jantan = Kambing::create(['nama' => 'Pejantan Utama', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);
        $betina = Kambing::create(['nama' => 'Induk Super', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);

        $perkawinan = Perkawinan::create([
            'betina_id' => $betina->id,
            'pejantan_id' => $jantan->id,
            'tanggal_kawin' => '2025-01-10',
            'status' => 'berhasil',
        ]);

        $kelahiran = Kelahiran::create([
            'perkawinan_id' => $perkawinan->id,
            'induk_id' => $betina->id,
            'pejantan_id' => $jantan->id,
            'tanggal_kelahiran' => '2025-06-10',
        ]);

        $anak = Kambing::create([
            'nama' => 'Cempe 1',
            'jenis_kelamin' => 'betina',
            'status' => 'hidup',
            'kelahiran_id' => $kelahiran->id,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Induk Super');
        $response->assertSee('Pejantan Utama');
        $response->assertSee('1 ekor');
    }
}
