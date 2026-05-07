<?php

namespace App\Http\Controllers;
// test
use App\Models\Verification;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Submit verification (upload ktp + selfie)
     */
    public function submit(Request $request)
    {
        $request->validate([
            "id_number"=> "required|string",
            "selfie"=> "required|image|mimes:jpg,jpeg,png|max:2048",
            "id_card"=> "required|image|mimes:jpg,jpeg,png|max:2048",
        ]);

        $user = $request->user();

        $selfiePath = $request->file("selfie")->store("verifications/selfies", "public");
        $idCardPath = $request->file("id_card")->store("verifications/id_cards", "public");

        $verification = Verification::updateOrCreate(
            ["user_id" => $user->id],
            [
                "id_number" => $request->id_number,
                "selfie_url" => $selfiePath,
                "id_card_url" => $idCardPath,
                "status" => "pending"
            ]
            );

            return response()->json([
                "message" => "Verification submitted. Waiting for approval",
                "data" => $verification
            ]);
    }

    /**
     * Status Update
     */
    public function statusUpdate(Request $request, $id)
    {
        $request->validate([
            "status" => "required|in:verified, rejected"
        ]);

        $verification = Verification::where("id", $id)
            ->where("user_id", $request->user()->id)
            ->firstOrFail();

        $verification->status = $request->status;
        $verification->save();

        return response()->json([
            "message" => "Status updated"
        ]);
    }
    
    /**
     * Verification status
     */
    public function status(Request $request)
    {
        $verification = Verification::where("user_id", $request->user()->id)->first();

        return response()->json([
            "data" => $verification
        ]);
    }
}
