<?php

namespace Bmatovu\Ussd\Providers;

use Carbon\Carbon;

/**
 * Usage: Show the next 5 week days.
 *
 * ```xml
 * <list header="Choose appointment day" provider="week_days" prefix="appointment_date"/>
 * <variable name="appointment_date" value="{{appointment_date_id}}" />
 * ```
 */
class WeekDaysProvider extends BaseProvider
{
    public function load(): array
    {
        $day = Carbon::now();

        $weekDays = [];

        do {
            $day->addDay();

            if ($day->isWeekend()) {
                continue;
            }

            $weekDays[] = [
                'id' => $day->format('Y-m-d'),
                'label' => $day->format('D, jS M'),
            ];
        } while (count($weekDays) < 5);

        return $weekDays;
    }
}
