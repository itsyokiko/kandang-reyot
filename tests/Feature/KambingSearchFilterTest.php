<?php

namespace Tests\Feature;

use App\Models\Kambing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KambingSearchFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_by_name_partial_and_case_insensitive(): void
    {
        Kambing::create(['nama' => 'Mawar Putih', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        Kambing::create(['nama' => 'Mawar Merah', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);
        Kambing::create(['nama' => 'Si Hitam', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $response = $this->get(route('kambings.index', ['search' => 'mawar']));

        $response->assertStatus(200);
        $response->assertSee('Mawar Putih');
        $response->assertSee('Mawar Merah');
        $response->assertDontSee('Si Hitam');
    }

    public function test_filter_by_jenis_kelamin(): void
    {
        Kambing::create(['nama' => 'Bandot', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);
        Kambing::create(['nama' => 'Melati', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);

        $response = $this->get(route('kambings.index', ['jenis_kelamin' => 'jantan']));

        $response->assertStatus(200);
        $response->assertSee('Bandot');
        $response->assertDontSee('Melati');
    }

    public function test_filter_by_status(): void
    {
        Kambing::create(['nama' => 'Si Putih', 'jenis_kelamin' => 'betina', 'status' => 'dijual']);
        Kambing::create(['nama' => 'Si Hitam', 'jenis_kelamin' => 'jantan', 'status' => 'hidup']);

        $response = $this->get(route('kambings.index', ['status' => 'dijual']));

        $response->assertStatus(200);
        $response->assertSee('Si Putih');
        $response->assertDontSee('Si Hitam');
    }

    public function test_filter_by_ras(): void
    {
        Kambing::create(['nama' => 'Etawa 1', 'jenis_kelamin' => 'betina', 'ras' => 'Etawa', 'status' => 'hidup']);
        Kambing::create(['nama' => 'Kacang 1', 'jenis_kelamin' => 'jantan', 'ras' => 'Kacang', 'status' => 'hidup']);

        $response = $this->get(route('kambings.index', ['ras' => 'Etawa']));

        $response->assertStatus(200);
        $response->assertSee('Etawa 1');
        $response->assertDontSee('Kacang 1');
    }

    public function test_combination_of_multiple_filters(): void
    {
        Kambing::create(['nama' => 'Mawar Etawa', 'jenis_kelamin' => 'betina', 'ras' => 'Etawa', 'status' => 'hidup']);
        Kambing::create(['nama' => 'Mawar Jawa', 'jenis_kelamin' => 'betina', 'ras' => 'Jawa Randu', 'status' => 'hidup']);
        Kambing::create(['nama' => 'Bimo Etawa', 'jenis_kelamin' => 'jantan', 'ras' => 'Etawa', 'status' => 'hidup']);

        $response = $this->get(route('kambings.index', [
            'search' => 'mawar',
            'jenis_kelamin' => 'betina',
            'ras' => 'Etawa',
            'status' => 'hidup',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Mawar Etawa');
        $response->assertDontSee('Mawar Jawa');
        $response->assertDontSee('Bimo Etawa');
    }

    public function test_filter_returns_zero_results(): void
    {
        Kambing::create(['nama' => 'Mawar', 'jenis_kelamin' => 'betina', 'status' => 'hidup']);

        $response = $this->get(route('kambings.index', ['search' => 'KambingTidakAda']));

        $response->assertStatus(200);
        $response->assertSee('Tidak ada kambing yang sesuai');
        $response->assertSee('Reset Filter');
    }

    public function test_pagination_retains_search_and_filter_params(): void
    {
        // Create 15 kambings with ras Etawa
        for ($i = 1; $i <= 15; $i++) {
            Kambing::create([
                'nama' => 'Kambing Etawa ' . sprintf('%02d', $i),
                'jenis_kelamin' => 'betina',
                'ras' => 'Etawa',
                'status' => 'hidup',
            ]);
        }

        $response = $this->get(route('kambings.index', [
            'ras' => 'Etawa',
            'page' => 2,
        ]));

        $response->assertStatus(200);
        // Page 2 link or active page check
        $response->assertSee('ras=Etawa');
    }
}
