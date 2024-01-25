<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use DB;
use URL;
use Carbon\Carbon;
use Mail;
use App\Mail\DemoMail;

class eventcontroller extends Controller
{
    public function submitevent(request $request)
    {
        $validate = Validator::make($request->all(), [
            'event_title' => 'required',
            'event_description' => 'required',
            'event_startdate' => 'required',
            'event_enddate' => 'required',
            'event_type' => 'required_if:is_recurrence,1',
            'recurrence_startdate' => 'required_if:is_recurrence,1',
            'recurrence_enddate' => 'required_if:is_recurrence,1',
            'daydate' => 'required_if:event_type,week',
            'targetmonth' => 'required_if:event_type,year',
            'month_day' => 'required_if:event_type,month',

        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }

        $event_startdate = date('Y-m-d', strtotime($request->event_startdate));
        $event_enddate = date('Y-m-d', strtotime($request->event_enddate));
        $event_starttime = date('h:i:s a', strtotime($request->event_startdate));
        $event_endtime = date('h:i:s a', strtotime($request->event_enddate));


        $recurrence_startdate = date('Y-m-d', strtotime($request->recurrence_startdate));
        $recurrence_enddate = date('Y-m-d', strtotime($request->recurrence_enddate));
        $recurrence_starttime = date('h:i:s a', strtotime($request->recurrence_startdate));
        $recurrence_endtime = date('h:i:s a', strtotime($request->recurrence_enddate));
        // $request->recurrence_enddate =  date('Y-m-d',strtotime($request->recurrence_enddate));

        $event = [
            'event_title' => $request->event_title,
            'event_description' => $request->event_description,
            'event_tags' => json_encode($request->event_tags),
            'event_Categories' => json_encode($request->evevnt_Categories),
            'event_status' => 1,
            'event_city' => $request->event_city,
            'venue_detail' => json_encode($request->venue_detail),
            'organizer_detail' => json_encode($request->organizer_detail),
            'event_Website' => $request->event_Website,
            'event_cost' => $request->event_cost,
            'is_event_allday' => $request->is_event_allday,
            'event_startdate' => $event_startdate,
            'event_enddate' => $event_enddate,
            'event_starttime' => $event_starttime,
            'event_endtime' => $event_endtime,
            'event_timezone' => $request->event_timezone,
            'created_at' => date('Y-m-d h:i:s'),

        ];

        $event = DB::table('event')->insertGetId($event);
        $eventid = $event;

        if ($request->hasFile('event_image')) {

            $eventimages = time() . rand() . '.' . $request->event_image->extension();
            $userpicturename = $request->event_image->move(public_path('events/'), $eventimages);

            // Update the event record with the image information
            $save = DB::table('event')
                ->where('event_id', $eventid)
                ->update(['event_image' => $eventimages]);
        }

        //getAllDatesInRange function for daily
        function getAllDatesInRange($startDate, $endDate)
        {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            $resultDates = [];

            while ($start->lte($end)) {
                $resultDates[] = $start->toDateString();
                $start->addDay(); // Move to the next day
            }

            return $resultDates;
        }




        //getWeekdaysBetweenDates function for weeks
        function getWeekdaysBetweenDates($startDate, $endDate, $weekdays)
        {
            $startDate = Carbon::parse($startDate);
            $endDate = Carbon::parse($endDate);
            $dates = [];


            while ($startDate <= $endDate) {
                if (in_array($startDate->dayOfWeek, $weekdays)) {
                    $dates[] = $startDate->toDateString();
                }
                $startDate->addDay();
            }

            return $dates;
        }

        //calculateSpecificDaysOfMonth function for Months

        function calculateSpecificDaysOfMonth($startDate, $endDate, $specificDays)
        {
            $startDate = Carbon::parse($startDate);
            $endDate = Carbon::parse($endDate);


            $dates = [];

            while ($startDate->lessThanOrEqualTo($endDate)) {
                foreach ($specificDays as $day) {
                    // Set the day to the specified day of the month
                    $specificDayOfMonth = $startDate->copy()->day($day);

                    // Check if the calculated date is within the specified range
                    if ($specificDayOfMonth->gte($startDate) && $specificDayOfMonth->lte($endDate)) {
                        $dates[] = $specificDayOfMonth->toDateString();
                    }
                }


                // Move to the next month
                $startDate->addMonth();
            }


            return $dates;
        }
        //getTargetMonthsAndDates function for Year

        function getTargetMonthsAndDates($startDate, $endDate, $targetMonths, $targetDates)
        {
            $startYear = (int) date('Y', strtotime($startDate));
            $endYear = (int) date('Y', strtotime($endDate));

            $resultData = [];

            foreach ($targetMonths as $index => $targetMonth) {
                // Check if the key exists in $targetDates array
                if (isset($targetDates[$index])) {
                    $targetDate = $targetDates[$index];

                    for ($year = $startYear; $year <= $endYear; $year++) {
                        $date = sprintf('%04d-%02d-%02d', $year, $targetMonth, $targetDate);

                        if ($date >= $startDate && $date <= $endDate) {
                            $resultData[] = $date;
                        }
                    }
                }
            }

            return $resultData;
        }
        if ($request->event_type == 'once') {
            $once = [
                'event_title' => $request->event_title,
                'event_description' => $request->event_description,
                'event_tags' => json_encode($request->event_tags),
                'event_Categories' => json_encode($request->evevnt_Categories),
                'event_status' => 1,
                'event_city' => $request->event_city,
                'venue_detail' => json_encode($request->venue_detail),
                'organizer_detail' => json_encode($request->organizer_detail),
                'event_Website' => $request->event_Website,
                'event_cost' => $request->event_cost,
                'is_event_allday' => $request->is_event_allday,
                'event_startdate' => $recurrence_startdate,
                'event_enddate' => $recurrence_enddate,
                'event_starttime' => $recurrence_starttime,
                'event_endtime' => $recurrence_endtime,
                'event_timezone' => $request->event_timezone,
                'event_image' => $eventimages ?? null,
                'created_at' => date('Y-m-d h:i:s'),

            ];

            $event = DB::table('event')->insertGetId($once);
        } else if ($request->event_type == 'daily') {
            $startDate = $recurrence_startdate;
            $endDate = $recurrence_enddate;
            $allDates = getAllDatesInRange($startDate, $endDate);

            $dates = [];
            foreach ($allDates as $date) {
                $dates[] = [
                    'date' => $date,
                ];
            }
            if (!empty($dates)) {
                foreach ($dates as $date) {
                    $days = [
                        'event_title' => $request->event_title,
                        'event_description' => $request->event_description,
                        'event_tags' => json_encode($request->event_tags),
                        'event_Categories' => json_encode($request->evevnt_Categories),
                        'event_status' => 1,
                        'event_city' => $request->event_city,
                        'venue_detail' => json_encode($request->venue_detail),
                        'organizer_detail' => json_encode($request->organizer_detail),
                        'event_Website' => $request->event_Website,
                        'event_cost' => $request->event_cost,
                        'is_event_allday' => $request->is_event_allday,
                        'event_startdate' => $date['date'],
                        'event_enddate' => $date['date'],
                        'event_starttime' => $recurrence_starttime,
                        'event_endtime' => $recurrence_endtime,
                        'event_timezone' => $request->event_timezone,
                        'event_image' => $eventimages ?? null,
                        'created_at' => date('Y-m-d h:i:s'),

                    ];


                    $event = DB::table('event')->insertGetId($days);
                }
            }
        } else if ($request->event_type == 'weekly') {
            $data = [];
            foreach ($request->daydate as $row) {
                $data[] = intval($row);
            }
            $startDate = $recurrence_startdate;
            $endDate = $recurrence_enddate;
            $weekdays = $data;

            $weekdayDates = getWeekdaysBetweenDates($startDate, $endDate, $weekdays);

            // dd($weekdayDates);
            $dates = [];
            foreach ($weekdayDates as $date) {
                $dates[] = [
                    'date' => $date,
                ];
            }
            $data = [];
            if (!empty($dates)) {
                foreach ($dates as $date) {

                    $week = [
                        'event_title' => $request->event_title,
                        'event_description' => $request->event_description,
                        'event_tags' => json_encode($request->event_tags),
                        'event_Categories' => json_encode($request->evevnt_Categories),
                        'event_status' => 1,
                        'event_city' => $request->event_city,
                        'venue_detail' => json_encode($request->venue_detail),
                        'organizer_detail' => json_encode($request->organizer_detail),
                        'event_Website' => $request->event_Website,
                        'event_cost' => $request->event_cost,
                        'is_event_allday' => $request->is_event_allday,
                        'event_startdate' => $date['date'],
                        'event_enddate' => $date['date'],
                        'event_starttime' => $recurrence_starttime,
                        'event_endtime' => $recurrence_endtime,
                        'event_timezone' => $request->event_timezone,
                        'event_image' => $eventimages ?? null,
                        'created_at' => date('Y-m-d h:i:s'),

                    ];
                    $event = DB::table('event')->insertGetId($week);
                }
                // return response()->json([ 'message' => 'Event Submitted Succesfully'], 200);
            }
        }

        // for months
        else if ($request->event_type == 'monthly') {
            $monthday = [];
            foreach ($request->month_day as $row) {
                $monthday[] = intval($row);
            }

            $startDate = $recurrence_startdate;
            $endDate = $recurrence_enddate;
            // Specify the specific days you want to find in each month
            $specificDays = $monthday;


            $resultDates = calculateSpecificDaysOfMonth($startDate, $endDate, $specificDays);
            $dates = [];
            foreach ($resultDates as $date) {
                $dates[] = [
                    'date' => $date,
                ];
            }
            $month = [];
            if (!empty($dates)) {
                foreach ($dates as $date) {
                    $month = [
                        'event_title' => $request->event_title,
                        'event_description' => $request->event_description,
                        'event_tags' => json_encode($request->event_tags),
                        'event_Categories' => json_encode($request->evevnt_Categories),
                        'event_status' => 1,
                        'event_city' => $request->event_city,
                        'venue_detail' => json_encode($request->venue_detail),
                        'organizer_detail' => json_encode($request->organizer_detail),
                        'event_Website' => $request->event_Website,
                        'event_cost' => $request->event_cost,
                        'is_event_allday' => $request->is_event_allday,
                        'event_startdate' => $date['date'],
                        'event_enddate' => $date['date'],
                        'event_starttime' => $recurrence_starttime,
                        'event_endtime' => $recurrence_endtime,
                        'event_timezone' => $request->event_timezone,
                        'event_image' => $eventimages ?? null,
                        'created_at' => date('Y-m-d h:i:s'),

                    ];
                    $eventid = DB::table('event')->insertGetId($month);

                }
                // return response()->json(['message' => 'Event Submitted Succesfully'], 200);

            }
            //for years

        } else if ($request->event_type == 'yearly') {
            $month = [];
            $dates = [];
            foreach ($request->targetmonth as $row) {
                $month[] = intval($row);
            }
            foreach ($request->month_day as $row) {
                $dates[] = intval($row);
            }
            $startDate = $recurrence_startdate;
            $endDate = $recurrence_enddate;

            $targetMonths = $month; // Specify the dynamic target months
            $targetDates = $dates; // Specify the corresponding dynamic target dates

            $result = getTargetMonthsAndDates($startDate, $endDate, $targetMonths, $targetDates);
            $dates = [];
            foreach ($result as $date) {
                $dates[] = [
                    'date' => $date,
                ];
            }
            $year = '';
            if (!empty($dates)) {
                foreach ($dates as $date) {
                    $year = [
                        'event_title' => $request->event_title,
                        'event_description' => $request->event_description,
                        'event_tags' => json_encode($request->event_tags),
                        'event_Categories' => json_encode($request->evevnt_Categories),
                        'event_status' => 1,
                        'event_city' => $request->event_city,
                        'venue_detail' => json_encode($request->venue_detail),
                        'organizer_detail' => json_encode($request->organizer_detail),
                        'event_Website' => $request->event_Website,
                        'event_cost' => $request->event_cost,
                        'is_event_allday' => $request->is_event_allday,
                        'event_startdate' => $date['date'],
                        'event_enddate' => $date['date'],
                        'event_starttime' => $recurrence_starttime,
                        'event_endtime' => $recurrence_endtime,
                        'event_timezone' => $request->event_timezone,
                        'event_image' => $eventimages ?? null,
                        'created_at' => date('Y-m-d h:i:s'),

                    ];
                    $eventid = DB::table('event')->insertGetId($year);
                }

            }
        }

        $mailData = [
            'startdate' => date('Y-M-d h:i:s a', strtotime($event_startdate . ' ' . $event_starttime)),
            'enddate' => date('Y-M-d h:i:s a', strtotime($event_starttime . ' ' . $event_endtime)),
            'event_title' => $request->event_title,
            'event_description' => $request->event_description,
            'imagename' => $eventimages ?? null,
        ];

        // Mail::to('carl@neo-techie.com')->send(new DemoMail($mailData));
        Mail::to('yasirshahpk8@gmail.com')->send(new DemoMail($mailData));


        return response()->json(['message' => 'Event Submitted Succesfully'], 200);

    }

    public function eventlist()
    {


        $events = DB::table('event')
            ->where('event_status', 2)
            ->latest()
            ->paginate(8);

        $imagepath = URL::to('/') . '/public/events';
        return $events->isEmpty()
            ? response()->json(['message' => 'no event available'], 404)
            : response()->json(['data' => $events, 'imagepath' => $imagepath, 'message' => 'Event List',], 200);


    }
    public function eventdetail(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'event_id' => 'required',
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
        $event = DB::table('event')
            ->where([
                'event_id' => $request->event_id,
                // 'event_status' => 2,
            ])
            ->first();
        $imagepath = URL::to('/') . '/public/events';

        return response()->json(['data' => $event, 'imagepath' => $imagepath, 'message' => 'Event detail'], 200);
    }
    public function searchevent(Request $request)
    {

        $eventQuery = DB::table('event')
            ->where('event_status', 2);


        if (!empty($request->event_title)) {
            $eventQuery->where('event_title', 'like', '%' . $request->event_title . '%');
        }

        $events = $eventQuery
            ->latest()
            ->paginate(5);
        $imagepath = URL::to('/') . '/public/events';
        return $events->isEmpty()
            ? response()->json(['message' => 'no event available'], 404)
            : response()->json(['data' => $events, 'imagepath' => $imagepath, 'message' => 'Events'], 200);
    }

    public function eventbystatus(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'event_status' => 'required',
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }

        $events = DB::table('event')
            ->where('event_status', $request->event_status)
            ->orderBy('event_id', 'DESC')
            ->paginate(30);

        $imagepath = URL::to('/') . '/public/events';
        return $events->isEmpty()
            ? response()->json(['message' => 'no event available'], 404)
            : response()->json(['data' => $events, 'imagepath' => $imagepath, 'message' => 'Event List',], 200);
    }

    public function approvedeclineevent(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'event_id' => 'required',
            'event_status' => 'required',
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
        $save = DB::table('event')
            ->where('event_id', '=', $request->event_id)
            ->update([
                'event_status' => $request->event_status
            ]);
        if ($save) {
            return response()->json(['message' => 'EventUpdated Successfully'], 200);
        } else {
            return response()->json("Oops! Something Went Wrong", 400);
        }
    }

    public function eventbymonthyear(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'event_month' => 'required',
            'event_year' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }

        $year = $request->event_year;
        $month = $request->event_month;

        // Create a Carbon instance for the first day of the specified month and year
        $startDate = Carbon::create($year, $month, 1);

        // Get the last day of the month
        $endDate = $startDate->copy()->endOfMonth();

        // Use $startDate and $endDate in your query
        $events = $this->getDataByMonthAndYear($startDate, $endDate);

        return response()->json(['events' => $events, 'message' => 'All events'], 200);
    }

    public function getDataByMonthAndYear($startDate, $endDate)
    {
        $events = DB::table('event')
            ->whereBetween('event_startdate', [$startDate, $endDate])
            ->get();

        return $events;
    }

    public function eventbydate(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'event_date' => 'required'
            ]
        );
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
        $date = date('Y-m-d', strtotime($request->event_date));
        $events = DB::table('event')
            ->where('event_startdate', $date)
            ->get();

        return response()->json(['events' => $events, 'message' => 'All events'], 200);
    }

}
