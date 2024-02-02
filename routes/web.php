<?php

use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
   dd('ds');
});

                Route::get('/yasir', function () {
                $eventName = 'Your Event Name';
                $eventDescription = 'Your Event Description';
                $startDate = '2022-12-31T12:00:00';
                $endDate = '2022-12-31T14:00:00';

                // Construct the Google Calendar event link
                $googleCalendarLink = "https://www.google.com/calendar/render?action=TEMPLATE&text="
                    . urlencode($eventName)
                    . "&details=" . urlencode($eventDescription)
                    . "&dates=" . urlencode($startDate . '/' . $endDate);

                // Redirect the user to the Google Calendar event page
                return redirect()->to($googleCalendarLink);
});