<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;

class ApiBaseController extends Controller
{
    use ApiResponse;

    protected function keepFilters()
    {
        $search = null;
        if (!empty(request()->filter['Search'])) {
            $search =  request()->filter['Search'];
        }

        $per_page = '10';
        if (!empty(request()->per_page)) {
            $per_page =  request()->per_page;
        }

        $sort = '';
        if (!empty(request()->sort)) {
            $sort =  request()->sort;
        }

        $page = '';
        if (!empty(request()->page)) {
            $page =  request()->page;
        }
        $filters =  [
            'search' => $search, // Pass the search query to the view
            'per_page' => $per_page, // Pass the perPage value to the view
            'sort' =>  $sort, // Pass the sort value to the view
            'page' => $page,
        ];

        return $filters;
    }
}
