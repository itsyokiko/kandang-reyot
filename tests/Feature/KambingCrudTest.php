<?php

namespace Tests\Feature;

use App\Models\Kambing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KambingCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_kambing_index_page(): void
    {
        $kambing = Kambing::create([
            'nama' => 'Si Hitam',
            'jenis_kelamin' => 'jantan',
            'ras' => 'Jawa Randu',
            'status' => 'hidup',
        ]);

        $response = $this->get('/kambings');

        $response->assertStatus(200);
        $response->assertSee('Si Hitam');
        $response->assertSee('Jawa Randu');
    }

    public function test_can_create_kambing_with_photo(): void
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->image('kambing.jpg');

        $response = $this->post('/kambings', [
            'nama' => 'Si Putih',
            'jenis_kelamin' => 'betina',
            'ras' => 'Etawa',
            'tanggal_lahir' => '2024-01-15',
            'status' => 'hidup',
            'catatan' => 'Kambing sehat dan aktif',
            'foto' => $photo,
        ]);

        $this->assertDatabaseHas('kambings', [
            'nama' => 'Si Putih',
            'jenis_kelamin' => 'betina',
            'ras' => 'Etawa',
            'status' => 'hidup',
        ]);

        $kambing = Kambing::where('nama', 'Si Putih')->first();
        $this->assertNotNull($kambing->foto);
        Storage::disk('public')->assertExists($kambing->foto);

        $response->assertRedirect(route('kambings.show', $kambing));
    }

    public function test_validation_fails_for_invalid_data(): void
    {
        $response = $this->post('/kambings', [
            'nama' => '',
            'jenis_kelamin' => 'invalid_gender',
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['nama', 'jenis_kelamin', 'status']);
    }

    public function test_can_view_kambing_detail(): void
    {
        $kambing = Kambing::create([
            'nama' => 'Rambo',
            'jenis_kelamin' => 'jantan',
            'ras' => 'Boer',
            'status' => 'hidup',
            'catatan' => 'Pejantan tangguh',
        ]);

        $response = $this->get(route('kambings.show', $kambing));

        $response->assertStatus(200);
        $response->assertSee('Rambo');
        $response->assertSee('Boer');
        $response->assertSee('Pejantan tangguh');
    }

    public function test_can_update_kambing(): void
    {
        $kambing = Kambing::create([
            'nama' => 'Si Belang',
            'jenis_kelamin' => 'betina',
            'ras' => 'Kacang',
            'status' => 'hidup',
        ]);

        $response = $this->put(route('kambings.update', $kambing), [
            'nama' => 'Si Belang Super',
            'jenis_kelamin' => 'betina',
            'ras' => 'Kacang Super',
            'status' => 'dijual',
        ]);

        $response->assertRedirect(route('kambings.show', $kambing));

        $this->assertDatabaseHas('kambings', [
            'id' => $kambing->id,
            'nama' => 'Si Belang Super',
            'status' => 'dijual',
        ]);
    }

    public function test_can_delete_kambing_and_associated_photo(): void
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->image('kambing.jpg');
        $path = $photo->store('kambing', 'public');

        $kambing = Kambing::create([
            'nama' => 'Si Coklat',
            'jenis_kelamin' => 'jantan',
            'status' => 'mati',
            'foto' => $path,
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->delete(route('kambings.destroy', $kambing));

        $response->assertRedirect(route('kambings.index'));
        $this->assertDatabaseMissing('kambings', [
            'id' => $kambing->id,
        ]);
        Storage::disk('public')->assertMissing($path);
    }
}
