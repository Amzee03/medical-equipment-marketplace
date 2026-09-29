<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Address\AddressRequest;
use App\Http\Resources\AddressResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    /**
     * Display a listing of the user's addresses.
     */
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'data' => AddressResource::collection($addresses),
        ]);
    }

    /**
     * Store a newly created address in storage.
     */
    public function store(AddressRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        try {
            DB::beginTransaction();

            // If it's the first address, force it to be default
            if ($user->addresses()->count() === 0) {
                $data['is_default'] = true;
            }

            if (!empty($data['is_default']) && $data['is_default'] === true) {
                // Set all other addresses to non-default
                $user->addresses()->update(['is_default' => false]);
            }

            $address = $user->addresses()->create($data);

            DB::commit();

            return response()->json([
                'message' => 'Alamat berhasil ditambahkan.',
                'data' => new AddressResource($address)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan alamat.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified address.
     */
    public function show(Request $request, string $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);
        
        return response()->json([
            'data' => new AddressResource($address),
        ]);
    }

    /**
     * Update the specified address in storage.
     */
    public function update(AddressRequest $request, string $id)
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($id);
        $data = $request->validated();

        try {
            DB::beginTransaction();

            if (!empty($data['is_default']) && $data['is_default'] === true) {
                $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update($data);

            DB::commit();

            return response()->json([
                'message' => 'Alamat berhasil diperbarui.',
                'data' => new AddressResource($address)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memperbarui alamat.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified address from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($id);

        try {
            DB::beginTransaction();

            $wasDefault = $address->is_default;
            $address->delete();

            // If the deleted address was default, set the latest remaining address as default
            if ($wasDefault) {
                $latestAddress = $user->addresses()->latest()->first();
                if ($latestAddress) {
                    $latestAddress->update(['is_default' => true]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Alamat berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus alamat.', 'error' => $e->getMessage()], 500);
        }
    }
}
