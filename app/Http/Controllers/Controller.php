<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;


class Controller extends BaseController
{
  /**
     * @OA\Info(
     *      version="1.0.0",
     *      title="Meeting Scheduler API",
     *      description="Meeting Scheduler API allows to perfom operation related to meeting",
     *      @OA\Contact(
     *          email="innotuyi8@gmail.com"
     *      ),
     *      @OA\License(
     *          name="Apache 2.0",
     *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
     *      )
     * )
 */


    use AuthorizesRequests,DispatchesJobs, ValidatesRequests;
}