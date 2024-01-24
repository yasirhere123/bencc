<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use DB;
use JWTAuth;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;

class CheckLogin {
    /**
    * Handle an incoming request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Closure  $next
    * @return mixed
    */

    public function handle( Request $request, Closure $next ) {
        if ( $request->header( 'authorization' ) && $request->id ) {
            $check = DB::table( 'user' )
            ->select( 'id' )
            ->where( 'id', '=', $request->id )
            // ->where( 'status_id', '=', 1 )
            ->count();
            if ( $check == 0 ) {
                return redirect( '/login' );

            } else {
                return $next( $request );
            }
        } else {
            return redirect( '/login' );
        }
    }
}
