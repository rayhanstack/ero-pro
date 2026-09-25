<?php

namespace App\Http\Controllers\Admin\Ajax;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\JsonResponse;

class LocationAjaxController extends Controller
{
    /**
     * Get states for a given country.
     */
    public function getStates(Country|int|string $country): JsonResponse
    {
        $countryId = $country instanceof Country ? $country->id : $country;

        $states = State::where('country_id', $countryId)
            ->orderBy('name')
            ->select('id', 'name', 'country_id')
            ->get();

        return response()->json($states);
    }

    /**
     * Get cities for a given state.
     */
    public function getCities(State|int|string $state): JsonResponse
    {
        $stateId = $state instanceof State ? $state->id : $state;

        $cities = City::where('state_id', $stateId)
            ->orderBy('name')
            ->select('id', 'name', 'state_id', 'country_id')
            ->get();

        return response()->json($cities);
    }
}
