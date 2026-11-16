<?php

namespace App\Http\Controllers;

use App\Models\IotDevice;
use App\Models\IotLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IotController extends Controller
{
    /**
     * Menampilkan daftar perangkat IoT ESP32 terdaftar.
     */
    public function devices(): View
    {
        $devices = IotDevice::withCount('logs')->latest()->paginate(10);

        return view('sarpras.iot.devices', compact('devices'));
    }

    /**
     * Tambah perangkat IoT baru.
     */
    public function storeDevice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:50', 'unique:iot_devices,device_id'],
            'nama_device' => ['required', 'string', 'max:100'],
        ], [
            'device_id.unique' => 'Device ID ini telah terdaftar.',
        ]);

        $validated['api_key'] = 'siis-' . Str::random(24);
        $validated['status'] = 'active';

        IotDevice::create($validated);

        return back()->with('success', "Perangkat IoT '{$validated['nama_device']}' berhasil didaftarkan.");
    }

    /**
     * Ubah status aktif/nonaktif perangkat IoT.
     */
    public function toggleDeviceStatus(IotDevice $device): RedirectResponse
    {
        $newStatus = $device->status === 'active' ? 'inactive' : 'active';
        $device->update(['status' => $newStatus]);

        return back()->with('success', "Status perangkat {$device->device_id} diubah menjadi {$newStatus}.");
    }

    /**
     * Tampilkan riwayat audit log tap kartu RFID dari perangkat IoT.
     */
    public function logs(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = IotLog::with(['user', 'device'])->latest();

        if ($status) {
            $query->where('status_aksi', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('rfid_uid', 'like', "%{$search}%")
                  ->orWhere('device_id', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate(15)->withQueryString();

        return view('sarpras.iot.logs', compact('logs', 'status', 'search'));
    }

    /**
     * Halaman Simulator Tap RFID untuk pengujian tanpa perangkat fisik ESP32.
     */
    public function simulator(): View
    {
        $devices = IotDevice::where('status', 'active')->get();
        $users = User::whereNotNull('rfid_uid')->get();

        return view('sarpras.iot.simulator', compact('devices', 'users'));
    }
}

