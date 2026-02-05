<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Display all bookings for the authenticated user.
     */
    public function index()
    {
        $bookings = Booking::where('user_id', auth('api')->id())
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $bookings,
        ]);
    }

    /**
     * Store one or multiple bookings with work schedule validation.
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|array|min:1',
            'price.*' => 'required|numeric|min:0',

            'start_date' => 'required|array|min:1',
            'start_date.*' => 'required|date',

            'end_date' => 'required|array|min:1',
            'end_date.*' => 'required|date',

            'start_time' => 'required|array|min:1',
            'start_time.*' => 'required|date_format:H:i',

            'end_time' => 'required|array|min:1',
            'end_time.*' => 'required|date_format:H:i',

            'gateway' => 'nullable|array|min:1',
            'gateway.*' => 'nullable|in:stripe,paypal,manual',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->price as $index => $price) {
            $serviceOwnerId = $request->user_id[$index];
            $startDate = $request->start_date[$index];
            $endDate = $request->end_date[$index];
            $startTime = $request->start_time[$index];
            $endTime = $request->end_time[$index];
            $gateway = $request->gateway[$index] ?? 'manual';

            // Validate end date >= start date
            if (strtotime($endDate) < strtotime($startDate)) {
                return response()->json([
                    'status' => true,
                    'message' => ["end_date.$index" => "End date must be after or equal to start date."]
                ], 201);
            }

            // Convert times to Carbon
            $requestedStart = Carbon::createFromFormat('H:i', $startTime);
            $requestedEnd = Carbon::createFromFormat('H:i', $endTime);

            if ($requestedEnd->lt($requestedStart)) {
                return response()->json([
                    'status' => true,
                    'message' => ["end_time.$index" => "End time must be after or equal to start time."]
                ], 201);
            }

            // Loop through each date in the range
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            for ($date = $start; $date->lte($end); $date->addDay()) {
                $dayOfWeek = strtolower($date->format('l')); // monday, tuesday, etc.

                // Get provider schedule for this day
                $schedule = \App\Models\WorkSchedule::where('user_id', auth('api')->id())
                    ->where('day_of_week', $dayOfWeek)
                    ->first();

                if (!$schedule) {
                    return response()->json([
                        'status' => false,
                        'message' => "Provider is not available on $dayOfWeek ({$date->toDateString()})"
                    ], 201);
                }

                // Convert schedule times to Carbon
                $scheduleStart = Carbon::createFromFormat('H:i:s', $schedule->start_time);
                $scheduleEnd = Carbon::createFromFormat('H:i:s', $schedule->end_time);

                if ($requestedStart->lt($scheduleStart) || $requestedEnd->gt($scheduleEnd)) {
                    return response()->json([
                        'status' => false,
                        'message' => "Requested time $startTime-$endTime is outside provider's schedule ({$schedule->start_time}-{$schedule->end_time}) on {$date->toDateString()}"
                    ], 201);
                }

                // Check overlapping bookings for this date
                $isBooked = \App\Models\Booking::where('service_owner_id', auth('api')->id())->whereNot('status', 'success')
                    ->whereDate('start_date', $date->toDateString())
                    ->where(function ($q) use ($requestedStart, $requestedEnd) {
                        $q->where(function ($q2) use ($requestedStart, $requestedEnd) {
                            $q2->whereTime('start_time', '<', $requestedEnd->format('H:i:s'))
                                ->whereTime('end_time', '>', $requestedStart->format('H:i:s'));
                        });
                    })
                    ->exists();

                if ($isBooked) {
                    return response()->json([
                        'status' => true,
                        'message' => "Provider is already booked on {$date->toDateString()} $startTime-$endTime."
                    ], 201);
                }
            }

            // Save booking
            $booking = new \App\Models\Booking();
            $booking->user_id =  $serviceOwnerId;
            $booking->service_owner_id = auth('api')->id();
            $booking->price = $price;
            $booking->currency = 'USD';
            $booking->start_date = $startDate;
            $booking->end_date = $endDate;
            $booking->start_time = $startTime;
            $booking->end_time = $endTime;
            $booking->day = strtolower(date('l', strtotime($startDate)));
            $booking->gateway = $gateway;
            $booking->status = 'requested';
            $booking->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Bookings created successfully.',
        ], 201);
    }


    public function status($service_owner_id, Request $request)
    {
        // Get all bookings for authenticated user and the specific service owner
        $bookings = Booking::where('user_id', auth('api')->id())
            ->where('service_owner_id', $service_owner_id)
            ->where('status', 'requested')
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No bookings found for this service provider.'
            ], 404);
        }

        // Map bookings with a friendly status message
        $data = $bookings->map(function ($booking) {


            return [
                'id' => $booking->id,
                'service_owner_id' => $booking->service_owner_id,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'day' => $booking->day,
                'price' => $booking->price,
                'currency' => $booking->currency,
                'gateway' => $booking->gateway,
                'status' => $booking->status,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Requested Bookings retrieved successfully.',
            'data' => $data
        ], 200);
    }

    public function accept(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|array',
            'booking_id.*' => 'integer|exists:bookings,id'
        ]);

        foreach ($request->booking_id as $id) {
            $booking = Booking::find($id);
            $booking->status = 'pending';
            $booking->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Selected bookings have been accepted successfully.'
        ]);
    }



    /**
     * Customer bookings.
     */
    public function customer_booking()
    {
        $bookings = Booking::where('user_id', auth('api')->id())
            ->with(['customer_booking', 'provider_booking'])
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No bookings found.',
            ], 404);
        }

        $data = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'service_owner_id' => $booking->service_owner_id,
                'customer_first_name' => optional($booking->customer_booking)->first_name,
                'customer_last_name' => optional($booking->customer_booking)->last_name,
                'service_provider_first' => optional($booking->provider_booking)->first_name,
                'service_provider_last' => optional($booking->provider_booking)->last_name,
                'service_name' => optional($booking->service)->title,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'day' => $booking->day,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'time_range' => $booking->start_time && $booking->end_time
                    ? date('g:i A', strtotime($booking->start_time)) . ' - ' . date('g:i A', strtotime($booking->end_time))
                    : null,
                'gateway' => $booking->gateway,
                'status' => $booking->status,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Customer bookings fetched successfully.',
            'data' => $data,
        ]);
    }

    /**
     * Provider bookings.
     */
    public function provider_booking()
    {
        $bookings = Booking::where('service_owner_id', auth('api')->id())
            ->with(['customer_booking', 'provider_booking'])
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No bookings found.',
            ], 404);
        }

        $data = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'user_id' => $booking->user_id,
                'customer_first_name' => optional($booking->customer_booking)->first_name,
                'customer_last_name' => optional($booking->customer_booking)->last_name,
                'service_provider_first' => optional($booking->provider_booking)->first_name,
                'service_provider_last' => optional($booking->provider_booking)->last_name,
                'service_name' => optional($booking->service)->title,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'day' => $booking->day,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'time_range' => $booking->start_time && $booking->end_time
                    ? date('g:i A', strtotime($booking->start_time)) . ' - ' . date('g:i A', strtotime($booking->end_time))
                    : null,
                'gateway' => $booking->gateway,
                'status' => $booking->status,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Provider bookings fetched successfully.',
            'data' => $data,
        ]);
    }

    /**
     * Already booked slots for provider.
     */


    public function already_booked(Request $request)
    {
        $request->validate([
            'service_owner_id' => 'required|integer',
            'year'  => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year  = $request->year;
        $month = $request->month;

        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth   = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        $providerId = $request->service_owner_id;

        // Get all bookings for the month
        $bookings = Booking::where('service_owner_id', $providerId)
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($x) use ($startOfMonth, $endOfMonth) {
                        $x->where('start_date', '<=', $startOfMonth)
                            ->where('end_date', '>=', $endOfMonth);
                    });
            })
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No fully booked dates.',
                'dates' => []
            ]);
        }

        $fullyBookedDates = [];

        // Generate all dates in month
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);

        foreach ($period as $date) {
            $dayOfWeek = strtolower($date->format('l')); // monday, tuesday, etc.

            // Get provider's schedule for this day
            $schedules = WorkSchedule::where('user_id', $providerId)
                ->where('day_of_week', $dayOfWeek)
                ->get();

            if ($schedules->isEmpty()) {
                continue;
            }

            $allSchedulesCovered = true;

            foreach ($schedules as $schedule) {
                $scheduleStart = Carbon::parse($schedule->start_time);
                $scheduleEnd = Carbon::parse($schedule->end_time);

                // Get bookings that overlap with this schedule on this date
                $bookings = Booking::where('service_owner_id', $providerId)
                    ->whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date)
                    ->get();

                $coveredIntervals = [];
                foreach ($bookings as $b) {
                    $bookingStart = Carbon::parse($b->start_time);
                    $bookingEnd = Carbon::parse($b->end_time);

                    if ($bookingEnd > $scheduleStart && $bookingStart < $scheduleEnd) {
                        $coveredIntervals[] = ['start' => $bookingStart, 'end' => $bookingEnd];
                    }
                }

                usort($coveredIntervals, function ($a, $b) {
                    return $a['start']->timestamp <=> $b['start']->timestamp;
                });

                $current = $scheduleStart->copy();
                foreach ($coveredIntervals as $interval) {
                    if ($interval['start'] > $current) {
                        $allSchedulesCovered = false;
                        break 2; // no need to check other schedules
                    }
                    if ($interval['end'] > $current) {
                        $current = $interval['end']->copy();
                    }
                }

                if ($current < $scheduleEnd) {
                    // End of schedule not covered
                    $allSchedulesCovered = false;
                    break;
                }
            }

            if ($allSchedulesCovered) {
                $fullyBookedDates[] = [
                    'date' => $date->format('y-n-j'),
                    'day'  => $date->format('l')
                ];
            }
        }

        if (empty($fullyBookedDates)) {
            return response()->json([
                'status' => false,
                'message' => 'No fully booked dates.',
                'dates' => []
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Fully booked dates.',
            'dates' => $fullyBookedDates
        ]);
    }





    /**
     * Update a booking.
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
            ->where('service_owner_id', auth('api')->id())
            ->first();

        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'start_date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_date' => 'nullable|date',
            'end_time' => 'nullable|date_format:H:i',
            'gateway' => 'nullable|in:stripe,paypal,manual',
            'status' => 'nullable|in:pending,success,failed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $booking->update($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Booking updated successfully.',
            'data' => $booking,
        ]);
    }
    public function provider_schedule_by_date(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $date = $request->date;
        $dayOfWeek = strtolower(date('l', strtotime($date))); // monday, tuesday, ...

        // Get provider's schedule for that weekday
        $schedule = WorkSchedule::where('user_id', auth('api')->id())
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$schedule) {
            return response()->json([
                'status' => false,
                'message' => "No schedule found for {$date} ({$dayOfWeek}).",
                'data' => null
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => "Schedule for {$date} fetched successfully.",
            'data' => [
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'time_range' => date('g:i A', strtotime($schedule->start_time))
                    . ' - ' . date('g:i A', strtotime($schedule->end_time)),
            ],
        ]);
    }

    /**
     * Delete a booking.
     */
    public function destroy($id)
    {
        $booking = Booking::where('user_id', auth('api')->id())->find($id);

        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found.',
            ], 404);
        }

        $booking->delete();

        return response()->json([
            'status' => true,
            'message' => 'Booking deleted successfully.',
        ]);
    }

    public function work_sechedule()
    {

        $schedule = WorkSchedule::where('user_id', auth('api')->id())->get();


        return response()->json([
            'status' => true,
            'message' => 'Avilable Sechedule',
            'data' => $schedule
        ]);
    }
}
