<?php

use App\Models\User;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('employee checkout after 4pm registers overtime (lembur) correctly', function () {
    // 1. Create a user
    $admin = User::factory()->create([
        'role' => 'admin',
        'timezone' => 'Asia/Jakarta',
        'face_photo' => 'faces/admin.png'
    ]);

    // 2. Setup mock presence entry (masuk) for today
    $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
    $presensi = Presensi::create([
        'user_id' => $admin->id,
        'tanggal' => $today,
        'jam_masuk' => '08:00:00',
        'foto_masuk' => 'presensi/test_masuk.png',
        'status' => 'Hadir'
    ]);

    // Mock time to 17:30:00 (5:30 PM), which is 1.5 hours (90 minutes) overtime
    $mockTime = Carbon::createFromFormat('Y-m-d H:i:s', $today . ' 17:30:00', 'Asia/Jakarta');
    Carbon::setTestNow($mockTime);

    // Setup base64 photo payload
    $photoBase64 = 'data:image/png;base64,' . base64_encode('fake-image-content');

    // 3. Act as admin and post to presensi.store (this should trigger checkout)
    $response = $this->actingAs($admin)
        ->post('/presensi', [
            'user_id' => $admin->id,
            'photo' => $photoBase64
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertStatus(302); // Redirect back

    // 4. Assert that presensi was updated with correct checkout time and overtime (lembur = 90 minutes)
    $presensi->refresh();
    $this->assertEquals('17:30:00', $presensi->jam_keluar);
    $this->assertEquals(90, $presensi->lembur);

    // Clean up
    Carbon::setTestNow(); // Reset mock time
});

test('employee checkout before 4pm does not register overtime', function () {
    // 1. Create a user
    $admin = User::factory()->create([
        'role' => 'admin',
        'timezone' => 'Asia/Jakarta',
        'face_photo' => 'faces/admin.png'
    ]);

    // 2. Setup mock presence entry (masuk) for today
    $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
    $presensi = Presensi::create([
        'user_id' => $admin->id,
        'tanggal' => $today,
        'jam_masuk' => '08:00:00',
        'foto_masuk' => 'presensi/test_masuk.png',
        'status' => 'Hadir'
    ]);

    // Mock time to 15:45:00 (3:45 PM), which is before 4 PM
    $mockTime = Carbon::createFromFormat('Y-m-d H:i:s', $today . ' 15:45:00', 'Asia/Jakarta');
    Carbon::setTestNow($mockTime);

    // Setup base64 photo payload
    $photoBase64 = 'data:image/png;base64,' . base64_encode('fake-image-content');

    // 3. Act as admin and post to presensi.store
    $response = $this->actingAs($admin)
        ->post('/presensi', [
            'user_id' => $admin->id,
            'photo' => $photoBase64
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertStatus(302);

    // 4. Assert that presensi was updated with checkout time and lembur = 0
    $presensi->refresh();
    $this->assertEquals('15:45:00', $presensi->jam_keluar);
    $this->assertEquals(0, $presensi->lembur);

    // Clean up
    Carbon::setTestNow(); // Reset mock time
});
