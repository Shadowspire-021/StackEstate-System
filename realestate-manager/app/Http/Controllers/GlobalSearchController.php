<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Services\CacheService;

class GlobalSearchController extends Controller
{
    /**
     * Maximum results per entity type to prevent excessive queries.
     */
    private const LIMIT_PER_ENTITY = 15;

    /**
     * Cache TTL for search results (short - 30 seconds).
     */
    private const SEARCH_TTL = 30;

    /**
     * Handle global search across clients, properties, and units.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        $term = $request->input('q', '');

        if ($request->ajax()) {
            return response()->json($this->performSearch($term));
        }

        return view('search.results', [
            'term' => $term,
            'results' => $this->performSearch($term),
        ]);
    }

    /**
     * Perform the search across all entity types.
     *
     * @param  string  $term
     * @return array
     */
    private function performSearch(string $term): array
    {
        $cleanTerm = trim($term);

        if (empty($cleanTerm)) {
            return [
                'clients' => collect(),
                'properties' => collect(),
                'units' => collect(),
            ];
        }

        $searchGen = CacheService::getGeneration(CacheService::PREFIX_SEARCH);
        $cacheKey = CacheService::key(CacheService::PREFIX_SEARCH, md5($cleanTerm), (string) $searchGen);

        return CacheService::remember($cacheKey, self::SEARCH_TTL, function () use ($cleanTerm) {
            $results = [];

            // Search clients: full_name, cnic, phone, client_id
            $results['clients'] = Client::with(['property.unit'])
                ->search($cleanTerm)
                ->limit(self::LIMIT_PER_ENTITY)
                ->get();

            // Search properties: plot_number, block_name, location, property_type
            $results['properties'] = Property::with(['client', 'unit'])
                ->search($cleanTerm)
                ->limit(self::LIMIT_PER_ENTITY)
                ->get();

            // Search units: unit_number, floor_number, status
            $results['units'] = Unit::with(['property.client'])
                ->search($cleanTerm)
                ->limit(self::LIMIT_PER_ENTITY)
                ->get();

            return $results;
        });
    }
}
