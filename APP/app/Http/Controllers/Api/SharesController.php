<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ShareResource;
use App\Models\Share;
use App\Models\User;
use Illuminate\Http\Request;

class SharesController extends Controller
{
    /*
     * Function to fetch Shares of a specific member
     *
     * */
    public function index(Request $request){
        // Get the authenticated user
        $user = auth()->user();

        // Get the user's shares
        $shares = Share::where('member_id', $user->id)->first();

        if (!$shares) {
            // If user has no shares, return empty data
            return response()->json([
                'success' => true,
                'message' => 'No shares found',
                'data' => [
                    'id' => null,
                    'total_shares' => 0,
                    'share_value' => 0,
                    'total_value' => 0,
                    'dividends_earned' => 0,
                    'last_dividend_date' => null,
                    'certificates' => []
                ]
            ]);
        }

        // Return the shares data
        return response()->json([
            'success' => true,
            'message' => 'Shares retrieved successfully',
            'data' => new ShareResource($shares)
        ]);
    }

    /*
     * Function to purchase shares
     *
     * */
    public function purchase(Request $request){
        // Validate the request
        $request->validate([
            'shares' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string'
        ]);

        // Get the authenticated user
        $user = auth()->user();

        // Get or create the user's shares
        $shares = Share::firstOrCreate(
            ['member_id' => $user->id],
            [
                'total_shares' => 0,
                'share_value' => 0,
                'total_value' => 0,
                'dividends_earned' => 0
            ]
        );

        // Update the shares
        $shareValue = $request->amount / $request->shares;
        $shares->total_shares += $request->shares;
        $shares->share_value = $shareValue;
        $shares->total_value += $request->amount;
        $shares->save();

        // Create a certificate for this purchase
        $certificate = $shares->certificates()->create([
            'certificate_number' => 'SC' . str_pad($shares->id . rand(100, 999), 8, '0', STR_PAD_LEFT),
            'shares_count' => $request->shares,
            'purchase_date' => now(),
            'purchase_price' => $request->amount
        ]);

        // Return the updated shares data
        return response()->json([
            'success' => true,
            'message' => 'Shares purchased successfully',
            'data' => new ShareResource($shares)
        ]);
    }

    /*
     * Function to get dividends
     *
     * */
    public function getDividends(Request $request){
        // Get the authenticated user
        $user = auth()->user();

        // Get the user's dividends
        $dividends = $user->dividends()->get();

        // Return the dividends data
        return response()->json([
            'success' => true,
            'message' => 'Dividends retrieved successfully',
            'data' => $dividends
        ]);
    }

    /*
     * Function to get certificates
     *
     * */
    public function getCertificates(Request $request){
        // Get the authenticated user
        $user = auth()->user();

        // Get the user's shares
        $shares = Share::where('member_id', $user->id)->first();

        if (!$shares) {
            // If user has no shares, return empty data
            return response()->json([
                'success' => true,
                'message' => 'No certificates found',
                'data' => []
            ]);
        }

        // Get the certificates
        $certificates = $shares->certificates;

        // Return the certificates data
        return response()->json([
            'success' => true,
            'message' => 'Certificates retrieved successfully',
            'data' => $certificates
        ]);
    }
}
